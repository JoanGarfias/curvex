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

        // Si contiene comas, rechazar inmediatamente
        if (strpos($trimmed, ',') !== false) {
            return false;
        }

        // Regex mejorada:
        // ^\s*                     - inicio de línea, espacios opcionales
        // [+-]?\d+(?:\.\d+)?      - número con signo opcional y parte decimal opcional
        // (?:\s+[+-]?\d+(?:\.\d+)?)*  - más números separados por espacios
        // \s*$                     - espacios opcionales, fin de línea
        $pattern = '/^\s*[+-]?\d+(?:\.\d+)?(?:\s+[+-]?\d+(?:\.\d+)?)*\s*$/';

        // Verificar el patrón general
        if (preg_match($pattern, $trimmed) !== 1) {
            return false;
        }

        // Verificar que haya al menos dos números
        $numbers = preg_split('/\s+/', $trimmed);
        return count($numbers) >= 2;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Los valores deben ser números (enteros o flotantes) separados por espacios. Formato correcto: "1.2 30.4 50". Formato incorrecto: "1,2,30.4,50" o "1.2, 30.4, 50".';
    }
}
