<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception; // Para el try-catch general

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
            $promedio = $this->promedio($listaNumeros, $n);

            // double valmin = valormin(mat, n)
            $valmin = $this->valormin($listaNumeros, $n); // O simplemente: min($listaNumeros)

            // double valmax = valormax(mat, n)
            $valmax = $this->valormax($listaNumeros, $n); // O simplemente: max($listaNumeros)

            // double rango = valmax - valmin
            $rango = $valmax - $valmin;

            // double varianza = obtenerVarianza(mat,n,promedio)
            $varianza = $this->obtenerVarianza($listaNumeros, $n, $promedio);

            // double desviacionEstandar (Cálculo implícito en tu Java)
            $desviacionEstandar = sqrt($varianza);
            
            // double curtosis = obtenerCurtosis(mat,n,promedio,Math.sqrt(varianza))
            $curtosis = $this->obtenerCurtosis($listaNumeros, $n, $promedio, $desviacionEstandar);


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

    // --- MÉTODOS PRIVADOS (Tus funciones de Java adaptadas a PHP) ---

    /**
     * Calcula el promedio (media).
     * Equivalente a tu función promedio().
     */
    private function promedio(array $lista, int $n): float
    {
        // En PHP, esto es más simple con array_sum(), pero replicamos la lógica:
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += $valor;
        }
        return $res / $n;
        // Forma PHP simple: return array_sum($lista) / $n;
    }

    /**
     * Encuentra el valor mínimo.
     * Equivalente a tu función valormin().
     */
    private function valormin(array $lista, int $n): float
    {
        // La función min() de PHP hace esto.
        return min($lista);
    }

    /**
     * Encuentra el valor máximo.
     * Equivalente a tu función valormax().
     */
    private function valormax(array $lista, int $n): float
    {
        // La función max() de PHP hace esto.
        return max($lista);
    }

    /**
     * Calcula la Varianza Poblacional (divide entre 'n').
     * Equivalente a tu función obtenerVarianza().
     */
    private function obtenerVarianza(array $lista, int $n, float $promedio): float
    {
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += pow($valor - $promedio, 2);
        }
        
        if ($n === 0) return 0.0; // Evitar división por cero
        
        return $res / $n; // Varianza Poblacional
    }

    /**
     * Calcula la Curtosis Excesiva.
     * Equivalente a tu función obtenerCurtosis().
     */
    private function obtenerCurtosis(array $lista, int $n, float $promedio, float $desviacion): float
    {
        if ($desviacion == 0) {
            // Si la desviación es 0, todos los valores son iguales.
            // La curtosis no está definida (división por cero).
            // Devolvemos 0 o null según la lógica de negocio.
            return 0.0;
        }
        
        $res = 0.0;
        foreach ($lista as $valor) {
            $res += pow($valor - $promedio, 4);
        }
        
        $denominador = ($n * pow($desviacion, 4));

        if ($denominador == 0) return 0.0; // Doble chequeo
        
        // (res / denominador) - 3 es la "Curtosis Excesiva"
        return ($res / $denominador) - 3;
    }
}

?>