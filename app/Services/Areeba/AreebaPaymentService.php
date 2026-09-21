<?php

namespace App\Services\Areeba;

use App\Models\Api\AppUsers;
use App\Models\Api\AppUsersPoint;
use App\Models\Payment;
use App\Services\VitasCoreClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orchestrates an Areeba loan-installment payment: builds the local Payment
 * record, opens the gateway transaction, and reconciles the outcome from the
 * gateway's status API (which is treated as the single source of truth — the
 * async callback is only a "wake up and re-check" ping).
 */
class AreebaPaymentService
{
    public function __construct(
        private AreebaClient $client,
        private VitasCoreClient $core
    ) {
    }

    /**
     * Create a Payment from a CustomerDataInquiry result and open the Areeba
     * transaction. On success the returned Payment has ->redirect_url set.
     *
     * @param  array        $inquiry  Raw CustomerDataInquiry response.
     * @param  string       $channel  'app' | 'web'
     * @param  int|null     $appUserId  app_users.id when initiated from the mobile app.
     *
     * @throws AreebaException
     */
    public function startLoanPayment(array $inquiry, string $channel, ?int $appUserId = null): Payment
    {
        [$amount, $currency] = $this->resolveAmount($inquiry);

        $payment = Payment::create([
            'reference'          => $this->newReference(),
            'channel'            => $channel,
            'gateway'            => 'areeba',
            'purpose'            => 'loan_installment',
            'status'             => 'pending',
            'app_user_id'        => $appUserId,
            'client_id'          => $inquiry['ClientID'] ?? null,
            'loan_number'        => $inquiry['LoanNumber'] ?? null,
            'branch_code'        => $inquiry['BranchCode'] ?? null,
            'installment_number' => $inquiry['PaymentNumber'] ?? null,
            'phone_number'       => $inquiry['PhoneNumber'] ?? null,
            'customer_name'      => $inquiry['CustomerName'] ?? ($inquiry['ClientName'] ?? null),
            'amount'             => $amount,
            'currency'           => $currency,
            'amount_usd'         => $inquiry['TotalPaymentDueUSD'] ?? null,
            'amount_iqd'         => $inquiry['TotalPaymentDueIQD'] ?? null,
            'exchange_rate'      => $inquiry['ExchangeRate'] ?? null,
            'meta'               => ['inquiry' => $inquiry],
        ]);

        $payload = $this->buildDebitPayload($payment);
        $payment->logEvent('init_request', ['context' => $this->redactPayload($payload)]);

        try {
            $response = $this->client->debit($payload);
        } catch (AreebaException $e) {
            $payment->logEvent('error', ['message' => $e->getMessage(), 'context' => $e->context]);
            $payment->markStatus('error', Str::limit($e->getMessage(), 240));
            throw $e;
        }

        $payment->logEvent('init_response', [
            'http_status' => $response['__httpStatus'] ?? null,
            'context'     => $response,
        ]);

        $ok = ($response['success'] ?? false) === true;
        $redirectUrl = $response['redirectUrl'] ?? null;

        if (!$ok || !$redirectUrl) {
            $reason = $this->extractError($response) ?: 'Gateway did not return a redirect URL';
            $payment->markStatus('error', Str::limit($reason, 240));
            throw AreebaException::make('Areeba init failed: ' . $reason, ['response' => $response]);
        }

        $payment->forceFill([
            'gateway_uuid'        => $response['uuid'] ?? null,
            'gateway_purchase_id' => $response['purchaseId'] ?? null,
            'payment_method'      => $response['paymentMethod'] ?? null,
            'redirect_url'        => $redirectUrl,
        ])->save();

        $payment->markStatus('redirected');
        $payment->logEvent('redirect', ['message' => $redirectUrl]);

        return $payment->refresh();
    }

