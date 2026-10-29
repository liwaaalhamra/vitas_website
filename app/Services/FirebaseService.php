<?php

namespace App\Services;

use App\Models\Api\UserPush;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications through Firebase Cloud Messaging (HTTP v1 API).
 *
 * Authentication uses a Google service account JSON file
 * (Firebase console > Project settings > Service accounts > Generate new private key).
 */
class FirebaseService
{
    const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    const CONCURRENCY = 50;

    // global language channels, the mobile app subscribes each device to the one matching its language
    const TOPIC_EN = 'GLOBAL_EN';
    const TOPIC_AR = 'GLOBAL_AR';

    protected $credentials;
    protected $projectId;

    public function __construct()
    {
        $path = config('services.firebase.credentials');
        if (!$path || !is_file($path)) {
            throw new \RuntimeException('Firebase credentials file not found: '.$path);
        }
        $this->credentials = json_decode(file_get_contents($path), true);
        $this->projectId = config('services.firebase.project_id') ?: $this->credentials['project_id'];
    }

    /**
     * $info keys:
     *  tokens  array   FCM registration tokens (required unless topic is set)
     *  topic   string  topic to broadcast to instead of tokens, e.g. FirebaseService::TOPIC_EN
     *  title   string  (required)
     *  body    string  (required)
     *  data    array   extra key/value data for the app
     *  image   string  public image URL shown in the notification
     *  badge   int     iOS badge number
     *
     * Returns ['status' => OK|OK_WITH_ERRORS, 'message' => ..., 'debugger' => ...] or ['error' => ...]
     */
    public function send(array $info)
    {
        $tokens = !empty($info['tokens']) ? array_values(array_unique(array_filter($info['tokens']))) : [];
        if (empty($tokens) && empty($info['topic'])) {
            return ['error' => 'No device tokens provided'];
        }

        try {
            $accessToken = $this->accessToken();
        } catch (\Exception $ex) {
            return ['error' => 'Firebase Auth Error: '.$ex->getMessage().' - Failed to Send Push'];
        }

        $url = 'https://fcm.googleapis.com/v1/projects/'.$this->projectId.'/messages:send';

        if (!empty($info['topic'])) {
            $message = $this->buildMessage(['topic' => $info['topic']], $info);
            $response = Http::withToken($accessToken)->post($url, ['message' => $message]);
            if ($response->successful()) {
                $message = 'Push Sent Successfully to '.$info['topic'].' subscribers';
                return ['status' => 'OK', 'message' => $message, 'debugger' => $message];
            }
            return ['error' => ($response->json('error.message') ?: 'HTTP '.$response->status()).' - Failed to Send Push'];
        }
        $sent = 0;
        $invalidTokens = [];
        $errors = [];

        foreach (array_chunk($tokens, self::CONCURRENCY) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $url, $accessToken, $info) {
                foreach ($chunk as $token) {
                    $pool->withToken($accessToken)->post($url, ['message' => $this->buildMessage(['token' => $token], $info)]);
                }
            });

            foreach ($responses as $i => $response) {
                if (!$response instanceof Response) {
                    $errors[] = $response instanceof \Throwable ? $response->getMessage() : 'Connection error';
                    continue;
                }
                if ($response->successful()) {
                    $sent++;
                    continue;
                }
                if ($this->isInvalidToken($response)) {
                    $invalidTokens[] = $chunk[$i];
                } else {
                    $errors[] = $response->json('error.message') ?: 'HTTP '.$response->status();
                }
            }
        }

        if (!empty($invalidTokens)) {
            // Tokens of uninstalled apps / expired registrations, no point keeping them
            UserPush::whereIn('player_id', $invalidTokens)->delete();
        }
        if (!empty($errors)) {
            Log::warning('FCM push errors', array_slice(array_unique($errors), 0, 10));
        }

        $total = count($tokens);
        if ($sent == $total) {
            $message = 'Push Sent Successfully to '.$sent.' devices';
            return ['status' => 'OK', 'message' => $message, 'debugger' => $message];
        }
        if ($sent > 0 || (empty($errors) && !empty($invalidTokens))) {
            $message = 'Push Sent to '.$sent.' of '.$total.' devices'
                .(!empty($invalidTokens) ? ', '.count($invalidTokens).' invalid devices removed' : '');
            return ['status' => 'OK_WITH_ERRORS', 'message' => $message, 'debugger' => $message];
        }
        return ['error' => (isset($errors[0]) ? $errors[0] : 'Unknown Error').' - Failed to Send Push'];
    }

    /**
     * $target is ['token' => ...] or ['topic' => ...]
     */
    protected function buildMessage(array $target, array $info)
    {
        $notification = [
            'title' => $info['title'],
            'body' => $info['body'],
        ];
        $aps = ['sound' => 'default'];
        if (isset($info['badge'])) {
            $aps['badge'] = (int) $info['badge'];
        }

        $message = $target + [
            'notification' => $notification,
            'android' => ['notification' => ['sound' => 'default']],
            'apns' => ['payload' => ['aps' => $aps]],
        ];

        if (!empty($info['image'])) {
            $message['notification']['image'] = $info['image'];
            $message['apns']['payload']['aps']['mutable-content'] = 1;
            $message['apns']['fcm_options'] = ['image' => $info['image']];
        }

        if (!empty($info['data'])) {
            // FCM only accepts string values in data
            $message['data'] = array_map(function ($value) {
                return is_scalar($value) ? (string) $value : json_encode($value);
            }, $info['data']);
        }

        return $message;
    }

    protected function isInvalidToken(Response $response)
    {
        if ($response->status() == 404) {
            return true;
        }
        foreach ((array) $response->json('error.details') as $detail) {
            $code = isset($detail['errorCode']) ? $detail['errorCode'] : null;
            if ($code == 'UNREGISTERED') {
                return true;
            }
            // e.g. old OneSignal player ids still stored in the table
            if ($code == 'INVALID_ARGUMENT' && stripos((string) $response->json('error.message'), 'registration token') !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * OAuth2 access token from the service account, cached until shortly before it expires.
     */
    protected function accessToken()
    {
        return Cache::remember('fcm_access_token_'.$this->projectId, 3300, function () {
            $tokenUri = isset($this->credentials['token_uri']) ? $this->credentials['token_uri'] : 'https://oauth2.googleapis.com/token';
            $now = time();
            $header = $this->base64Url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->base64Url(json_encode([
                'iss' => $this->credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => $tokenUri,
                'iat' => $now,
                'exp' => $now + 3600,
            ]));
            if (!openssl_sign($header.'.'.$claims, $signature, $this->credentials['private_key'], 'sha256WithRSAEncryption')) {
                throw new \RuntimeException('Unable to sign JWT with the service account private key');
            }
            $response = Http::asForm()->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $header.'.'.$claims.'.'.$this->base64Url($signature),
            ]);
            if (!$response->successful() || !$response->json('access_token')) {
                throw new \RuntimeException($response->json('error_description') ?: 'HTTP '.$response->status());
            }
            return $response->json('access_token');
        });
    }

    protected function base64Url($value)
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
