<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception; // Para el try-catch general

use App\Services\StatisticService;

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

        // 2. OBTENER NÚMEROS (Equivalente a tu bloque 'try' inicial)

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
            $numbers = preg_split('/[\s,]+/', str_replace(["\r\n", "\r"], "\n", $data['values']));
            $numbers = array_map('floatval', $numbers);
        }

        try {
            $listaNumeros = $numbers;

            // 3. GUARDAR VARIABLES (Equivalente al bloque principal de tu 'try')
            
            // int n = obtenerCantNumeros(...)
            $n = count($listaNumeros);

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


            // 4. CREAR EL JSON DE RESPUESTA
            // (Equivalente a jTextArea2.setText(...))
            // Todas las variables se guardan en un array asociativo:
            $resultados = [
                'count' => $n,
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