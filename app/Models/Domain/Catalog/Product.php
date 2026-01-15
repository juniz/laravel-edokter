<?php

namespace App\Models\Domain\Catalog;

use App\Models\Domain\Order\OrderItem;
use App\Models\Domain\Subscription\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'type',
        'name',
        'slug',
        'status',
        'metadata',
        'price_cents',
        'currency',
        'setup_fee_cents',
        'trial_days',
        'duration_1_month_enabled',
        'duration_12_months_enabled',
        'annual_discount_percent',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'price_cents' => 'integer',
            'setup_fee_cents' => 'integer',
            'duration_1_month_enabled' => 'boolean',
            'duration_12_months_enabled' => 'boolean',
            'annual_discount_percent' => 'integer',
            'display_order' => 'integer',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(ProductFeature::class)->orderBy('display_order');
    }
}
