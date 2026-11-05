<?php

namespace App\Services\Areeba;

use App\Models\Payment;
use App\Services\ApiLogger;
use App\Support\Payload;
use App\Support\Secret;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * HTTP client for the Areeba hosted checkout API (payments-api.areeba.com).
 *
 *   A. POST {base}/{env}/api/Authentication                 grant_type=password -> bearer token
 *   B. POST {base}/{env}/api/tokens/request                  -> checkoutUrl
 *   C. GET  {base}/{env}/api/v1/payments/GetTransaction/{id} -> transaction status
 *
 * Every call is appended to api_logs. Response shapes are read defensively
 * (see Payload::find()) since field casing / nesting is not fixed by the spec.
 */
class AreebaClient
{
    const SERVICE = 'areeba';

    private array $config;

    public function __construct(private ApiLogger $logger, ?array $config = null)
    {
        $this->config = $config ?: config('services.areeba');
    }

    /**
     * Step B: open a hosted checkout session for the payment.
     *
     * @return array{checkout_url: string, raw: array}
     * @throws AreebaException
     */
    public function createCheckoutSession(Payment $payment, ?string $returnUrl = null): array
    {
        $parameters = [
            ['key' => 'productId', 'value' => (int) $this->config['product_id']],
            ['key' => 'chargeAmount', 'value' => round((float) $payment->amount, 2)],
            ['key' => 'isTaxIncluded', 'value' => (string) $this->config['tax_included']],
            ['key' => 'lang-country', 'value' => $payment->locale === 'en' ? 'en-iq' : 'ar-iq'],
            ['key' => 'pt', 'value' => $payment->reference],
            ['key' => 'currency', 'value' => $payment->currency],
        ];
        if (!empty($this->config['return_url_param']) && $returnUrl) {
            $parameters[] = ['key' => $this->config['return_url_param'], 'value' => $returnUrl];
        }

        $json = $this->authorized('checkout_session', 'POST', $this->url('/api/tokens/request'), [
            'parameters'  => $parameters,
            'tokenTypeId' => (int) $this->config['token_type_id'],
            'u'           => $this->config['username'],
            'p'           => Secret::reveal($this->config['password']),
        ], $payment->id);

        $checkoutUrl = Payload::find($json, ['checkoutUrl', 'checkout_url', 'redirectUrl']);
        if (!is_string($checkoutUrl) || !filter_var($checkoutUrl, FILTER_VALIDATE_URL)) {
            throw AreebaException::make('Areeba did not return a checkoutUrl: '.(self::errorMessage($json) ?: 'unknown error'), ['response' => $json]);
        }

        return ['checkout_url' => $checkoutUrl, 'raw' => $json];
    }

    /**
     * Step C: authoritative transaction status.
     *
     * @throws AreebaException
     */
    public function getTransaction(string $transactionId, ?int $paymentId = null): array
    {
        return $this->authorized('get_transaction', 'GET', $this->url('/api/v1/payments/GetTransaction/'.rawurlencode($transactionId)), null, $paymentId);
    }

    /**
     * Step A: bearer token, cached until shortly before it expires.
     *
     * @throws AreebaException
     */
    public function accessToken(bool $fresh = false, ?int $paymentId = null): string
    {
        $cacheKey = 'areeba_token_'.md5($this->config['base_url'].$this->config['client_id'].$this->config['username']);
        if (!$fresh && ($token = Cache::get($cacheKey))) {
            return $token;
        }

        $result = $this->logger->send($this->baseRequest(), self::SERVICE, 'authenticate', 'POST', $this->url('/api/Authentication'), [
            'form' => [
                'grant_type' => 'password',
                'username'   => $this->config['username'],
                'password'   => Secret::reveal($this->config['password']),
                'client_id'  => $this->config['client_id'],
            ],
        ], $paymentId);

        $json = $this->decode($result, 'authenticate');
        $token = Payload::find($json, ['access_token', 'accessToken', 'token']);
        if (!is_string($token) || $token === '') {
            throw AreebaException::make('Areeba authentication failed: '.(self::errorMessage($json) ?: 'no access token'), ['response' => $json]);
        }

        $ttl = (int) (Payload::find($json, ['expires_in', 'expiresIn']) ?: 3600);
        Cache::put($cacheKey, $token, max(60, $ttl - 120));

        return $token;
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Call an endpoint with the bearer token; refresh the token once on 401.
     */
    private function authorized(string $action, string $method, string $url, ?array $body, ?int $paymentId): array
    {
        foreach ([false, true] as $fresh) {
            $request = $this->baseRequest()->withToken($this->accessToken($fresh, $paymentId));
            $result = $this->logger->send($request, self::SERVICE, $action, $method, $url, $body !== null ? ['json' => $body] : [], $paymentId);

            if ($result['response'] && $result['response']->status() === 401 && !$fresh) {
                continue;
            }

            return $this->decode($result, $action);
        }
    }

    private function decode(array $result, string $action): array
    {
        if ($result['error'] || !$result['response']) {
            throw AreebaException::make("Areeba {$action} unreachable: ".$result['error']);
        }

        $response = $result['response'];
        $json = $response->json();
        if (!is_array($json)) {
            throw AreebaException::make("Areeba {$action} returned a non-JSON response (HTTP {$response->status()})", [], $response->status());
        }
        if ($response->failed()) {
            throw AreebaException::make("Areeba {$action} failed (HTTP {$response->status()}): ".(self::errorMessage($json) ?: 'no message'), ['response' => $json], $response->status());
        }

        $json['__httpStatus'] = $response->status();

        return $json;
    }

    private function baseRequest(): PendingRequest
    {
        $request = Http::acceptJson()
            ->timeout((int) $this->config['timeout'])
            ->connectTimeout(10);

        $key = Secret::reveal($this->config['subscription_key'] ?? null);
        if ($key) {
            $request = $request->withHeaders([$this->config['subscription_header'] => $key]);
        }

        return $request;
    }

    private function url(string $path): string
    {
        return rtrim($this->config['base_url'], '/').'/'.trim($this->config['env_path'], '/').$path;
    }

    public static function errorMessage(array $json): ?string
    {
        $message = Payload::find($json, ['errorMessage', 'error_description', 'message', 'error', 'title', 'statusDescription']);

        return is_scalar($message) ? (string) $message : null;
    }
}
