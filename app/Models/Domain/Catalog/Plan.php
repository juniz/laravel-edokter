<?php

namespace App\Models\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'product_id',
        'code',
        'price_cents',
        'currency',
        'trial_days',
        'setup_fee_cents',
        'duration_1_month_enabled',
        'duration_12_months_enabled',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'setup_fee_cents' => 'integer',
            'trial_days' => 'integer',
            'duration_1_month_enabled' => 'boolean',
            'duration_12_months_enabled' => 'boolean',
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
}

