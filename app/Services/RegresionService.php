<?php

namespace App\Services;

use App\Support\Math\RegresionModeloLineal;
use App\Support\Math\RegresionModeloExponencial;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
//use RegresionExponencial;


class RegresionBetterResponse {
    public string $name = "";
    public float $R2 = 0.0;

    /** @var object|null */
    public $model = null;
}

class RegresionService
{


    public function getBetterModel(
        RegresionModeloLineal $lineal = null,
        RegresionExponencial $exponential = null,
    ): RegresionBetterResponse {
        $response = new RegresionBetterResponse();
    

        $options = array_filter([$lineal, $exponential]);
        if(empty($options)){
            Log::warning("No se recibieron modelos de regresión");
            return $response;
        }

        $bestModel = null;
        $bestR2 = -INF;

        foreach($options as $model){
            $r2 = $model->getR2();
            Log::info("Modelo". (new ReflectionClass($model))->getShortName() . "R**2 = {$r2}");
            if($r2 > $bestR2){
                $bestR2 = $r2;
                $bestModel = $model;
            }
        }

        if($bestModel !== null){
            $response->R2 = $bestR2;
            $response->name = new ReflectionClass($bestModel)->getShortName();
            $response->model = $model;
        }

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
        };
    }
}