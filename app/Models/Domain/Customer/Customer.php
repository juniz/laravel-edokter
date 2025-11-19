<?php

namespace App\Models\Domain\Customer;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Domain\Order\Order;
use App\Models\Domain\Billing\Invoice;
use App\Models\Domain\Subscription\Subscription;
use App\Models\Domain\Support\Ticket;
use App\Models\Domain\Customer\Domain;
use App\Models\Domain\Order\Cart;

class Customer extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'tax_number',
        'billing_address_json',
        // RDASH required fields
        'organization',
        'street_1',
        'street_2',
        'city',
        'state',
        'country_code',
        'postal_code',
        'fax',
        // RDASH sync fields
        'rdash_customer_id',
        'rdash_synced_at',
        'rdash_sync_status',
        'rdash_sync_error',
    ];

    protected function casts(): array
    {
        return [
            'billing_address_json' => 'array',
            'rdash_synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
