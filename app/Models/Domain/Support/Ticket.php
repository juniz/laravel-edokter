<?php

namespace App\Models\Domain\Support;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Customer\Customer;

class Ticket extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'customer_id',
        'subject',
        'status',
        'priority',
        'sla_due_at',
    ];

    protected function casts(): array
    {
        return [
            'sla_due_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }
}
