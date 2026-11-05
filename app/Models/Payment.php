<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single payment attempt through the Areeba hosted checkout.
 *
 * Status lifecycle:   initiated -> session_created -> paid | failed | cancelled
 * Banking push:       null (not paid) -> pending -> success | failed
 *
 * Every step is in payment_events, every outbound HTTP call in api_logs.
 *
 * @property string $reference   our order reference, sent to Areeba as "pt"
 * @property string $channel     app|web
 * @property string $status
 */
class Payment extends Model
{
    public const OPEN_STATUSES = ['initiated', 'session_created'];

    public const TERMINAL_STATUSES = ['paid', 'failed', 'cancelled'];

    public const PUSH_PENDING = 'pending';
    public const PUSH_SUCCESS = 'success';
    public const PUSH_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'meta'                    => 'array',
        'areeba_response_json'    => 'array',
        'banking_response_json'   => 'array',
        'paid_at'                 => 'datetime',
        'confirmed_at'            => 'datetime',
        'banking_pushed_at'       => 'datetime',
        'banking_last_attempt_at' => 'datetime',
        'due_date'                => 'date',
        'amount'                  => 'decimal:2',
        'amount_usd'              => 'decimal:2',
        'amount_iqd'              => 'decimal:2',
        'exchange_rate'           => 'decimal:4',
    ];

    public function events()
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function apiLogs()
    {
        return $this->hasMany(ApiLog::class);
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
     * Move to a new status. Terminal statuses are sticky, with one exception:
     * a gateway-verified "paid" overrides failed / cancelled, because money
     * actually taken must never be lost behind an earlier customer cancel.
     */
    public function markStatus(string $status, ?string $reason = null): bool
    {
        if ($this->isPaid() && $status !== 'paid') {
            return false;
        }
        if ($this->isTerminal() && $status !== 'paid' && $status !== $this->status) {
            return false;
        }

        $this->status = $status;
        $this->status_reason = $reason;

        if ($status === 'paid' && $this->paid_at === null) {
            $this->paid_at = now();
            $this->banking_push_status = $this->banking_push_status ?: self::PUSH_PENDING;
        }

        $this->save();

        return true;
    }

    /** Merge one stage ("session", "transaction", "webhook", "return") into areeba_response_json. */
    public function storeAreebaResponse(string $stage, $response): void
    {
        $stored = $this->areeba_response_json ?? [];
        $stored[$stage] = $response;
        $this->areeba_response_json = $stored;
        $this->save();
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

    /** Open payments old enough to re-check with the gateway (but not ancient). */
    public function scopeStale($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES)
            ->where('created_at', '<=', now()->subMinutes(15))
            ->where('created_at', '>=', now()->subDays(7));
    }

    /** Paid payments the core banking has not acknowledged yet and are still retried automatically. */
    public function scopeNeedingBankingPush($query)
    {
        return $query->where('status', 'paid')
            ->whereIn('banking_push_status', [self::PUSH_PENDING, self::PUSH_FAILED])
            ->where('banking_push_attempts', '<', (int) config('services.vitas_core.push_max_attempts', 10))
            ->where('paid_at', '>=', now()->subDays(7));
    }

    public function getBorrowerNameMaskedAttribute(): ?string
    {
        return \App\Support\Mask::name($this->customer_name);
    }
}
