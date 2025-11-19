<?php

namespace App\Models\Domain\Billing;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'description',
        'qty',
        'unit_price_cents',
        'total_cents',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_cents' => 'integer',
            'total_cents' => 'integer',
            'meta' => 'array',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
