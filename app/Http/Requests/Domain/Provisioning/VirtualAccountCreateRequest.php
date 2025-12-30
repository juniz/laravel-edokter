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
            'create_website' => ['nullable', 'string', 'in:0,1'],
            'username' => ['required', 'string', 'max:16', 'regex:/^[a-z0-9_]+$/'],
            'password' => ['required', 'string', 'min:8'],
            'email' => ['required', 'email', 'max:255'],
            'expire_type' => ['nullable', 'string', 'in:perpetual,custom'],
            'expire_date' => ['nullable', 'date'],
            'package_id' => ['nullable', 'string'],
            'storage_disk' => ['nullable', 'string', 'max:255'],
            'disk_space_quota' => ['nullable', 'integer', 'min:0'],
            'monthly_bandwidth_limit' => ['nullable', 'integer', 'min:0'],
            'max_site_limit' => ['nullable', 'integer', 'min:0'],
            'max_database' => ['nullable', 'integer', 'min:0'],
            'php_start_children' => ['nullable', 'integer', 'min:1'],
            'php_max_children' => ['nullable', 'integer', 'min:1'],
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
            'expire_date.date' => 'Format tanggal expire tidak valid.',
            'package_id.integer' => 'Package ID harus berupa angka.',
            'package_id.min' => 'Package ID minimal 1.',
            'disk_space_quota.integer' => 'Disk space quota harus berupa angka.',
            'disk_space_quota.min' => 'Disk space quota minimal 0.',
            'monthly_bandwidth_limit.integer' => 'Monthly bandwidth limit harus berupa angka.',
            'monthly_bandwidth_limit.min' => 'Monthly bandwidth limit minimal 0.',
            'max_site_limit.integer' => 'Max site limit harus berupa angka.',
            'max_site_limit.min' => 'Max site limit minimal 0.',
            'max_database.integer' => 'Max database harus berupa angka.',
            'max_database.min' => 'Max database minimal 0.',
            'php_start_children.integer' => 'PHP start children harus berupa angka.',
            'php_start_children.min' => 'PHP start children minimal 1.',
            'php_max_children.integer' => 'PHP max children harus berupa angka.',
            'php_max_children.min' => 'PHP max children minimal 1.',
            'automatic_dns.boolean' => 'Automatic DNS harus berupa boolean.',
        ];
    }
}
