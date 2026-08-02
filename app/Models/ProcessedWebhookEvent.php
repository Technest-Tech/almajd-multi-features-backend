<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tracks webhook event IDs we've already processed, so retried/duplicate
 * deliveries (e.g. from XPay) are handled idempotently.
 */
class ProcessedWebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
    ];
}
