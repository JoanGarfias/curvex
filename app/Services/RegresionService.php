<?php

namespace App\Services;

use App\Support\Math\Regresion\RegresionLinealModel;
use App\Support\Math\Regresion\RegresionExponentialModel;
use App\Support\Math\Regresion\RegresionPotentialModel;
use App\Support\Math\Regresion\RegresionCuadraticModel;
use Illuminate\Support\Facades\Log;

class RegresionBetterResponse {
    public string $name = "";
    public float $R2 = 0.0;

    /** @var object|null */
    public $model = null;
}

class RegresionService
{


    public function getBetterModel(
        RegresionLinealModel $lineal = null,
        RegresionExponentialModel $exponential = null,
        RegresionPotentialModel $potential = null,
        RegresionCuadraticModel $cuadratic = null
    ): RegresionBetterResponse {
        $response = new RegresionBetterResponse();
    

        $options = array_filter([$lineal, $exponential, $potential, $cuadratic]);
        if(empty($options)){
            Log::warning("No se recibieron modelos de regresión");
            return $response;
        }

        $bestModel = null;
        $bestR2 = -INF;

        foreach($options as $model){
            $r2 = $model->getR2();
            Log::info("Modelo". $model->getName() . "R**2 = {$r2}");
            if($r2 > $bestR2){
                $bestR2 = $r2;
                $bestModel = $model;
            }
        }

        if($bestModel !== null){
            $response->R2 = $bestR2;
            $response->name = $model->getName();
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
            match($method){
                "lineal" => new RegresionLinealModel($independent_variables, $dependent_values, $method),
                "exponential" => new RegresionExponentialModel($independent_variables, $dependent_values, $method),
                "potential" => new RegresionPotentialModel($independent_variables, $dependent_values, $method),
                "cuadratic" => new RegresionCuadraticModel($independent_variables, $dependent_values, $method),
                default => new RegresionLinealModel($independent_variables, $dependent_values, $method)
            }
        ;
    }
}