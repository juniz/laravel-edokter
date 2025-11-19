<?php

namespace App\Models\Domain\Catalog;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_until',
        'applicable_product_ids',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'applicable_product_ids' => 'array',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }
}
