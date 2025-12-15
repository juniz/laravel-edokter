<?php

namespace App\Http\Requests\Domain\Provisioning;

use Illuminate\Foundation\Http\FormRequest;

class ServerStoreRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:cpanel,directadmin,proxmox,aapanel'],
            'endpoint' => ['required', 'url', 'max:255'],
            'auth_secret_ref' => ['required', 'string'],
            'status' => ['required', 'in:active,maintenance,disabled'],
            'meta' => ['nullable', 'array'],
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
            'name.required' => 'Nama server wajib diisi.',
            'type.required' => 'Tipe server wajib dipilih.',
            'type.in' => 'Tipe server tidak valid.',
            'endpoint.required' => 'Endpoint URL wajib diisi.',
            'endpoint.url' => 'Endpoint harus berupa URL yang valid.',
            'auth_secret_ref.required' => 'Auth secret reference wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
        ];
    }
}
