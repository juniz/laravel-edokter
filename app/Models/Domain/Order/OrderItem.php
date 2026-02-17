<?php

namespace App\Models\Domain\Order;

use App\Models\Domain\Catalog\Plan;
use App\Models\Domain\Catalog\Product;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'subscription_id',
        'qty',
        'unit_price_cents',
        'total_cents',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'total_cents' => 'integer',
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
