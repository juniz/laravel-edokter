<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'filled'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:255',
                'filled',
                // Check unique in both users and pending_registrations
                function ($attribute, $value, $fail) {
                    if (User::where('email', $value)->exists()) {
                        $fail('Email sudah terdaftar.');
                    }
                    // Allow same email in pending_registrations (will be replaced)
                },
            ],
            'password' => ['required', 'string', 'filled', 'confirmed', Rules\Password::defaults()],
            // RDASH customer fields
            'organization' => ['required', 'string', 'max:255', 'filled'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'filled', 'regex:/^[0-9+\-\s()]+$/'],
            'street_1' => ['required', 'string', 'max:255', 'filled'],
            'street_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255', 'filled'],
            'state' => ['required', 'string', 'max:255', 'filled'],
            'country_code' => ['required', 'string', 'size:2', 'filled', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['required', 'string', 'max:20', 'filled'],
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
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sesuai.',
            'organization.required' => 'Nama organisasi/perusahaan wajib diisi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.min' => 'Nomor telepon minimal 9 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, +, -, spasi, dan tanda kurung.',
            'street_1.required' => 'Alamat jalan wajib diisi.',
            'city.required' => 'Kota wajib diisi.',
            'state.required' => 'Provinsi wajib diisi.',
            'country_code.required' => 'Kode negara wajib diisi.',
            'country_code.size' => 'Kode negara harus 2 karakter.',
            'country_code.regex' => 'Kode negara harus berupa 2 huruf kapital (contoh: ID, US, SG).',
            'postal_code.required' => 'Kode pos wajib diisi.',
        ];
    }
}
