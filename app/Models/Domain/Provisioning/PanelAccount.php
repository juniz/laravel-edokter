<?php

namespace App\Models\Domain\Provisioning;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Domain\Subscription\Subscription;

class PanelAccount extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'panel_accounts';

    protected $fillable = [
        'server_id',
        'subscription_id',
        'username',
        'domain',
        'status',
        'last_sync_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'last_sync_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
