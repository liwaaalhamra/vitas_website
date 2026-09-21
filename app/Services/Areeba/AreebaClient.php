<?php

namespace App\Services\Areeba;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Thin HTTP wrapper around the Areeba (IXOPAY) Transaction + Status APIs.
 *
 *   Init:    POST {base}/transaction/{apiKey}/debit
 *   Status:  GET  {base}/status/{apiKey}/getByMerchantTransactionId/{ref}
 *
 * Auth is HTTP Basic with the API user credentials. Every call is logged to the
 * "payments" channel with the Authorization header and any PAN-ish fields
 * stripped.
 */
class AreebaClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $username;
    private string $password;
    private int $timeout;

    public function __construct(?array $config = null)
    {
        $config = $config ?: config('services.areeba');

        $this->baseUrl  = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey   = trim((string) ($config['api_key'] ?? ''));
        $this->username = (string) ($config['username'] ?? '');
        $this->password = (string) ($config['password'] ?? '');
        $this->timeout  = (int) ($config['timeout'] ?? 30);
    }

    /**
     * Create a debit transaction. Returns the decoded JSON body.
     *
     * @throws AreebaException on transport failure or a non-JSON response.
     */
    public function debit(array $payload): array
    {
        $url = "{$this->baseUrl}/transaction/" . rawurlencode($this->apiKey) . '/debit';

        return $this->send('POST', $url, $payload);
    }

    /**
     * Retrieve the authoritative status of a transaction by our merchant
     * transaction id (the Payment reference).
     *
     * @throws AreebaException
     */
    public function statusByMerchantTransactionId(string $reference): array
    {
        $url = "{$this->baseUrl}/status/" . rawurlencode($this->apiKey)
            . '/getByMerchantTransactionId/' . rawurlencode($reference);

        return $this->send('GET', $url);
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->acceptJson()
            ->asJson()
            ->timeout($this->timeout)
            ->connectTimeout(10);
    }

    private function send(string $method, string $url, ?array $payload = null): array
    {
        $this->log('request', ['method' => $method, 'url' => $url, 'body' => $this->redact($payload ?? [])]);

        try {
            $response = $method === 'GET'
                ? $this->client()->get($url)
                : $this->client()->post($url, $payload ?? []);
        } catch (Throwable $e) {
            $this->log('transport_error', ['url' => $url, 'error' => $e->getMessage()]);
            throw AreebaException::make('Areeba gateway unreachable: ' . $e->getMessage(), ['url' => $url]);
        }

        $json = $response->json();

        $this->log('response', [
            'url'    => $url,
            'status' => $response->status(),
            'body'   => is_array($json) ? $this->redact($json) : (string) $response->body(),
        ]);

        if (!is_array($json)) {
            throw AreebaException::make('Areeba returned a non-JSON response', [
                'url'    => $url,
                'status' => $response->status(),
            ], $response->status());
        }

        $json['__httpStatus'] = $response->status();

        return $json;
    }

    /**
     * Never persist card data or credentials.
     */
    private function redact(array $data): array
    {
        $blocked = ['password', 'cardNumber', 'cvv', 'cvc', 'pan', 'authorization'];

        array_walk_recursive($data, function (&$value, $key) use ($blocked) {
            if (is_string($key) && in_array(strtolower($key), $blocked, true)) {
                $value = '***';
            }
        });

        return $data;
    }

    private function log(string $event, array $context): void
    {
        Log::channel('payments')->info("areeba.client.{$event}", $context);
    }
}
