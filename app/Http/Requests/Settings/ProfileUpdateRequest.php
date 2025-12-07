<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // Customer fields (optional for existing customers)
            'organization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'street_1' => ['nullable', 'string', 'max:255'],
            'street_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'fax' => ['nullable', 'string', 'max:20'],
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
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Email harus berupa alamat email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'phone.min' => 'Nomor telepon minimal 9 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, +, -, spasi, dan tanda kurung.',
            'country_code.size' => 'Kode negara harus 2 karakter.',
            'country_code.regex' => 'Kode negara harus berupa 2 huruf kapital (contoh: ID, US, SG).',
        ];
    }
}
