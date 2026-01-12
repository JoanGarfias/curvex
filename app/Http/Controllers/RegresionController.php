<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordsRequest;
use App\Http\Requests\GetRegresionValueRequest;
use App\Services\RegresionService;
use App\ValueObjects\VariableData;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\PseudoTypes\LowercaseString;

class RegresionController extends Controller
{
    public function calcular(CoordsRequest $request)
    {
        try {
            Log::info("Iniciando cálculo de regresión");
            
            $data = $request->validated();

            // Parsear la variable dependiente (y)
            $dependent_str = trim($data['dependent']);
            $dependent_values = array_map(
                fn($val) => (float)trim($val),
                explode(',', $dependent_str)
            );

            Log::info("Variable dependiente (Y): " . count($dependent_values) . " datos");
            Log::debug("Valores de Y: " . implode(', ', $dependent_values));

            // Parsear las variables independientes
            $independent_variables = [];
            foreach ($data['independent'] as $idx => $ind_str) {
                $ind_str = trim($ind_str);
                $ind_values = array_map(
                    fn($val) => (float)trim($val),
                    explode(',', $ind_str)
                );
                
                $variable_data = new VariableData($ind_values);
                $independent_variables[] = $variable_data;
                
                Log::info("Variable independiente #{$idx}: " . count($ind_values) . " datos");
                Log::debug("Valores: " . implode(', ', $ind_values));
            }

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
}
