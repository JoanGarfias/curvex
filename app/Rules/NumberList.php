<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validate a string that contains numbers (int or float) separated by commas or newlines.
 * Examples valid:
 *  - "1,2,3"
 *  - "1.5, -3, 4\n5,6.0"
 */
class NumberList implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Normalize Windows CRLF to LF
        $normalized = str_replace(["\r\n", "\r"], "\n", $value);

        // Regex:
        // ^\s*[+-]?\d+(?:\.\d+)?(?:\s*(?:,|\n)\s*[+-]?\d+(?:\.\d+)?)*\s*$
        // - allows integers or floats with optional sign
        // - values separated by comma or newline, optional spaces
        $pattern = '/^\s*[+-]?\d+(?:\.\d+)?(?:\s*(?:,|\n)\s*[+-]?\d+(?:\.\d+)?)*\s*$/';

        return preg_match($pattern, $normalized) === 1;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Los valores deben ser números (enteros o flotantes) separados por comas o saltos de línea.';
    }
}
