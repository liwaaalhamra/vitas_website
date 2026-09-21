<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Services\Areeba\AreebaPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Re-queries the gateway for payments that started but never reached a terminal
 * state (customer abandoned, callback lost, callback raced the browser return,
 * transient 3DS PENDING). Safe to run often — syncFromGateway is idempotent.
 */
class ReconcilePayments extends Command
{
    protected $signature = 'payments:reconcile {--limit=200 : Max payments to process}';

    protected $description = 'Reconcile pending/redirected gateway payments against the gateway status API';

    public function handle(AreebaPaymentService $areeba): int
    {
        $payments = Payment::stale()
            ->where('gateway', 'areeba')
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        $this->info("Reconciling {$payments->count()} payment(s)...");

        $changed = 0;

        foreach ($payments as $payment) {
            $before = $payment->status;

            try {
                $payment = $areeba->syncFromGateway($payment);
            } catch (Throwable $e) {
                Log::channel('payments')->error('payments.reconcile.error', [
                    'reference' => $payment->reference,
                    'error'     => $e->getMessage(),
                ]);
                $this->warn("  {$payment->reference}: {$e->getMessage()}");
                continue;
            }

            if ($payment->status !== $before) {
                $changed++;
                $this->line("  {$payment->reference}: {$before} -> {$payment->status}");
            }
        }

        // Retry posting paid installments back to the Vitas core.
        $unconfirmed = Payment::needingCoreConfirmation()
            ->where('gateway', 'areeba')
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->get();

        $confirmed = 0;

        foreach ($unconfirmed as $payment) {
            try {
                $areeba->confirmToCore($payment);
            } catch (Throwable $e) {
                Log::channel('payments')->error('payments.reconcile.confirm_error', [
                    'reference' => $payment->reference,
                    'error'     => $e->getMessage(),
                ]);
                continue;
            }

            if ($payment->fresh()->confirmed_at) {
                $confirmed++;
                $this->line("  {$payment->reference}: confirmed to core");
            }
        }

        $this->info("Done. {$changed} payment(s) changed status, {$confirmed} confirmed to core.");

        return self::SUCCESS;
    }
}
