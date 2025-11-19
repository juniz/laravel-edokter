<?php

namespace App\Models\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Order\OrderItem;
use App\Models\Domain\Subscription\Subscription;

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
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
