<?php

namespace App\Models\Domain\Billing;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'refunds';

    protected $fillable = [
        'payment_id',
        'amount_cents',
        'reason',
        'status',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'raw_payload' => 'array',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
