<?php

namespace App\Http\Requests\Domain\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'string'],
            'items.*.plan_id' => ['nullable', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_cents' => ['required', 'integer', 'min:0'],
            'items.*.total_cents' => ['required', 'integer', 'min:0'],
            'subtotal_cents' => ['required', 'integer', 'min:0'],
            'discount_cents' => ['nullable', 'integer', 'min:0'],
            'tax_cents' => ['nullable', 'integer', 'min:0'],
            'total_cents' => ['required', 'integer', 'min:0'],
            'coupon_id' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }
}
