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
        // Skip keys in the except list (including nested attributes e.g. "user.password")
        foreach ($this->except as $exceptKey) {
            if ($key === $exceptKey || (strpos($key, '.') !== false && substr($key, -strlen('.' . $exceptKey)) === '.' . $exceptKey)) {
                return $value;
            }
        }

        // Only sanitize string inputs
        if (!is_string($value)) {
            return $value;
        }

        // 1. Strip HTML tags
        $value = strip_tags($value);

        // 2. Replace single quote (') with backtick (`)
        $value = str_replace("'", "`", $value);

        // 3. Remove double quotes (") and backslashes (\)
        $value = str_replace(['"', '\\'], '', $value);

        return $value;
    }
}
