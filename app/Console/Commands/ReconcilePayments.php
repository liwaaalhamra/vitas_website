<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Areeba\AreebaPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Safety net for lost browser returns / webhooks (scheduled every 10 minutes):
 *   - re-verifies open payments that have an Areeba transaction id
 *   - closes checkout sessions abandoned for longer than AREEBA_SESSION_TTL_HOURS
 *   - retries core banking pushes that failed (up to CORE_BANKING_PUSH_MAX_ATTEMPTS)
 * Safe to run often: verify() and pushToBanking() are idempotent and row locked.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=200 : Max payments to process per step}';

    protected $description = 'Reconcile open Areeba payments and retry failed core banking pushes';

    public function handle(AreebaPaymentService $areeba): int
    {
        $limit = (int) $this->option('limit');
        $expireBefore = now()->subHours((int) config('services.areeba.session_ttl_hours', 24));
        $changed = 0;

        $open = Payment::stale()->where('gateway', 'areeba')->orderBy('id')->limit($limit)->get();
        $this->info("Checking {$open->count()} open payment(s)...");

        foreach ($open as $payment) {
            $before = $payment->status;

            try {
                if ($payment->areeba_transaction_id) {
                    $payment = $areeba->verify($payment, null, 'reconcile');
                }
                if (!$payment->isTerminal() && $payment->created_at <= $expireBefore) {
                    $payment = $areeba->expire($payment);
                }
            } catch (Throwable $e) {
                Log::channel('payments')->error('payments.reconcile.error', ['reference' => $payment->reference, 'error' => $e->getMessage()]);
                $this->warn("  {$payment->reference}: {$e->getMessage()}");
                continue;
            }

            if ($payment->status !== $before) {
                $changed++;
                $this->line("  {$payment->reference}: {$before} -> {$payment->status}");
            }
        }

        $pushed = 0;
        $unpushed = Payment::needingBankingPush()->where('gateway', 'areeba')->orderBy('id')->limit($limit)->get();

        foreach ($unpushed as $payment) {
            try {
                $payment = $areeba->pushToBanking($payment, 'reconcile');
            } catch (Throwable $e) {
                Log::channel('payments')->error('payments.reconcile.push_error', ['reference' => $payment->reference, 'error' => $e->getMessage()]);
                continue;
            }

            if ($payment->banking_push_status === Payment::PUSH_SUCCESS) {
                $pushed++;
                $this->line("  {$payment->reference}: pushed to core banking");
            }
        }

        $this->info("Done. {$changed} payment(s) changed status, {$pushed} pushed to core banking.");

        return self::SUCCESS;
    }
}
