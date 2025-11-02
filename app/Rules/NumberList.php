<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Validate a string that contains numbers (int or float) separated by spaces.
 * Examples valid:
 *  - "1 2 3"
 *  - "1.5 -3 4 5 6.0"
 *  - "1.5  -3   4" (multiple spaces allowed)
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

        // Trim whitespace
        $trimmed = trim($value);

        if (empty($trimmed)) {
            return false;
        }

        // Regex:
        // ^\s*[+-]?\d+(?:\.\d+)?(?:\s+[+-]?\d+(?:\.\d+)?)*\s*$
        // - allows integers or floats with optional sign
        // - values separated by one or more spaces
        $pattern = '/^\s*[+-]?\d+(?:\.\d+)?(?:\s+[+-]?\d+(?:\.\d+)?)*\s*$/';

        return preg_match($pattern, $trimmed) === 1;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Los valores deben ser números (enteros o flotantes) separados por espacios.';
    }
}
