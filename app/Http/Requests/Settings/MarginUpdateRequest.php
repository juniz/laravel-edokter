<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class MarginUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domain_margin_type' => ['required', 'string', 'in:percentage,fixed'],
            'domain_margin_value' => ['required', 'numeric', 'min:0'],
            'ssh_margin_type' => ['required', 'string', 'in:percentage,fixed'],
            'ssh_margin_value' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'domain_margin_type.required' => 'Tipe margin domain wajib diisi.',
            'domain_margin_type.in' => 'Tipe margin domain harus berupa percentage atau fixed.',
            'domain_margin_value.required' => 'Nilai margin domain wajib diisi.',
            'domain_margin_value.numeric' => 'Nilai margin domain harus berupa angka.',
            'domain_margin_value.min' => 'Nilai margin domain minimal 0.',
            'ssh_margin_type.required' => 'Tipe margin SSH wajib diisi.',
            'ssh_margin_type.in' => 'Tipe margin SSH harus berupa percentage atau fixed.',
            'ssh_margin_value.required' => 'Nilai margin SSH wajib diisi.',
            'ssh_margin_value.numeric' => 'Nilai margin SSH harus berupa angka.',
            'ssh_margin_value.min' => 'Nilai margin SSH minimal 0.',
        ];
    }
}
