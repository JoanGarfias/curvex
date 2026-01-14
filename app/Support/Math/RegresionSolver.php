<?php

namespace App\Support\Math;

use App\Services\RegresionData;
use App\ValueObjects\VariableData;
use App\ValueObjects\Matrix;
use App\Support\Math\MatrixSolver;
use App\Services\MatrizInversaService;
use Exception;
use Illuminate\Support\Facades\Log;

abstract class RegresionSolver {

    protected float $SSE = 0.0;
    protected float $SST = 0.0;
    protected float $y_avg = 0.0;
    protected float $R2 = 0.0;
    protected int $n = 0;

    /** @var int[] */
    protected array $solutions = [];

    //data incluye la cantidad de variables, es decir puede ser tanto solo X, como U,V o como U,V,Z
    
    /** @var VariableData[] */
    protected array $data = [];
    protected array $datacopy = [];

    //valor de las y
    /** @var float[] */
    protected array $dependent_data = [];
    protected array $dependent_datacopy = [];
    protected array $predictions = [];

    public function __construct(array $data, array $dependent_data, array $solutions = [] ) {
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
        
        // 1. Guardamos la copia de seguridad (referencias originales)
        $this->datacopy = $data; 

        // 2. Para la data de trabajo, CLONAMOS cada objeto explícitamente
        // Esto crea nuevos objetos independientes en memoria para $this->data
        $this->data = array_map(function ($variable) {
            return clone $variable;
        }, $data);


        // 3. Arrays de primitivos (números)
        // PHP copia arrays de números por valor automáticamente, esto SÍ funciona bien directo:
        $this->dependent_data = $dependent_data;
        $this->dependent_datacopy = $dependent_data; // Esto es seguro si son solo floats/ints

        $this->n = $countDataPerVariable;

        $this->solutions = $solutions;
        
        Log::info("Datos validados correctamente");

    }

    abstract public function calculateYModel(array $solutions, array $ind_term): float;

    abstract public function getName(): string;

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

        if($product_variables == null){
            $product_variables = $mixed_variables;
        }

        Log::debug("Producto de variables independientes: ". implode(', ', $product_variables));

