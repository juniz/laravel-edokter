<?php

namespace App\Models\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Subscription\Subscription;

class Plan extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'product_id',
        'code',
        'billing_cycle',
        'price_cents',
        'currency',
        'trial_days',
        'setup_fee_cents',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'setup_fee_cents' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
