<?php

namespace App\Http\Requests\Domain\Provisioning;

use Illuminate\Foundation\Http\FormRequest;

class PanelAccountCreateRequest extends FormRequest
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
            'domain' => ['required', 'string', 'max:255', 'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i'],
            'username' => ['nullable', 'string', 'max:16', 'regex:/^[a-z0-9_]+$/'],
            'password' => ['nullable', 'string', 'min:8'],
            'path' => ['nullable', 'string', 'max:255'],
            'php_version' => ['nullable', 'string', 'in:00,52,53,54,55,56,70,71,72,73,74,80,81,82,83,84'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'type_id' => ['nullable', 'integer', 'min:0'],
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
            'domain.required' => 'Domain wajib diisi.',
            'domain.regex' => 'Format domain tidak valid.',
            'username.regex' => 'Username hanya boleh mengandung huruf kecil, angka, dan underscore.',
            'username.max' => 'Username maksimal 16 karakter.',
            'password.min' => 'Password minimal 8 karakter.',
            'php_version.in' => 'Versi PHP tidak valid.',
            'port.integer' => 'Port harus berupa angka.',
            'port.min' => 'Port minimal 1.',
            'port.max' => 'Port maksimal 65535.',
        ];
    }
}