    /**
     * Pull the authoritative status for a Payment from Areeba and apply it.
     * Idempotent: safe to call from the callback, the browser return, the
     * status poll and the reconcile command.
     */
    public function syncFromGateway(Payment $payment): Payment
    {
        if ($payment->isTerminal()) {
            return $payment;
        }

        $payment->logEvent('status_request', ['message' => $payment->reference]);

        try {
            $response = $this->client->statusByMerchantTransactionId($payment->reference);
        } catch (AreebaException $e) {
            $payment->logEvent('error', ['message' => 'status query failed: ' . $e->getMessage()]);

            return $payment;
        }

        $payment->logEvent('status_response', [
            'http_status' => $response['__httpStatus'] ?? null,
            'context'     => $response,
        ]);

        $payment->forceFill([
            'meta' => array_merge($payment->meta ?? [], ['last_status' => $response]),
        ])->save();

        $status = strtoupper((string) ($response['transactionStatus'] ?? ''));

        // No status yet (e.g. "transaction not found" because the customer never
        // started). Leave the Payment open for a later retry / ageing out.
        if ($status === '' && ($response['success'] ?? null) === false) {
            $payment->logEvent('error', ['message' => $this->extractError($response) ?: 'no transactionStatus']);

            return $payment;
        }

        if (in_array($status, ['SUCCESS', 'OK'], true)) {
            if (!$this->amountsMatch($payment, $response)) {
                $payment->markStatus('mismatch', sprintf(
                    'gateway reported %s %s, expected %s %s',
                    $response['amount'] ?? '?', $response['currency'] ?? '?',
                    $payment->amount, $payment->currency
                ));
                $payment->logEvent('error', ['message' => 'amount/currency mismatch', 'context' => $response]);

                return $payment->refresh();
            }

            $payment->forceFill([
                'gateway_uuid'   => $response['uuid'] ?? $payment->gateway_uuid,
                'payment_method' => $response['paymentMethod'] ?? $payment->payment_method,
            ])->save();

            $becamePaid = !$payment->isPaid();
            $payment->markStatus('paid');

            if ($becamePaid) {
                $this->awardLoyaltyPoints($payment->refresh());
                $this->confirmToCore($payment->refresh());
            }

            return $payment->refresh();
        }

        if ($status === 'PENDING' || $status === 'NEW') {
            $payment->markStatus('pending', 'gateway PENDING');

            return $payment->refresh();
        }

        // ERROR / DECLINED / EXPIRED / CANCELLED / anything else -> failed.
        $mapped = in_array($status, ['DECLINED', 'CANCELLED', 'EXPIRED'], true)
            ? strtolower($status)
            : 'failed';

        $payment->markStatus($mapped, $this->extractError($response) ?: ('gateway ' . ($status ?: 'ERROR')));

        return $payment->refresh();
    }

    /**
     * Handle the async server-to-server notification. The body is untrusted:
     * we log it, optionally verify X-Signature, then reconcile from the status
     * API regardless.
     */
    public function handleCallback(Request $request): ?Payment
    {
        $reference = $request->input('merchantTransactionId')
            ?? $request->input('reference')
            ?? $request->query('reference');

        $payment = $reference ? Payment::where('reference', $reference)->first() : null;

        if (!$payment) {
            Log::channel('payments')->warning('areeba.callback.unknown_reference', [
                'reference' => $reference,
                'body'      => $request->all(),
            ]);

            return null;
        }

        $payment->logEvent('callback', [
            'context' => [
                'headers' => ['x-signature' => $request->header('X-Signature')],
                'body'    => $request->all(),
                'query'   => $request->query(),
            ],
        ]);

        $secret = config('services.areeba.webhook_secret');
        if ($secret) {
            $expected = base64_encode(hash_hmac('sha512', $request->getContent(), $secret, true));
            if (!hash_equals($expected, (string) $request->header('X-Signature'))) {
                $payment->logEvent('error', ['message' => 'callback X-Signature mismatch']);
            }
        }

        return $this->syncFromGateway($payment);
    }

    /**
     * Mirror of the legacy ZainCash loyalty award. Idempotent, app channel only,
     * and only when a positive point value is configured.
     */
    public function awardLoyaltyPoints(Payment $payment): void
    {
        $points = (float) config('services.areeba.loyalty_points', 0);

        if ($points <= 0 || $payment->channel !== 'app' || !$payment->client_id) {
            return;
        }

        DB::transaction(function () use ($payment, $points) {
            $already = AppUsersPoint::where('meta_name', 'areeba')
                ->where('meta_value', $payment->reference)
                ->lockForUpdate()
                ->exists();

            if ($already) {
                return;
            }

            AppUsersPoint::create([
                'user_id'     => $payment->client_id,
                'meta_name'   => 'areeba',
                'meta_value'  => $payment->reference,
                'points'      => $points,
                'description' => 'Areeba loan installment payment. Meta value is the payment reference.',
            ]);

            $user = AppUsers::where('employee_id', $payment->client_id)->lockForUpdate()->first();
            if ($user) {
                $user->total_points = (int) $user->total_points + (int) $points;
                $user->save();
            }
        });

        $payment->logEvent('points_awarded', ['message' => $points . ' points -> client ' . $payment->client_id]);
    }

