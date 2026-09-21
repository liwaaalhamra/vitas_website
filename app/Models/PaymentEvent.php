<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit trail for a Payment. One row per meaningful step
 * (init request/response, redirect, callback, status query/response,
 * points award, error). Never updated.
 */
class PaymentEvent extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'context'    => 'array',
        'created_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
