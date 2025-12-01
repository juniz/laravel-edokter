<?php

namespace App\Models\Domain\Ssl;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SslProduct extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'ssl_products';

    protected $fillable = [
        'rdash_ssl_product_id',
        'provider',
        'brand',
        'name',
        'ssl_type',
        'is_wildcard',
        'is_refundable',
        'max_period',
        'status',
        'features',
        'price_cents',
        'currency',
        'rdash_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'is_wildcard' => 'boolean',
            'is_refundable' => 'boolean',
            'max_period' => 'integer',
            'status' => 'integer',
            'features' => 'array',
            'price_cents' => 'integer',
            'rdash_synced_at' => 'datetime',
        ];
    }
}