    /**
     * Post the settled installment back to the Vitas core (loan system), the
     * same step the legacy ZainCash flow performed. Never lets a confirmation
     * failure break the "paid" transition — the reconcile command retries.
     */
    public function confirmToCore(Payment $payment): void
    {
        if ($payment->confirmed_at || !$payment->isPaid()) {
            return;
        }

        if (!config('services.vitas_core.confirm_enabled', true)) {
            $payment->logEvent('core_confirm_request', ['message' => 'skipped (confirm disabled)']);

            return;
        }

        $payment->forceFill(['confirm_attempts' => (int) $payment->confirm_attempts + 1])->save();
        $payment->logEvent('core_confirm_request', ['message' => 'attempt ' . $payment->confirm_attempts]);

        try {
            $ok = $this->core->confirmPayment($payment);
        } catch (Throwable $e) {
            $payment->forceFill(['confirm_error' => Str::limit($e->getMessage(), 240)])->save();
            $payment->logEvent('error', ['message' => 'core confirmation threw: ' . $e->getMessage()]);

            return;
        }

        if ($ok) {
            $payment->forceFill(['confirmed_at' => now(), 'confirm_error' => null])->save();
            $payment->logEvent('core_confirm_response', ['message' => 'confirmed by core']);
        } else {
            $payment->forceFill(['confirm_error' => 'core did not acknowledge (attempt ' . $payment->confirm_attempts . ')'])->save();
            $payment->logEvent('error', ['message' => 'core confirmation not acknowledged']);
        }
    }

    // ------------------------------------------------------------------ helpers

    private function buildDebitPayload(Payment $payment): array
    {
        [$first, $last] = $this->splitName($payment->customer_name);

        return array_filter([
            'merchantTransactionId' => $payment->reference,
            'amount'                => number_format((float) $payment->amount, 2, '.', ''),
            'currency'              => $payment->currency,
            'successUrl'            => $this->returnUrl($payment, 'success'),
            'cancelUrl'             => $this->returnUrl($payment, 'cancel'),
            'errorUrl'              => $this->returnUrl($payment, 'error'),
            'callbackUrl'           => route('areeba.callback'),
            'customer'              => array_filter([
                'firstName' => $first,
                'lastName'  => $last,
                'company'   => config('services.areeba.company_name'),
                'ipAddress' => request()->ip(),
            ]),
            'language'              => app()->getLocale() === 'ar' ? 'ar' : 'en',
        ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    }

    private function returnUrl(Payment $payment, string $outcome): string
    {
        if ($payment->channel === 'web') {
            return route('pay.result', [
                'locale'    => app()->getLocale(),
                'reference' => $payment->reference,
                'outcome'   => $outcome,
            ]);
        }

        return route('areeba.return', [
            'reference' => $payment->reference,
            'outcome'   => $outcome,
        ]);
    }

    /**
     * Currency follows the loan currency; both amounts are still stored on the
     * Payment for reporting.
     *
     * @return array{0: string, 1: string}  [amount, currency]
     */
    private function resolveAmount(array $inquiry): array
    {
        $loanCurrency = strtoupper((string) ($inquiry['LoanCurrency'] ?? 'USD'));

        if ($loanCurrency === 'IQD') {
            $amount = $inquiry['TotalPaymentDueIQD'] ?? $inquiry['PaymentDueIQD'] ?? 0;

            return [number_format((float) $amount, 2, '.', ''), 'IQD'];
        }

        $amount = $inquiry['TotalPaymentDueUSD'] ?? $inquiry['PaymentDueUSD'] ?? 0;

        return [number_format((float) $amount, 2, '.', ''), 'USD'];
    }

    private function amountsMatch(Payment $payment, array $response): bool
    {
        $sameCurrency = strtoupper((string) ($response['currency'] ?? '')) === strtoupper($payment->currency);
        $sameAmount = abs((float) ($response['amount'] ?? 0) - (float) $payment->amount) < 0.01;

        return $sameCurrency && $sameAmount;
    }

    private function extractError(array $response): ?string
    {
        if (!empty($response['errors'][0]['message'])) {
            return $response['errors'][0]['message'];
        }
        if (!empty($response['errorMessage'])) {
            return $response['errorMessage'];
        }
        if (!empty($response['message'])) {
            return $response['message'];
        }

        return null;
    }

    private function splitName(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') {
            return ['Vitas', 'Customer'];
        }

        $parts = preg_split('/\s+/', $name, 2);

        return [$parts[0], $parts[1] ?? $parts[0]];
    }

    private function newReference(): string
    {
        return 'VITAS-' . now()->format('ymd') . '-' . strtoupper(Str::random(12));
    }

    private function redactPayload(array $payload): array
    {
        if (isset($payload['customer'])) {
            $payload['customer'] = ['redacted' => true];
        }

        return $payload;
    }
}
