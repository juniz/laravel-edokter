<?php

namespace App\Models\Domain\Billing;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Order\Order;

class Invoice extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'order_id',
        'customer_id',
        'number',
        'status',
        'currency',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'total_cents',
        'due_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'due_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
