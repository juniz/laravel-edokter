<?php

namespace App\Models\Domain\Order;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Catalog\Coupon;
use App\Models\Domain\Billing\Invoice;

class Order extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'orders';

    protected $fillable = [
        'customer_id',
        'status',
        'currency',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'total_cents',
        'coupon_id',
        'placed_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'discount_cents' => 'integer',
            'tax_cents' => 'integer',
            'total_cents' => 'integer',
            'placed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
