<?php

namespace App\Services;

use App\Support\Math\CrammerSolver;
use App\ValueObjects\Matrix;
use Illuminate\Support\Facades\Log;
use App\ValueObjects\Point;
use App\ValueObjects\Solution2VSystem;
use Exception;


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
    public array $solutions = null;
}

class RegresionLineal extends RegresionData implements RegresionOperations {
    //data incluye la cantidad de variables, es decir puede ser tanto solo X, como U,V o como U,V,Z
    
    /** @var VariableData[] */
    private array $data = [];

    //valor de las y
    /** @var VariableData */
    private array $dependent_data = [];

    public function __construct(array $data, array $dependent_data, string $method = "lineal") {
        $countDataPerVariable = count($data[0]->countPoints());
        $countDataIndependentVariable = count($dependent_data);

        foreach($data as $variable_data){
            $dataQuantityPerVariable = count($variable_data);
            if(!is_array($variable_data)){
                throw new Exception("Debe enviar información (array) de la variable independiente");
            }
            if($dataQuantityPerVariable != $countDataPerVariable){
                throw new Exception("La cantidad de datos de cada variable independiente debe ser la misma, revise sus datos");
            }
            if($dataQuantityPerVariable != $countDataIndependentVariable){
                throw new Exception("La cantidad de datos de la variable independiente tiene que ser la misma que la de las variables independientes");
            }
        }
        $this->data = $data;
        $this->dependent_data = $dependent_data;
        $this->method = $method;
        $this->n = $this->data[0]->countPoints();
    }

    private function countVariables(): int  {
        return count($this->data);
    }

    private function getM(): int {
        return $this->data[0]->countPoints;
    }

    //remaning_solutions excluye a0, ya tiene el array_slice para solo hacer un loop de a_i * variable_i
    private function calculateYModel(array $solutions, array $ind_term): float {
        $y_model = $this->solutions[0];

        for($i=1; $i < $this->countVariables(); $i++){
            $y_model += $solutions[$i] + $ind_term[$i];
        }

        return $y_model;
    }


    public function calculateSSE(): float {
        $sse = 0.0;
        $row_variable_value = [];

        for($i = 0; $i < $this->getM(); $i++){
            //por cada fila recorrida, obtenemos el valor de cada variable
            $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->data
                                );

            $y_pri = $this->calculateYModel($this->solutions, $row_variable_value);
            $sse += $this->dependent_data[$i] - $y_pri;

            //limpieza del array de fila de la matriz de datos de las variables independientes
            $row_variable_value = [];
        }

        return $sse;
    }

    public function calculateSST(): float {
        return array_reduce($this->dependent_data->points, fn($y) => $y, 0.0) / $this->y_avg;
    }

    public function calculateR2(): float {
        /*Paso 1: Calcular m, la cantidad de datos */
        $m = (float) $this->getM();

        /*Paso 2: Calcular las sumatorias (SSE, SSR, SST) */

        /** @var float[] */
        $sum_value_variables = [];
        /** @var float[] */
        $sum_value_variables_squared = [];
        $sum_y = array_reduce($this->dependent_data->points, fn(float $s, float $y) => $s + $y);

        foreach($this->data as $variable_data){
            $sum_value_variables = array_reduce($variable_data->points, fn(float $s, float $value) => $s + $value, 0.0);
            $sum_value_variables_squared = array_reduce($variable_data->points, fn(float $s, float $value) => $s + pow($value, 2), 0.0);
        }


        Log::info("Sumatoria de valores de las variables ", $sum_value_variables, " sumatoria de valores de las variables al cuadrado", $sum_value_variables_squared);
        /*
        $sum_xy = array_reduce($this->points, fn(float $s, Point $p) => $s + ($p->x * $p->y), 0.0);
        Log::info("Sumatorias calculadas: sum_y = $sum_y, sum_x = $sum_x, sum_x2 = $sum_x2, sum_xy = $sum_xy");
        */

        try{
            $this->y_avg = $sum_y / $m;

            /*
            $matriz = new Matrix(
                [
                    [$m, $sum_x, $sum_y],
                    [$sum_x, $sum_x2, $sum_xy],
                ], 2, 3);

            */
            //Implementar lógica para obtener las soluciones
            //$this->a = CrammerSolver::solveCrammerMatrix2X2($matriz);

            Log::info("Coeficientes calculados: a0 = {$this->a->a0}, a1 = {$this->a->a1}");

            $this->SSE = $this->calculateSSE();
            $this->SST = $this->calculateSST();

            Log::info("SSE = $this->SSE, SST = $this->SST");

            if($this->SST == 0){
                $R2 = 1;
            }else{
                $R2 = 1 - ($this->SSE / $this->SST);
            }
            
            return $R2;
        } catch (Exception $e) {
            Log::error("Error al calcular los coeficientes de regresión: " . $e->getMessage());
            throw $e;
        }
    }
}


class RegresionExponencial implements RegresionOperations {
    /** @var Point[] */
    private array $points = [];
    private float $y_avg = 0.0;
    private float $SSE = 0.0;
    private float $SST = 0.0;
    private string $method = "lineal";
    public function calculateSSE(){}
    public function calculateSST(){}
    public function calculateR2(){}
}

class RegresionService
{
    public static function createRegresion(array $points, int $variables = 2, string $method = "lineal"){
        return
            match($variables){
                1 => new RegresionLineal($points, $points, $method),
                3 => new RegresionExponencial($points, $method),
                default => throw new Exception("No existe una solución de regresión disponible para la información proporcionada.")
            }
        ;
    }
}