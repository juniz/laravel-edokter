<?php

namespace App\Http\Requests\Domain\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'product_type_id' => ['required', 'string', 'exists:product_types,id'],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
