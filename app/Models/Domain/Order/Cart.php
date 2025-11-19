<?php

namespace App\Models\Domain\Order;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Catalog\Coupon;

class Cart extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'carts';

    protected $fillable = [
        'customer_id',
        'coupon_id',
        'currency',
        'totals_json',
    ];

    protected function casts(): array
    {
        return [
            'totals_json' => 'array',
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
        return $this->hasMany(CartItem::class);
    }
}