        return $product_variables;
    }

    protected function calculateSSE(): float {
        $sse = 0.0;
        $row_variable_value = [];

        for($i = 0; $i < $this->getM(); $i++){
            //por cada fila recorrida, obtenemos el valor de cada variable
            switch($this->getName()){
                case "Potencial":
                    $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->datacopy
                                );
                    $y_actual = $this->dependent_datacopy[$i];
                    break;
                case "Exponencial":
                    $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->datacopy
                                );
                    $y_actual = $this->dependent_datacopy[$i];
                    break;
                default:
                    $row_variable_value = array_map(
                                    fn($variable) => $variable->getVariableAt($i),
                                    $this->data
                                );
                    $y_actual = $this->dependent_data[$i];
                    break;
            }
            
            $y_pri = $this->calculateYModel($this->solutions, $row_variable_value);
            $this->predictions[] = $y_pri;
            
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
        $sst = 0.0;
        
        switch($this->getName()){
                case "Potencial":
                    $sum_y = array_reduce($this->dependent_datacopy, fn(float $s, float $y) => $s + $y, 0.0);
                    $this->y_avg = $sum_y / count($this->dependent_datacopy);

                    Log::debug("Suma de Y: {$sum_y}, Promedio de Y: {$this->y_avg}");
        
                    
                    foreach($this->dependent_datacopy as $y) {
                        $deviation = $y - $this->y_avg;
                        $squared_deviation = pow($deviation, 2);
                        $sst += $squared_deviation;
                        Log::debug("Y: {$y}, Desviación: {$deviation}, Desviación²: {$squared_deviation}");
                    }
                    break;
                case "Exponencial":
                    $sum_y = array_reduce($this->dependent_datacopy, fn(float $s, float $y) => $s + $y, 0.0);
                    $this->y_avg = $sum_y / count($this->dependent_datacopy);

                    Log::debug("Suma de Y: {$sum_y}, Promedio de Y: {$this->y_avg}");
        
                    foreach($this->dependent_datacopy as $y) {
                        $deviation = $y - $this->y_avg;
                        $squared_deviation = pow($deviation, 2);
                        $sst += $squared_deviation;
                        Log::debug("Y: {$y}, Desviación: {$deviation}, Desviación²: {$squared_deviation}");
                    }
                    break;
                default:
                    $sum_y = array_reduce($this->dependent_data, fn(float $s, float $y) => $s + $y, 0.0);
                    $this->y_avg = $sum_y / count($this->dependent_data);
                    

                    Log::debug("Suma de Y: {$sum_y}, Promedio de Y: {$this->y_avg}");
            
                    foreach($this->dependent_data as $y) {
                        $deviation = $y - $this->y_avg;
                        $squared_deviation = pow($deviation, 2);
                        $sst += $squared_deviation;
                        Log::debug("Y: {$y}, Desviación: {$deviation}, Desviación²: {$squared_deviation}");
                    }
                    break;
            }
        
        
        Log::info("SST calculado: {$sst}");
        return $sst;
    }

    public function getR2(): float {
        return $this->R2;
    }

    public function calculateR2(): array {
        $m = (float) $this->getM();
        $num_vars = $this->countVariables();

        Log::info("Iniciando cálculo de R**2. Datos: {$m}, Variables: {$num_vars}");

        // 1. Transformaciones (Logaritmos, etc.)
        $this->transformData();

        // 2. Inicializar Acumuladores
        $sum_y = 0.0;
        $sum_vars = array_fill(0, $num_vars, 0.0); // Suma simple de cada X
        
        // Matriz 2D para guardar Sum(Xi * Xj). 
        // Ej: $sum_products[0][0] es Sum(X1^2), $sum_products[0][1] es Sum(X1*X2)
        $sum_products = []; 
        for($k=0; $k<$num_vars; $k++) {
            for($l=0; $l<$num_vars; $l++) {
                $sum_products[$k][$l] = 0.0;
            }
        }

        $sum_prod_x_y = array_fill(0, $num_vars, 0.0); // Suma de Xi * Y

        // 3. Bucle Principal (Recorremos los datos UNA sola vez)
        for ($i = 0; $i < $m; $i++) {
            
            // Obtener Y actual
            $y = $this->dependent_data[$i]; // Ojo: Usar data transformada si aplica
            
            $sum_y += $y;

            // Obtener valores de X para esta fila (Ej: [ValorX1, ValorX2])
            $x_row_values = [];
            foreach($this->data as $idx => $variable) {
                $val = $variable->getVariableAt($i);
                $x_row_values[$idx] = $val;
                
                // Acumular suma simple y suma con Y
                $sum_vars[$idx] += $val;
                $sum_prod_x_y[$idx] += ($val * $y);
            }

            // Acumular productos cruzados entre variables (Para la matriz X'X)
            for ($k = 0; $k < $num_vars; $k++) {
                for ($l = 0; $l < $num_vars; $l++) {
                    // Sumamos X_k * X_l
                    $sum_products[$k][$l] += $x_row_values[$k] * $x_row_values[$l];
                }
            }
        }

        // 4. Promedio Y
        $this->y_avg = $sum_y / $m;

        // 5. Construcción de Matrices para el Solver
        
        if ($this->getName() == "Cuadrático") {
            // 1. Validación: La regresión cuadrática simple solo acepta 1 variable independiente
            if ($this->countVariables() !== 1) {
                throw new Exception("La regresión cuadrática simple requiere exactamente 1 variable independiente.");
            }

            Log::info("Calculando regresión cuadrática (Parabólica)...");

            // 2. Inicialización de acumuladores
            // Necesitamos sumas hasta x^4 para la Matriz A, y hasta x^2*y para el Vector B
            $sum_x = 0.0;
            $sum_x2 = 0.0;
            $sum_x3 = 0.0;
            $sum_x4 = 0.0;
            
            $sum_y = 0.0;
            $sum_xy = 0.0;
            $sum_x2y = 0.0;

            // Obtenemos la única variable independiente disponible
            $variable_x = $this->data[0]; 

            // 3. Bucle Único: Calculamos TODO en una sola pasada
            for ($i = 0; $i < $m; $i++) {
                $x = $variable_x->getVariableAt($i);
                $y = $this->dependent_data[$i];

                // Pre-cálculo de potencias para eficiencia
                $x2 = $x * $x;
                $x3 = $x2 * $x;
                $x4 = $x3 * $x;

                // Acumulación de X
                $sum_x  += $x;
                $sum_x2 += $x2;
                $sum_x3 += $x3;
                $sum_x4 += $x4;

                // Acumulación de Y e interacciones
                $sum_y   += $y;
                $sum_xy  += ($x * $y);
                $sum_x2y += ($x2 * $y);
            }

            Log::debug("Sumatorias calculadas: X=$sum_x, X2=$sum_x2, X3=$sum_x3, X4=$sum_x4");

            // 4. Construcción manual de la Matriz Normal (3x3)
            // |  N      Sx     Sx2  |
            // |  Sx     Sx2    Sx3  |
            // |  Sx2    Sx3    Sx4  |
            
            $matind = [
                [$m,      $sum_x,  $sum_x2],
                [$sum_x,  $sum_x2, $sum_x3],
                [$sum_x2, $sum_x3, $sum_x4]
            ];

            // 5. Construcción del Vector B (3x1)
            // | Sy   |
            // | Sxy  |
            // | Sx2y |
            
            $matdep = [
                $sum_y,
                $sum_xy,
                $sum_x2y
            ];

            // 6. Resolución del Sistema
            // Inversa de A
            $MatrizInversaService = new MatrizInversaService();
            // Asegúrate de que tu servicio devuelva array puro
            $matindinv = $MatrizInversaService->calcular($matind); 

            // Crear objetos Matrix
            $mata = new Matrix($matindinv, 3, 3);
            
            // Convertimos el vector B plano a formato columna para la multiplicación
            // [[Sy], [Sxy], [Sx2y]]
            $matdepColumn = array_map(fn($val) => [$val], $matdep);
            $matb = new Matrix($matdepColumn, 3, 1);

            // Multiplicar (A^-1 * B)
            $solver = new MatrixSolver();
            $matres = $solver->multiply($mata, $matb);

            // Guardar soluciones (aplanando el resultado)
            $this->solutions = [];
            foreach($matres->data as $row) {
                $this->solutions[] = $row[0];
            }
        }
        else {
            // --- Lógica General Multivariable (Lineal, Potencial, Exponencial) ---
            
            $matrix_size = $num_vars + 1; // +1 por el intercepto
            $matind = [];

            // Llenado de la Matriz A (Lado Izquierdo)
            for ($row = 0; $row < $matrix_size; $row++) {
                for ($col = 0; $col < $matrix_size; $col++) {
                    
                    if ($row == 0 && $col == 0) {
                        $matind[$row][$col] = $m; // N
                    } 
                    elseif ($row == 0) {
                        $matind[$row][$col] = $sum_vars[$col - 1]; // Sum X
                    } 
                    elseif ($col == 0) {
                        $matind[$row][$col] = $sum_vars[$row - 1]; // Sum X (Simetría)
                    } 
                    else {
                        // Aquí usamos la matriz 2D que calculamos arriba
                        // Restamos 1 a los índices por el offset del intercepto
                        $matind[$row][$col] = $sum_products[$row - 1][$col - 1];
                    }
                }
            }

            Log::info($matind);

            // Llenado del Vector B (Lado Derecho)
            $matdep = [];
            $matdep[] = $sum_y; // Primer elemento: Sum Y
            foreach($sum_prod_x_y as $val) {
                $matdep[] = $val; // Siguientes: Sum X*Y
            }

            Log::info($matdep);

            // Resolver Sistema
            $MatrizInversaService = new MatrizInversaService();
            // Asegúrate que tu servicio de inversa devuelva array, no Response
            $matindinv = $MatrizInversaService->calcular($matind); 

            $mata = new Matrix($matindinv, $matrix_size, $matrix_size);
            $matb = new Matrix(array_map(fn($v) => [$v], $matdep), $matrix_size, 1); // Convertir vector a columna

            $solver = new MatrixSolver();
            $matres = $solver->multiply($mata, $matb);
            
            // Aplanar resultado
            $this->solutions = [];
            foreach($matres->data as $row) {
                $this->solutions[] = $row[0];
            }
        }

        // 6. Post-Procesamiento de Soluciones
        switch($this->getName()){
            case "Potencial":
                $this->solutions[0] = 10 ** ($this->solutions[0]); // A = 10^a0
                break;
            case "Exponencial":
                $this->solutions[0] = exp($this->solutions[0]); // A = e^a0
                break;
        }

        Log::info("Soluciones: " . implode(", ", $this->solutions));

        // 7. Estadísticas finales (Esto estaba bien)
        $this->SSE = $this->calculateSSE();
        $this->SST = $this->calculateSST();

        if($this->SST == 0){
             $this->R2 = 1.0;
        } else {
             $this->R2 = 1.0 - ($this->SSE / $this->SST);
        }

        // CORRECCIÓN FINAL: Antes estabas devolviendo R2 local y seteando this->R2 a 0.0
        return [
            "R2" => $this->R2, 
            "solutions" => $this->solutions, 
            "SST" => $this->SST, 
            "SSE" => $this->SSE,
            "predictions" => $this->predictions
        ];
    }
}