<?php

namespace App\Models\Domain\Provisioning;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Domain\Subscription\Subscription;

class ProvisionTask extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'provision_tasks';

    protected $fillable = [
        'subscription_id',
        'server_id',
        'action',
        'status',
        'attempts',
        'error',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
