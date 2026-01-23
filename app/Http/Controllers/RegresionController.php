<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordsRequest;
use App\Http\Requests\GetRegresionValueRequest;
use App\Services\RegresionBetterResponse;
use App\Services\RegresionService;
use App\Support\Math\Regresion\RegresionLinealModel;
use App\Support\Math\Regresion\RegresionExponentialModel;
use App\Support\Math\Regresion\RegresionPotentialModel;
use App\Support\Math\Regresion\RegresionCuadraticModel;
use App\ValueObjects\VariableData;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

class RegresionController extends Controller
{

    private function convertIndepentArray(array $input): array {
        $n_array = [];    
        foreach ($input as $idx => $ind_str) {
            $ind_str = trim($ind_str);
            $ind_values = array_map(
                fn($val) => (float)trim($val),
                explode(',', $ind_str)
            );
            Log::debug($ind_values);
            $variable_data = new VariableData($ind_values);
            $n_array[] = $variable_data;
        }

        return $n_array;
    }

    private function convertDependentArray(string $input): array {
        $dependent_values = array_map(
                fn($val) => (float)trim($val),
                explode(',', $input)
        );
        return $dependent_values;
    }

    public function calcular(CoordsRequest $request)
    {
        try {
            Log::info("Iniciando cálculo de regresión");
            
            $data = $request->validated();

            // Parsear la variable dependiente (y)
            $dependent_values = $this->convertDependentArray($data['dependent']);

            Log::info("Variable dependiente (Y): " . count($dependent_values) . " datos");
            Log::debug("Valores de Y: " . implode(', ', $dependent_values));

            // Parsear las variables independientes
            $independent_variables = $this->convertIndepentArray($data['independent']);

            Log::info("Total de variables independientes: " . count($independent_variables));

            // Crear el servicio de regresión
            $method = $data['method'] ?? 'lineal';
            $regresionService = RegresionService::createRegresion(
                $independent_variables,
                $dependent_values,
                $method
            );

            Log::info("Servicio de regresión creado: " . class_basename($regresionService));

            // Calcular R²
            $datos = $regresionService->calculateR2();

            Log::info("Cálculo de regresión completado exitosamente. R² = {$datos['R2']}");

            $result = [
                'R2' => $datos['R2'],
                'solutions' => $datos['solutions'],
                'method' => $method,
                'independent_variables_count' => count($independent_variables),
                'data_points_count' => count($dependent_values),
                'SST' => $datos['SST'],
                'SSE' => $datos['SSE'],
                'predictions' => $datos['predictions'],
            ];

            return response()->json([
                'message' => 'Cálculo de regresión realizado con éxito.',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            Log::error("Error en cálculo de regresión: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error al realizar el cálculo de regresión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calcula X dado Y o Y dado X según el método de regresión especificado.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegresionValue(GetRegresionValueRequest $request)
    {
        try {
            $data = $request->validated();

            $method = $data['method'];
            $variable = $data['variable_input'];
            $value = (float) $data['value'];
            $solutions = array_map('floatval', $data['solutions']);

            Log::info("Calculando {$variable} con método {$method}");
            Log::debug("Valor proporcionado: {$value}");
            Log::debug("Soluciones: " . implode(', ', $solutions));

            $result = null;

            if($variable == "x" || $variable == "y"){
                $solver = RegresionService::createRegresionValueSolver($value, $solutions, $method);

                Log::info("Solver" . class_basename($solver));

                $regresionValue = ($variable == "y") ? $solver->calculateXValue() : $solver->calculateYModel($solutions, array($value));

                return response()->json([
                    'message' => "Calculo de $variable realizado con exito",
                    'data' => [
                        'method' => $method,
                        'y' => ($variable=="y")? $value : $regresionValue,
                        'x' => ($variable=="x")? $value : $regresionValue,
                    ]
                ]);
            }
            else{
                return response()->json([
                    'message' => 'Error: la variable que se intenta calcular no es correcta, pruebe x o bien y',
                    'error' => 'Error al intentar calcular el valor',
                ], 500);
            }

        } catch (Exception $e) {
            Log::error("Error en cálculo: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error al realizar el cálculo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function getBestRegresionModel(CoordsRequest $request){
        $data = $request->validated();

        $dependent_values = $this->convertDependentArray($data['dependent']);
        $independent_variables = $this->convertIndepentArray($data['independent']);

        /**@var RegresionLinealModel */
        $linealModel = RegresionService::createRegresion($independent_variables, $dependent_values, "lineal");

        /**@var RegresionPotentialModel */
        $potentialModel = RegresionService::createRegresion($independent_variables, $dependent_values, "potential");

        /**@var RegresionExponentialModel */
        $exponentialModel = RegresionService::createRegresion($independent_variables, $dependent_values, "exponential");

        $cuadraticModel = null;
        if(count($independent_variables) == 1){
            $cuadraticModel = RegresionService::createRegresion($independent_variables, $dependent_values, "cuadratic");
            $cuadraticModel->calculateR2();
        }
        /**@var RegresionCuadraticModel */


        $linealModel->calculateR2();
        $potentialModel->calculateR2();
        $exponentialModel->calculateR2();
        

        /**@var RegresionBetterResponse */
        $bestModel = RegresionService::getBetterModel(
            $linealModel,
            $exponentialModel,
            $potentialModel,
            $cuadraticModel
        );

        $modelos = [
            "RegresionLinealModel" => "lineal",
            "RegresionExponentialModel" => "exponential",
            "RegresionPotentialModel" => "potential",
            "RegresionCuadraticModel" => "cuadratic",
        ];

        $bestestModel = RegresionService::createRegresion($independent_variables, $dependent_values, $modelos[class_basename($bestModel->model)]);

        $result = $bestestModel->calculateR2();

        $result = [
            'R2' => $result['R2'],
            'solutions' => $result['solutions'],
            'method' => $bestestModel->getName(),
            'independent_variables_count' => count($independent_variables),
            'data_points_count' => count($dependent_values),
            'SST' => $result['SST'],
            'SSE' => $result['SSE'],
            'predictions' => $result['predictions'],
        ];

        return response()->json([
                'message' => 'Cálculo de regresión realizado con éxito.',
                'data' => $result,
            ]);

    }
}
