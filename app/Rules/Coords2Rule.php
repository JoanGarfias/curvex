<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Coords2Rule implements Rule
{
    public function passes($attribute, $value)
    {
        // Verificar que el valor no esté vacío
        if (empty($value)) {
            return false;
        }

        // Dividir el valor en líneas
        $lines = preg_split('/\r\n|\r|\n/', trim($value));

        // Debe tener al menos una línea
        if (count($lines) === 0) {
            return false;
        }

        // Validar cada línea
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Saltar líneas vacías
            if (empty($line)) {
                continue;
            }

            // Validar formato: número,número
            // Acepta números enteros y decimales (con punto o coma como separador decimal)
            if (!preg_match('/^-?\d+([.,]\d+)?\s*,\s*-?\d+([.,]\d+)?$/', $line)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'El formato debe ser coordenadas separadas por comas en cada línea (ej: 2,3).';
    }
}