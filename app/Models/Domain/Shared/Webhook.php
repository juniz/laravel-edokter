<?php

namespace App\Models\Domain\Shared;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $table = 'webhooks';

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'active' => 'boolean',
        ];
    }
}
