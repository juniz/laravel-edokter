<?php

namespace App\Models\Domain\Customer;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'domains';

    protected $fillable = [
        'customer_id',
        'name',
        'status',
        'whois_json',
        'auto_renew',
        // RDASH Integration Fields
        'rdash_domain_id',
        'rdash_synced_at',
        'rdash_sync_status',
        'rdash_verification_status',
        'rdash_required_document',
    ];

    protected function casts(): array
    {
        return [
            'whois_json' => 'array',
            'auto_renew' => 'boolean',
            'rdash_synced_at' => 'datetime',
            'rdash_required_document' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
