<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class SanitizeInputMiddleware extends TransformsRequest
{
    /**
     * The attributes that should not be sanitized.
     *
     * @var array<int, string>
     */
    protected $except = [
        'password',
        'password_confirmation',
        'token',
        'api_token',
        '_token',
        'credential',
        'isi',
    ];

    /**
     * Clean the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value)
    {
        // If it is a Livewire request, we must NOT sanitize fingerprint or serverMemo,
        // as doing so will corrupt the component signature and throw CorruptComponentPayloadException.
        // We only sanitize input fields in the "updates" array.
        if (request()->hasHeader('X-Livewire')) {
            if (strpos($key, 'updates.') !== 0) {
                return $value;
            }

            // Check if this update targets an excepted field (e.g. password)
            // Case 1: syncInput -> updates.0.payload.value
            if (preg_match('/^updates\.(\d+)\.payload\.value$/', $key, $matches)) {
                $index = $matches[1];
                $name = request()->input("updates.{$index}.payload.name");
                if (in_array($name, $this->except, true)) {
                    return $value;
                }
            }

            // Case 2: callMethod -> updates.0.payload.params.0
            if (preg_match('/^updates\.(\d+)\.payload\.params\.\d+$/', $key, $matches)) {
                $index = $matches[1];
                $method = request()->input("updates.{$index}.payload.method");
                if ($method) {
                    foreach ($this->except as $exceptKey) {
                        if (stripos($method, $exceptKey) !== false) {
                            return $value;
                        }
                    }
                }
            }
        } else {
            // Standard (non-Livewire) requests: skip keys in the except list
            foreach ($this->except as $exceptKey) {
                if ($key === $exceptKey || (strpos($key, '.') !== false && substr($key, -strlen('.' . $exceptKey)) === '.' . $exceptKey)) {
                    return $value;
                }
            }
        }

        // Only sanitize string inputs
        if (!is_string($value)) {
            return $value;
        }

        // 1. Strip HTML tags (using regex to preserve mathematical inequalities like < or >)
        $value = preg_replace('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/', '', $value);

        // 2. Replace single quote (') with backtick (`)
        $value = str_replace("'", "`", $value);

        // 3. Remove double quotes (") and backslashes (\)
        $value = str_replace(['"', '\\'], '', $value);

        return $value;
    }
}
