<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single payment attempt against an external gateway (currently Areeba).
 *
 * These two tables (payments + payment_events) are the system of record for
 * gateway payments. Nothing is posted back to the Vitas core.
 *
 * @property string $reference
 * @property string $channel   app|web
 * @property string $status    pending|redirected|paid|failed|declined|cancelled|expired|error|mismatch
 */
class Payment extends Model
{
    /** Non terminal statuses that a status sync is still allowed to move forward. */
    public const OPEN_STATUSES = ['pending', 'redirected'];

    /** Statuses that are final and must never be overwritten by a later sync. */
    public const TERMINAL_STATUSES = ['paid', 'failed', 'declined', 'cancelled', 'expired', 'error', 'mismatch'];

    protected $guarded = [];

    protected $casts = [
        'meta'          => 'array',
        'paid_at'       => 'datetime',
        'confirmed_at'  => 'datetime',
        'amount'        => 'decimal:2',
        'amount_usd'    => 'decimal:2',
        'amount_iqd'    => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function events()
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES, true);
    }

    /**
     * Move the payment to a new status. Terminal statuses are sticky: once the
     * payment is settled we never let a duplicate callback / reconcile pass
     * drag it back.
     */
    public function markStatus(string $status, ?string $reason = null): bool
    {
        if ($this->isTerminal() && $status !== $this->status) {
            return false;
        }

        $this->status = $status;
        $this->status_reason = $reason;

        if ($status === 'paid' && $this->paid_at === null) {
            $this->paid_at = now();
        }

        $this->save();

        return true;
    }

    /**
     * Append an entry to the immutable event log for this payment.
     */
    public function logEvent(string $type, array $data = []): PaymentEvent
    {
        return $this->events()->create([
            'type'        => $type,
            'http_status' => $data['http_status'] ?? null,
            'message'     => $data['message'] ?? null,
            'context'     => $data['context'] ?? null,
            'created_at'  => now(),
        ]);
    }

    /**
     * Payments that started but never reached a terminal state and are old
     * enough to be worth re-querying (but not ancient).
     */
    public function scopeStale($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES)
            ->where('created_at', '<=', now()->subMinutes(15))
            ->where('created_at', '>=', now()->subDays(3));
    }

    /**
     * Paid payments whose confirmation to the Vitas core has not gone through
     * yet (and is still worth retrying).
     */
    public function scopeNeedingCoreConfirmation($query)
    {
        return $query->where('status', 'paid')
            ->whereNull('confirmed_at')
            ->where('confirm_attempts', '<', 10)
            ->where('paid_at', '>=', now()->subDays(7));
    }
}
