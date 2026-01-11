<?php

namespace App\Services;

use App\Support\Math\RegresionModeloLineal;
use Illuminate\Support\Facades\Log;
use RegresionExponencial;

class RegresionBetterResponse {
    public string $name = "";
    public float $R2 = 0.0; 
}

class RegresionService
{


    public function getBetterModel(
        RegresionModeloLineal $lineal = null,
        RegresionExponencial $exponencial = null,
    ): RegresionBetterResponse {
        $response = new RegresionBetterResponse();
        


        return $response;
    }


    public static function createRegresion(
        array $independent_variables, 
        array $dependent_values, 
        string $method = "lineal"
    ){
        $variables_count = count($independent_variables);
        
        Log::info("Creando servicio de regresión con {$variables_count} variable(s) independiente(s)");
        
        return
            match($variables_count){
                1 => new RegresionModeloLineal($independent_variables, $dependent_values, $method),
                2 => new RegresionModeloLineal($independent_variables, $dependent_values, $method),
                3 => new RegresionModeloLineal($independent_variables, $dependent_values, $method),
                default => new RegresionModeloLineal($independent_variables, $dependent_values, $method)
            }
        ;
    }
}