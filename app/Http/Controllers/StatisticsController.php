<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception; // Para el try-catch general

use App\Services\StatisticService;
use App\Services\FrequencyService;

class StatisticsController extends Controller
{
    /**
     * Recibe una lista de números y calcula sus estadísticas.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculate(StatisticRequest $request): JsonResponse
    {
        // 1. VALIDACIÓN (Equivalente a jTextArea1.getText().isBlank() y NumberFormatException)
        $data = $request->validated();
        $service = new StatisticService();
        $frequencyService = new FrequencyService();

        // 2. OBTENER NÚMEROS (Equivalente a tu bloque 'try' inicial)

        if (isset($data['file'])) {
            $numbers = [];
            $path = $data['file']->getRealPath();
            $content = file_get_contents($path);
            
            // Eliminar BOM (Byte Order Mark) si existe
            $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
            
            // Dividir por saltos de línea y procesar cada línea
            $lines = preg_split('/\r\n|\r|\n/', $content);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Intentar separar por comas, espacios o tabulaciones
                $values = preg_split('/[,\s\t]+/', $line);
                
                foreach ($values as $value) {
                    $trimmed = trim($value);
                    if ($trimmed !== '' && is_numeric($trimmed)) {
                        $numbers[] = floatval($trimmed);
                    }
                }
            }
        } else {
            // Parse space-separated numbers
            $numbers = preg_split('/\s+/', trim($data['values']));
            $numbers = array_filter(array_map('floatval', $numbers));
        }

        try {
            $listaNumeros = $numbers;

            // 3. GUARDAR VARIABLES (Equivalente al bloque principal de tu 'try')
            
            // int n = obtenerCantNumeros(...)
            $n = count($listaNumeros);

            //Suma
            $suma = $service->suma($listaNumeros, $n);

            // double promedio = promedio(mat, n)
            $promedio = $service->promedio($listaNumeros, $n);

            // double valmin = valormin(mat, n)
            $valmin = $service->valormin($listaNumeros, $n); // O simplemente: min($listaNumeros)

            // double valmax = valormax(mat, n)
            $valmax = $service->valormax($listaNumeros, $n); // O simplemente: max($listaNumeros)

            // double rango = valmax - valmin
            $rango = $valmax - $valmin;

            // double varianza = obtenerVarianza(mat,n,promedio)
            $varianza = $service->obtenerVarianza($listaNumeros, $n, $promedio);

            // double desviacionEstandar (Cálculo implícito en tu Java)
            $desviacionEstandar = sqrt($varianza);
            
            // double curtosis = obtenerCurtosis(mat,n,promedio,Math.sqrt(varianza))
            $curtosis = $service->obtenerCurtosis($listaNumeros, $n, $promedio, $desviacionEstandar);

            $cuartiles = $service->obtenerPercentiles($listaNumeros, 4);
            $deciles = $service->obtenerPercentiles($listaNumeros, 10);
            $percentiles = $service->obtenerPercentiles($listaNumeros, 100);

            // Generar tabla de frecuencias
            $frequencyTable = $frequencyService->generateFrequencyTable($listaNumeros);


            // 4. CREAR EL JSON DE RESPUESTA
            // (Equivalente a jTextArea2.setText(...))
            // Todas las variables se guardan en un array asociativo:
            $resultados = [
                'count' => $n,
                'sum' => $suma,
                'mean' => $promedio,
                'min' => $valmin,
                'max' => $valmax,
                'range' => $rango,
                'variance' => $varianza, // Varianza poblacional
                'standard_deviation' => $desviacionEstandar,
                'kurtosis' => $curtosis, // Curtosis excesiva
                'cuartiles' => $cuartiles,
                'deciles' => $deciles,
                'percentiles' => $percentiles,
                'data' => $listaNumeros,
                'frequency_table' => $frequencyTable, // Nueva: tabla de frecuencias
            ];

            // 5. DEVOLVER RESPUESTA
            // (Equivalente a JOptionPane.showMessageDialog(rootPane, "Operación exitosa!"))
            return response()->json($resultados, 200);

        } catch (Exception $e) {
            // 6. MANEJO DE ERRORES GENERALES
            // (Equivalente a tu 'catch(Exception e)')
            return response()->json(['message' => 'Ocurrió un error inesperado.', 'error' => $e->getMessage()], 500);
        }
    }
}

?>