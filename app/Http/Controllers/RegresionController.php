<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordsRequest;
use App\Services\RegresionService;
use App\ValueObjects\VariableData;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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
        } catch (\Exception $e) {
            Log::error("Error en cálculo de regresión: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error al realizar el cálculo de regresión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
