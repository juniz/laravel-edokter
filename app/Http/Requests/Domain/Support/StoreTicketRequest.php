<?php

namespace App\Http\Requests\Domain\Support;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'message' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Subjek ticket harus diisi.',
            'priority.required' => 'Prioritas harus dipilih.',
            'message.required' => 'Pesan harus diisi.',
            'message.min' => 'Pesan minimal 10 karakter.',
        ];
    }
}
