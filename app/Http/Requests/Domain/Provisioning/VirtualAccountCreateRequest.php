<?php

namespace App\Http\Requests\Domain\Provisioning;

use Illuminate\Foundation\Http\FormRequest;

class VirtualAccountCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('admin-servers-create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'server_id' => ['required', 'exists:servers,id'],
            'username' => ['required', 'string', 'max:16', 'regex:/^[a-z0-9_]+$/'],
            'password' => ['required', 'string', 'min:8'],
            'email' => ['required', 'email', 'max:255'],
            'expire_type' => ['nullable', 'string', 'in:perpetual,custom'],
            'expire_date' => ['nullable', 'string'],
            'package_name' => ['nullable', 'string', 'max:255'],
            'mountpoint' => ['nullable', 'string', 'max:255'],
            'remark' => ['nullable', 'string', 'max:500'],
            'automatic_dns' => ['nullable', 'string', 'in:0,1'],
            'domain' => ['nullable', 'string', 'max:255'],
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
            'server_id.required' => 'Server wajib dipilih.',
            'server_id.exists' => 'Server tidak ditemukan.',
            'username.required' => 'Username wajib diisi.',
            'username.regex' => 'Username hanya boleh mengandung huruf kecil, angka, dan underscore.',
            'username.max' => 'Username maksimal 16 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'package_name.max' => 'Package name maksimal 255 karakter.',
        ];
    }
}
