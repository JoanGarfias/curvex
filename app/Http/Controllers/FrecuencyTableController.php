<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;
use App\Services\FrequencyService;

class FrecuencyTableController extends Controller
{
    /**
     * Calcula la tabla de frecuencias.
     * 
     * @param StatisticRequest $request
     * @return JsonResponse
     */
    public function calculateFrequencyTable(StatisticRequest $request): JsonResponse
    {
        // 1. VALIDACIÓN
        $data = $request->validated();
        $frequencyService = new FrequencyService();

        // 2. OBTENER NÚMEROS
        if (isset($data['file'])) {
            $numbers = [];
            $path = $data['file']->getRealPath();
            $file = fopen($path, 'r');
            if ($file !== false) {
                while (($line = fgetcsv($file)) !== false) {
                    foreach ($line as $value) {
                        $trimmed = trim($value);
                        if (is_numeric($trimmed)) {
                            $numbers[] = floatval($trimmed);
                        }
                    }
                }
                fclose($file);
            }
        } else {
            // Parse space-separated numbers
            $numbers = preg_split('/\s+/', trim($data['values']));
            $numbers = array_filter(array_map('floatval', $numbers));
        }

        try {
            // 3. Generar tabla de frecuencias usando el servicio
            $result = $frequencyService->generateFrequencyTable($numbers);
            
            // Validar si todos los valores son idénticos
            if (isset($result['info_intervalos']['mensaje'])) {
                return response()->json([
                    'message' => 'No se puede generar la tabla de frecuencias: todos los valores son idénticos.'
                ], 422);
            }

            // 4. Devolver respuesta
            return response()->json($result, 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error inesperado.', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
}