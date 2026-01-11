<?php

namespace App\Support\Math;

use App\Services\RegresionData;
use App\ValueObjects\VariableData;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class RegresionSolver {

    protected float $SSE = 0.0;
    protected float $SST = 0.0;
    protected float $y_avg = 0.0;
    protected int $n = 0;

    /** @var int[] */
    protected array $solutions = [];

    //data incluye la cantidad de variables, es decir puede ser tanto solo X, como U,V o como U,V,Z
    
    /** @var VariableData[] */
    protected array $data = [];

    //valor de las y
    /** @var float[] */
    protected array $dependent_data = [];

    public function __construct(array $data, array $dependent_data) {
        // Validar que data contiene objetos VariableData
        if (empty($data)) {
            throw new Exception("Debe proporcionar al menos una variable independiente");
        }

        $countDataPerVariable = $data[0]->countPoints();
        $countDataIndependentVariable = count($dependent_data);

        Log::info("Validando datos de regresión");
        Log::debug("Cantidad de datos por variable independiente: {$countDataPerVariable}");
        Log::debug("Cantidad de datos de variable dependiente: {$countDataIndependentVariable}");

        foreach($data as $idx => $variable_data){
            if(!($variable_data instanceof VariableData)){
                throw new Exception("La variable independiente #{$idx} debe ser una instancia de VariableData");
            }
            $dataQuantityPerVariable = $variable_data->countPoints();
            if($dataQuantityPerVariable != $countDataPerVariable){
                throw new Exception("La cantidad de datos de cada variable independiente debe ser la misma, revise sus datos");
            }
            if($dataQuantityPerVariable != $countDataIndependentVariable){
                throw new Exception("La variable independiente #{$idx} tiene {$dataQuantityPerVariable} datos, pero la variable dependiente tiene {$countDataIndependentVariable}");
            }
        }
        
        $this->data = $data;
        $this->dependent_data = $dependent_data;
        $this->n = $countDataPerVariable;
        
        Log::info("Datos validados correctamente");

    }

    abstract public function calculateYModel(array $solutions, array $ind_term): float;

    protected function countVariables(): int  {
        return count($this->data);
    }

    protected function getM(): int {
        return $this->data[0]->countPoints();
    }

    /**Función que recibe una fila de la combinación de variables
     * como: u, v, z, etc esto para generar multiplicaciones como
     * u*v, v*z, u*z sin duplicados, esto para generar posteriormente
     * la matriz para encontrar las soluciones.
     */
    protected function calculateProductVariables(array $mixed_variables) : array {
        $i = 0;
        $j = 0;
        /**@var float[] */
        $product_variables = [];
        $n = count($mixed_variables);

        Log::info("Calculando el producto de las variables independientes... ");
        Log::info("Se han detectado $n variables y los datos a multiplicar son: " . implode(",", $mixed_variables));

        for($i = 0; $i < $n; $i++){
            for($j = $i; $j < $n; $j++){
                if($i == $j) continue;
                $product_variables[] = $mixed_variables[$i] * $mixed_variables[$j];
            }
        }

        Log::debug("Producto de variables independientes: ". implode(', ', $product_variables));

        return $product_variables;
    }

    protected function calculateSSE(): float {
        $sse = 0.0;
        $row_variable_value = [];

        Log::info("Iniciando cálculo de SSE (Sum of Squared Errors)");

        for($i = 0; $i < $this->getM(); $i++){
            //por cada fila recorrida, obtenemos el valor de cada variable
            $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->data
                                );

            $y_pri = $this->calculateYModel($this->solutions, $row_variable_value);
            $y_actual = $this->dependent_data[$i];
            $error = $y_actual - $y_pri;
            $squared_error = pow($error, 2);
            $sse += $squared_error;

            Log::debug("Fila {$i}: y_actual={$y_actual}, y_predicho={$y_pri}, error={$error}, error**2={$squared_error}");

            //limpieza del array de fila de la matriz de datos de las variables independientes
            $row_variable_value = [];
        }

        Log::info("SSE calculado: {$sse}");
        return $sse;
    }

    protected function calculateSST(): float {
        Log::info("Iniciando cálculo de SST (Total Sum of Squares)");
        
        $sum_y = array_reduce($this->dependent_data, fn(float $s, float $y) => $s + $y, 0.0);
        $this->y_avg = $sum_y / count($this->dependent_data);
        
        Log::debug("Suma de Y: {$sum_y}, Promedio de Y: {$this->y_avg}");
        
        $sst = 0.0;
        foreach($this->dependent_data as $y) {
            $deviation = $y - $this->y_avg;
            $squared_deviation = pow($deviation, 2);
            $sst += $squared_deviation;
            Log::debug("Y: {$y}, Desviación: {$deviation}, Desviación²: {$squared_deviation}");
        }
        
        Log::info("SST calculado: {$sst}");
        return $sst;
    }

    public function calculateR2(): float {
        /*Paso 1: Calcular m, la cantidad de datos */
        $m = (float) $this->getM();
        Log::info("Iniciando cálculo de R**2 para regresión lineal");
        Log::info("Cantidad de datos (m): {$m}");
        Log::info("Cantidad de variables independientes: " . $this->countVariables());

        /*Paso 2: Calcular las sumatorias (SSE, SSR, SST) */

        /** @var float[] */
        $sum_value_variables = [];
        
        /** @var float[] */
        $sum_value_variables_squared = [];
        $sum_y = array_reduce($this->dependent_data, fn(float $s, float $y) => $s + $y, 0.0);
        
        Log::debug("Suma de valores Y: {$sum_y}");

        /** @var float[] */
        $product_variables = [];
        /** @var float[] */
        $sum_product_variables = [];

        /* Calcular multiplicaciones de variables independientes con la variable dependiente
        ejemplo: u*y, v*y, z*y
        */
        /** @var float[] */
        $product_dep_ind_variables = [];

        /** @var float[] */
        $sum_product_dep_ind_variables = [];


        for($i=0; $i < $this->countVariables(); $i++){ $sum_product_variables[] = 0.0; $sum_product_dep_ind_variables[] = 0.0;}

        foreach($this->data as $idx => $variable_data){
            $sum_val = array_reduce($variable_data->points, fn(float $s, float $value) => $s + $value, 0.0);
            $sum_sq = array_reduce($variable_data->points, fn(float $s, float $value) => $s + pow($value, 2), 0.0);
            
            Log::debug("Variable independiente #{$idx}: suma={$sum_val}, suma_cuadrados={$sum_sq}");
        }

        try{
            $this->y_avg = $sum_y / $m;
            Log::info("Promedio de Y: {$this->y_avg}");

            //Encontrar los productos entre los datos de cada variable
            for($i = 0; $i < $m; $i++){
                //Sacamos cada elemento de la variable y lo agregamos a un array para poder hacer el calculo de los productos
                //u*v, v*z, z*u etc
                $product_variables = $this
                                    ->calculateProductVariables(
                                        array_map(fn($value) => $value->getVariableAt($i), $this->data),
                                    );    

                Log::info("Calculando la suma de la multiplicación de las variables");

                $sum_product_variables = array_map(
                                            function($sum_array_value, $index) use ($product_variables, $sum_product_variables) {
                                                $sum_product_variables[$index] += $product_variables[$index];
                                            },
                                            $sum_product_variables, array_keys($sum_product_variables)
                                        );

                            $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->data
                                );

                Log::info("Calculando la suma de la multiplicación de cada variable independiente con los datos de la variable dependiente");

                // Acumular el producto de cada variable independiente con la variable dependiente
                foreach($this->data as $index => $variable_data) {
                    $independ_value = $variable_data->getVariableAt($i);
                    $dependent_value = $this->dependent_data[$i];
                    $product = $independ_value * $dependent_value;
                    
                    $sum_product_dep_ind_variables[$index] += $product;
                    
                    Log::debug("Var ind #{$index} fila {$i}: valor={$independ_value}, Y={$dependent_value}, producto={$product}, suma_acumulada={$sum_product_dep_ind_variables[$index]}");
                }
                
                Log::info("Acumulado multiplicación dep-ind en fila {$i}: " . implode(",", $sum_product_dep_ind_variables));
            }

            Log::info("La suma de la multiplicación de las variables dependientes con las independientes es: " . implode(",", $sum_product_dep_ind_variables));
            /**Aquí se tiene que implementar el armado de la matriz, el cálculo de la matriz inversa y su posterior multiplicación de matrices. */


            //Calculamos SSE y SST
            $this->SSE = $this->calculateSSE();
            $this->SST = $this->calculateSST();

            Log::info("Estadísticas finales:");
            Log::info("SSE (Sum of Squared Errors): {$this->SSE}");
            Log::info("SST (Total Sum of Squares): {$this->SST}");

            if($this->SST == 0){
                $R2 = 1;
                Log::warning("SST es 0, R**2 asignado a 1");
            }else{
                $R2 = 1 - ($this->SSE / $this->SST);
                Log::info("R**2 = 1 - (SSE/SST) = 1 - ({$this->SSE}/{$this->SST}) = {$R2}");
            }
            
            Log::info("Cálculo de R**2 completado. Resultado: {$R2}");
            return $R2;
        } catch (Exception $e) {
            Log::error("Error al calcular los coeficientes de regresión: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
}