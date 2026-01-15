<?php

namespace App\Models\Domain\Order;

use App\Models\Domain\Catalog\Product;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'unit_price_cents',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'meta' => 'array',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
