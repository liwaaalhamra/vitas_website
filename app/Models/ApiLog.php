<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

/**
 * Immutable record of one outbound HTTP call. Created by App\Services\ApiLogger.
 */
class ApiLog extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = [];

    protected $casts = [
        'request_payload' => 'array',
        'created_at'      => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function () {
            throw new LogicException('api_logs is append-only');
        });
        static::deleting(function () {
            throw new LogicException('api_logs is append-only');
        });
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /** Response payload decoded when it is JSON, raw text otherwise. */
    public function getResponseDecodedAttribute()
    {
        $decoded = json_decode((string) $this->response_payload, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $this->response_payload;
    }
}
