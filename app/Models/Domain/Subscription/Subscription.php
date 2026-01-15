<?php

namespace App\Models\Domain\Subscription;

use App\Models\Domain\Catalog\Product;
use App\Models\Domain\Customer\Customer;
use App\Models\Domain\Provisioning\PanelAccount;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'subscriptions';

    protected $fillable = [
        'customer_id',
        'product_id',
        'status',
        'start_at',
        'end_at',
        'next_renewal_at',
        'auto_renew',
        'provisioning_status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'next_renewal_at' => 'datetime',
            'auto_renew' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cycles(): HasMany
    {
        return $this->hasMany(SubscriptionCycle::class);
    }

    public function panelAccount(): HasMany
    {
        return $this->hasMany(PanelAccount::class);
    }
}
