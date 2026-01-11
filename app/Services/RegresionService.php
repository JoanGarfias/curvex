<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\ValueObjects\Point;
use App\ValueObjects\Solution2VSystem;
use Exception;
use RegresionLineal;

interface RegresionCalculator {
    public function calculateCoefficients() : Solution2VSystem;
    public function predict(float $x): float;
    public function transform(): float;
}

interface RegresionOperations {
    public function calculateSSE();
    public function calculateSST();
    public function calculateR2();
}

class RegresionData {
    public float $SSE = 0.0;
    public float $SST = 0.0;
    public float $y_avg = 0.0;
    public string $method = "lineal";
    public int $n = 0;

    /** @var int[] */
    public array $solutions = [];
}

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