<?php

namespace App\Services\Areeba;

use App\Models\Api\AppUsers;
use App\Models\Api\AppUsersPoint;
use App\Models\Payment;
use App\Services\ApiLogger;
use App\Services\VitasCoreClient;
use App\Support\Payload;
use App\Support\Secret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Orchestrates an Areeba hosted-checkout loan payment:
 *
 *   startCheckout()  initiated -> session_created (customer sent to checkoutUrl)
 *   verify()         GetTransaction is the single source of truth -> paid | failed | cancelled
 *   pushToBanking()  paid -> CustomerPaymentDetails on the core banking system
 *
 * verify() and pushToBanking() run under a row lock (SELECT ... FOR UPDATE) so the
 * browser return, the webhook and the reconcile command can race safely without
 * double-marking or double-posting a payment.
 */
class AreebaPaymentService
{
    const PAID = ['approved', 'success', 'successful', 'succeeded', 'paid', 'captured', 'completed', 'settled'];
    const FAILED = ['declined', 'failed', 'failure', 'error', 'rejected', 'denied'];
    const CANCELLED = ['cancelled', 'canceled', 'voided', 'void', 'expired', 'aborted'];

    public function __construct(
        private AreebaClient $client,
        private VitasCoreClient $core
    ) {
    }

    /**
     * Create the local order from a normalized core inquiry and open the
     * Areeba checkout session. The returned Payment has ->redirect_url set.
     *
     * @throws AreebaException
     */
    public function startCheckout(array $inquiry, string $channel, ?int $appUserId = null, ?string $locale = null): Payment
    {
        $payment = Payment::create([
            'reference'          => $this->newReference(),
            'channel'            => $channel,
            'locale'             => $locale ?: app()->getLocale(),
            'gateway'            => 'areeba',
            'purpose'            => 'loan_installment',
            'status'             => 'initiated',
            'app_user_id'        => $appUserId,
            'client_id'          => $inquiry['client_id'] ?? null,
            'loan_number'        => $inquiry['loan_number'],
            'branch_code'        => $inquiry['branch_code'] ?? null,
            'branch_name'        => $inquiry['branch_name'] ?? null,
            'loan_product'       => $inquiry['loan_product'] ?? null,
            'due_date'           => $inquiry['due_date'] ?? null,
            'installment_number' => $inquiry['installment_number'] ?? null,
            'phone_number'       => $inquiry['phone_number'] ?? null,
            'customer_name'      => $inquiry['customer_name'] ?? null,
            'amount'             => $inquiry['amount_iqd'],
            'amount_iqd'         => $inquiry['amount_iqd'],
            'currency'           => config('services.areeba.currency', 'IQD'),
            'meta'               => ['inquiry' => $inquiry['raw'] ?? null],
        ]);
        $payment->logEvent('initiated', ['message' => $payment->amount.' '.$payment->currency.' for loan '.$payment->loan_number]);

        try {
            $session = $this->client->createCheckoutSession($payment, route('payment.return', ['ref' => $payment->reference]));
        } catch (AreebaException $e) {
            $payment->logEvent('error', ['message' => $e->getMessage(), 'context' => $e->context]);
            $payment->markStatus('failed', Str::limit('checkout session: '.$e->getMessage(), 240));
            throw $e;
        }

        unset($session['raw']['__httpStatus']);
        $payment->storeAreebaResponse('session', $session['raw']);
        $payment->forceFill([
            'redirect_url'          => $session['checkout_url'],
            'areeba_transaction_id' => $this->transactionIdFrom($session['raw']),
        ])->save();
        $payment->markStatus('session_created');
        $payment->logEvent('session_created', ['message' => $session['checkout_url']]);

        return $payment->refresh();
    }

    /**
     * Verify the payment against Areeba GetTransaction and apply the result.
     * Idempotent and safe to call concurrently.
     *
     * @param  string|null  $transactionId  Areeba transaction id reported by the return / webhook
     *                                       (only ever used to query Areeba, never trusted as a result)
     */
    public function verify(Payment $payment, ?string $transactionId = null, string $source = 'manual'): Payment
    {
        $payment = DB::transaction(function () use ($payment, $transactionId, $source) {
            /** @var Payment $locked */
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                return $locked;
            }

            $transactionId = $transactionId ?: $locked->areeba_transaction_id;
            if (!$transactionId) {
                return $locked;
            }

            // A transaction id belongs to exactly one order
            if (Payment::where('areeba_transaction_id', $transactionId)->whereKeyNot($locked->id)->exists()) {
                $locked->logEvent('alert', ['message' => "{$source}: transaction {$transactionId} already belongs to another payment"]);

                return $locked;
            }

            try {
                $transaction = $this->client->getTransaction($transactionId, $locked->id);
            } catch (AreebaException $e) {
                $locked->logEvent('error', ['message' => "{$source}: GetTransaction failed: ".$e->getMessage()]);

                return $locked;
            }

            unset($transaction['__httpStatus']);
            $locked->areeba_transaction_id = $transactionId;
            $locked->storeAreebaResponse('transaction', $transaction);
            $this->applyTransaction($locked, $transaction, $source);

            return $locked->refresh();
        });

