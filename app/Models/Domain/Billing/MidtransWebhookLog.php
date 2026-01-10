<?php

namespace App\Models\Domain\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MidtransWebhookLog extends Model
{
    protected $table = 'midtrans_webhook_logs';

    protected $fillable = [
        'order_id',
        'payment_id',
        'transaction_status',
        'fraud_status',
        'payment_type',
        'status_code',
        'status_message',
        'processing_status',
        'error_message',
        'payload',
        'response',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'response' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
