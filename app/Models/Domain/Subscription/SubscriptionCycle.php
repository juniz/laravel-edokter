<?php

namespace App\Models\Domain\Subscription;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Billing\Payment;

class SubscriptionCycle extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'subscription_cycles';

    protected $fillable = [
        'subscription_id',
        'cycle_no',
        'period_start',
        'period_end',
        'invoice_id',
        'payment_id',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