        if ($payment->isPaid() && $payment->banking_push_status === Payment::PUSH_PENDING) {
            $this->awardLoyaltyPoints($payment);
            $payment = $this->pushToBanking($payment);
        }

        return $payment;
    }

    /**
     * Post a paid payment to the core banking system. Row locked so two
     * concurrent callers can never push the same payment twice.
     */
    public function pushToBanking(Payment $payment, ?string $triggeredBy = null): Payment
    {
        if (!config('services.vitas_core.push_enabled', true)) {
            $payment->logEvent('banking_push', ['message' => 'skipped (CORE_BANKING_PUSH_ENABLED=false)']);

            return $payment;
        }

        return DB::transaction(function () use ($payment, $triggeredBy) {
            /** @var Payment $locked */
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if (!$locked->isPaid() || $locked->banking_push_status === Payment::PUSH_SUCCESS) {
                return $locked;
            }

            $locked->forceFill([
                'banking_push_attempts'   => (int) $locked->banking_push_attempts + 1,
                'banking_last_attempt_at' => now(),
            ])->save();

            try {
                $result = $this->core->pushPayment($locked);
            } catch (Throwable $e) {
                $result = ['ok' => false, 'http_status' => null, 'status_code' => null, 'action_status' => Str::limit($e->getMessage(), 240), 'body' => null];
            }

            $locked->forceFill([
                'banking_push_status'   => $result['ok'] ? Payment::PUSH_SUCCESS : Payment::PUSH_FAILED,
                'banking_status_code'   => $result['status_code'] ?? ($result['http_status'] ? 'HTTP '.$result['http_status'] : null),
                'banking_action_status' => $result['action_status'] !== null ? Str::limit((string) $result['action_status'], 490) : null,
                'banking_response_json' => is_array($result['body']) ? $result['body'] : ['raw' => $result['body']],
                'banking_pushed_at'     => $result['ok'] ? now() : $locked->banking_pushed_at,
            ])->save();

            $locked->logEvent('banking_push', [
                'http_status' => $result['http_status'],
                'message'     => ($result['ok'] ? 'success' : 'failed').' (attempt '.$locked->banking_push_attempts
                    .($triggeredBy ? ', by '.$triggeredBy : '').')'.($result['action_status'] ? ': '.$result['action_status'] : ''),
            ]);

            return $locked->refresh();
        });
    }

    /**
     * Server-to-server notification (/payment/webhook). Rejected unless the
     * HMAC signature matches; the body only tells us which payment to
     * re-verify, the outcome always comes from GetTransaction.
     *
     * @return array{0: int, 1: string}  [http status, body]
     */
    public function handleWebhook(Request $request): array
    {
        if (!$this->validSignature($request)) {
            Log::channel('payments')->warning('areeba.webhook.invalid_signature', [
                'ip'     => $request->ip(),
                'header' => $request->header(config('services.areeba.webhook_header')),
                'body'   => Str::limit($request->getContent(), 2000),
            ]);

            return [401, 'invalid signature'];
        }

        $body = json_decode($request->getContent(), true);
        $body = is_array($body) ? $body : $request->all();

        $reference = Payload::find($body, ['pt', 'merchantReference', 'orderReference', 'orderId', 'reference']);
        $transactionId = $this->transactionIdFrom($body);

        $payment = null;
        if ($reference) {
            $payment = Payment::where('reference', (string) $reference)->first();
        }
        if (!$payment && $transactionId) {
            $payment = Payment::where('areeba_transaction_id', (string) $transactionId)->first();
        }

        if (!$payment) {
            Log::channel('payments')->warning('areeba.webhook.unknown_payment', ['body' => $body]);

            return [200, 'unknown payment'];
        }

        $payment->logEvent('webhook', ['context' => ApiLogger::redact($body)]);
        $payment->storeAreebaResponse('webhook', $body);
        $this->verify($payment, $transactionId ? (string) $transactionId : null, 'webhook');

        return [200, 'OK'];
    }

    /**
     * Customer browser returning from the hosted checkout.
     */
    public function handleReturn(Payment $payment, Request $request): Payment
    {
        $params = $request->except(['_token']);
        $payment->logEvent('return', ['context' => $params]);
        $payment->storeAreebaResponse('return', $params);

        $transactionId = $this->transactionIdFrom($params);
        $payment = $this->verify($payment, $transactionId ? (string) $transactionId : null, 'return');

        // No transaction to verify and the customer explicitly cancelled at the gateway.
        // Still overridable by a later verified "paid" (see Payment::markStatus).
        $outcome = strtolower((string) Payload::find($params, ['status', 'outcome', 'result']));
        if (!$payment->isTerminal() && !$payment->areeba_transaction_id && in_array($outcome, self::CANCELLED, true)) {
            $payment->markStatus('cancelled', 'cancelled by customer at checkout');
            $payment->logEvent('cancelled', ['message' => 'customer cancelled at checkout']);
        }

        return $payment->refresh();
    }

    /**
     * Close a checkout session that was never completed.
     */
    public function expire(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if (!$locked->isTerminal()) {
                $locked->markStatus('cancelled', 'checkout session expired');
                $locked->logEvent('cancelled', ['message' => 'checkout session expired without a transaction']);
            }

            return $locked->refresh();
        });
    }

    /**
     * Loyalty points for app payments (same as the legacy ZainCash flow).
     * Idempotent, only when a positive point value is configured.
     */
    public function awardLoyaltyPoints(Payment $payment): void
    {
        $points = (float) config('services.areeba.loyalty_points', 0);

        if ($points <= 0 || $payment->channel !== 'app' || !$payment->client_id) {
            return;
        }

        $awarded = DB::transaction(function () use ($payment, $points) {
            $already = AppUsersPoint::where('meta_name', 'areeba')
                ->where('meta_value', $payment->reference)
                ->lockForUpdate()
                ->exists();

            if ($already) {
                return false;
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

            return true;
        });

        if ($awarded) {
            $payment->logEvent('points_awarded', ['message' => $points.' points -> client '.$payment->client_id]);
        }
    }

    // ------------------------------------------------------------------ helpers

    private function applyTransaction(Payment $payment, array $transaction, string $source): void
    {
        $reported = Payload::find($transaction, ['pt', 'merchantReference', 'orderReference', 'orderId']);
        if ($reported !== null && (string) $reported !== $payment->reference) {
            $payment->logEvent('alert', ['message' => "{$source}: transaction belongs to order {$reported}, not {$payment->reference}"]);

            return;
        }

        $status = strtolower(trim((string) Payload::find($transaction, ['transactionStatus', 'paymentStatus', 'status', 'statusName', 'state', 'result'])));
        $payment->logEvent('verified', ['message' => "{$source}: Areeba status '{$status}'"]);

        if (in_array($status, self::PAID, true)) {
            $mismatch = $this->amountMismatch($payment, $transaction);
            if ($mismatch) {
                $payment->markStatus('failed', 'amount mismatch: '.$mismatch);
                $payment->logEvent('alert', ['message' => 'paid with amount mismatch, NOT posted to core banking: '.$mismatch]);

                return;
            }
            $payment->markStatus('paid');

            return;
        }

        if (in_array($status, self::FAILED, true)) {
            $payment->markStatus('failed', Str::limit('Areeba: '.(AreebaClient::errorMessage($transaction) ?: $status), 240));
        } elseif (in_array($status, self::CANCELLED, true)) {
            $payment->markStatus('cancelled', 'Areeba: '.$status);
        }
        // anything else (pending, processing, ...) stays open for a later check
    }

    private function amountMismatch(Payment $payment, array $transaction): ?string
    {
        $amount = Payload::find($transaction, ['chargeAmount', 'amount', 'totalAmount', 'transactionAmount']);
        $currency = Payload::find($transaction, ['currency', 'currencyCode']);

        if ($amount !== null && abs((float) $amount - (float) $payment->amount) >= 0.01) {
            return "gateway {$amount}, expected {$payment->amount}";
        }
        if ($currency !== null && !is_numeric($currency) && strtoupper((string) $currency) !== strtoupper($payment->currency)) {
            return "gateway currency {$currency}, expected {$payment->currency}";
        }

        return null;
    }

    private function validSignature(Request $request): bool
    {
        $secret = Secret::reveal(config('services.areeba.webhook_secret'));
        if (!$secret) {
            Log::channel('payments')->error('areeba.webhook.secret_not_configured');

            return false;
        }

        $given = trim((string) $request->header(config('services.areeba.webhook_header', 'X-Signature')));
        $given = preg_replace('/^[a-z0-9]+=/i', '', $given); // "sha256=..." form
        if ($given === '') {
            return false;
        }

        $algo = config('services.areeba.webhook_algo', 'sha256');
        $raw = hash_hmac($algo, $request->getContent(), $secret, true);

        return hash_equals(bin2hex($raw), strtolower($given)) || hash_equals(base64_encode($raw), $given);
    }

    private function transactionIdFrom(array $data): ?string
    {
        $id = Payload::find($data, ['transactionId', 'txId', 'transaction_id', 'TransactionID', 'tranId']);

        return $id !== null ? (string) $id : null;
    }

    private function newReference(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(8));
    }
}
