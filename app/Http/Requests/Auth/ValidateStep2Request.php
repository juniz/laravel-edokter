<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ValidateStep2Request extends FormRequest
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
            'organization' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'min:9', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'street_1' => ['required', 'string', 'max:255'],
            'street_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'postal_code' => ['required', 'string', 'max:20'],
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
            'organization.required' => 'Nama organisasi/perusahaan wajib diisi.',
            'organization.string' => 'Nama organisasi/perusahaan harus berupa teks.',
            'organization.max' => 'Nama organisasi/perusahaan maksimal 255 karakter.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.min' => 'Nomor telepon minimal 9 karakter.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, +, -, spasi, dan tanda kurung.',
            'street_1.required' => 'Alamat jalan wajib diisi.',
            'street_1.string' => 'Alamat jalan harus berupa teks.',
            'street_1.max' => 'Alamat jalan maksimal 255 karakter.',
            'street_2.string' => 'Alamat jalan 2 harus berupa teks.',
            'street_2.max' => 'Alamat jalan 2 maksimal 255 karakter.',
            'city.required' => 'Kota wajib diisi.',
            'city.string' => 'Kota harus berupa teks.',
            'city.max' => 'Kota maksimal 255 karakter.',
            'state.string' => 'Provinsi harus berupa teks.',
            'state.max' => 'Provinsi maksimal 255 karakter.',
            'country_code.required' => 'Kode negara wajib diisi.',
            'country_code.size' => 'Kode negara harus 2 karakter.',
            'country_code.regex' => 'Kode negara harus berupa 2 huruf kapital (contoh: ID, US, SG).',
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.string' => 'Kode pos harus berupa teks.',
            'postal_code.max' => 'Kode pos maksimal 20 karakter.',
            'fax.string' => 'Fax harus berupa teks.',
            'fax.max' => 'Fax maksimal 20 karakter.',
        ];
    }
}
