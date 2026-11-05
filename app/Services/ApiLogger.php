<?php

namespace App\Services;

use App\Models\ApiLog;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends an outbound HTTP request and appends it to api_logs (timing, status,
 * redacted request, raw response). Never lets a logging failure break the call.
 */
class ApiLogger
{
    /** Keys whose values are never written to the log. */
    const REDACT = ['password', 'p', 'access_token', 'refresh_token', 'authorization', 'cardnumber', 'pan', 'cvv', 'cvc'];

    /**
     * @param  string  $method  GET | POST
     * @param  array   $options ['json' => array] | ['form' => array]
     * @return array{response: ?Response, error: ?string, log: ?ApiLog}
     */
    public function send(PendingRequest $request, string $service, string $action, string $method, string $url, array $options = [], ?int $paymentId = null): array
    {
        $payload = $options['json'] ?? $options['form'] ?? null;
        if (isset($options['form'])) {
            $request = $request->asForm();
        }

        $started = microtime(true);
        $response = null;
        $error = null;

        try {
            $response = strtoupper($method) === 'GET'
                ? $request->get($url)
                : $request->post($url, $payload ?? []);
        } catch (Throwable $e) {
            $error = Str::limit($e->getMessage(), 490);
        }

        $elapsed = (int) round((microtime(true) - $started) * 1000);
        $log = null;

        try {
            $log = ApiLog::create([
                'payment_id'        => $paymentId,
                'service_name'      => $service,
                'action'            => $action,
                'method'            => strtoupper($method),
                'endpoint'          => Str::limit($url, 490),
                'http_status'       => $response ? $response->status() : null,
                'execution_time_ms' => $elapsed,
                'request_payload'   => $payload !== null ? self::redact($payload) : null,
                'response_payload'  => $response ? self::redactBody($response->body()) : null,
                'error'             => $error,
                'created_at'        => now(),
            ]);
        } catch (Throwable $e) {
            Log::channel('payments')->error('api_logs.write_failed', ['error' => $e->getMessage(), 'url' => $url]);
        }

        return ['response' => $response, 'error' => $error, 'log' => $log];
    }

    public static function redact($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::REDACT, true)) {
                $data[$key] = '***';
            } elseif (is_array($value)) {
                // Areeba style [{"key": "...", "value": "..."}] parameter lists
                if (isset($value['key'], $value['value']) && in_array(strtolower((string) $value['key']), self::REDACT, true)) {
                    $value['value'] = '***';
                }
                $data[$key] = self::redact($value);
            }
        }

        return $data;
    }

    private static function redactBody(string $body): string
    {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            return json_encode(self::redact($decoded), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $body;
    }
}
