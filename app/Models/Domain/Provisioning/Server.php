<?php

namespace App\Models\Domain\Provisioning;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'servers';

    protected $fillable = [
        'name',
        'type',
        'endpoint',
        'auth_secret_ref',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function panelAccounts(): HasMany
    {
        return $this->hasMany(PanelAccount::class);
    }

    public function provisionTasks(): HasMany
    {
        return $this->hasMany(ProvisionTask::class);
    }
}
