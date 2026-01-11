<?php

namespace App\Services;

use App\Support\Math\RegresionLineal;
use Illuminate\Support\Facades\Log;

class RegresionService
{
    public static function createRegresion(
        array $independent_variables, 
        array $dependent_values, 
        string $method = "lineal"
    ){
        $variables_count = count($independent_variables);
        
        Log::info("Creando servicio de regresión con {$variables_count} variable(s) independiente(s)");
        
        return
            match($variables_count){
                1 => new RegresionLineal($independent_variables, $dependent_values, $method),
                2 => new RegresionLineal($independent_variables, $dependent_values, $method),
                3 => new RegresionLineal($independent_variables, $dependent_values, $method),
                default => new RegresionLineal($independent_variables, $dependent_values, $method)
            }
        ;
    }
}