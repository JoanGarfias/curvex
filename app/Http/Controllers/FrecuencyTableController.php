<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class FrecuencyTableController extends Controller
{
    /**
     * NUEVO MÉTODO (Tabla de Frecuencias)
     * Adaptado de tu constructor TablaFrecuencias()
     */
    public function calculateFrequencyTable(StatisticRequest $request): JsonResponse
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
            // Parse space-separated numbers
            $numbers = preg_split('/\s+/', trim($data['values']));
            $numbers = array_filter(array_map('floatval', $numbers));
        }

        try {
            $lista = $numbers;
            
            // 2. Calcular estadísticas básicas necesarias (en lugar de recibirlas)
            $n = count($lista);
            $minimo = min($lista);
            $maximo = max($lista);
            $rango = $maximo - $minimo;
            
            // Validar que haya un rango (evita división por cero si todos los números son iguales)
            if ($rango == 0) {
                 return response()->json(['message' => 'No se puede generar la tabla de frecuencias: todos los valores son idénticos.'], 422);
            }

            // 3. Lógica de TablaFrecuencias() adaptada
            
            // double numIntervalos = Math.round(1+3.3*Math.log10(n)); (Regla de Sturges)
            $numIntervalos = (int) round(1 + 3.3 * log10($n));

            // double ancho = rango / numIntervalos;
            $ancho = $rango / $numIntervalos;

            // double [][] listareal = this.prepararMatriz(lista, n);
            // Usamos un array asociativo para manejar el flag 'counted'
            $listaConFlags = $this->prepararListaConFlags($lista);

            // double [][] limites = new double[(int)numIntervalos][2];
            $limites = [];
            for ($i = 0; $i < $numIntervalos; $i++) {
                $lim_inf = $minimo + ($i * $ancho);
                $lim_sup = $lim_inf + $ancho;
                $limites[] = [$lim_inf, $lim_sup];
            }
            
            // *** NOTA IMPORTANTE DE LÓGICA ***
            // Tu lógica original `lista[j][0] <= limites[i][1]` maneja correctamente el valor máximo.
            // Por ejemplo, si el último intervalo es [13.25, 15.0] y el valor máx es 15.0,
            // será contado. Esto funciona bien.

            // double [] frecuencias = obtenerFrecuencias(listareal, limites, n, (int)numIntervalos);
            // Pasamos $listaConFlags por referencia (&) para que se pueda modificar
            $frecuencias = $this->obtenerFrecuencias($listaConFlags, $limites, $n, $numIntervalos);

            // 4. Construir la respuesta JSON (en lugar de `Object[][] data`)
            $frecAcumulada = 0;
            $tablaResultados = [];
            
            $prec_limites = 3; // Precisión para redondear límites
            $prec_pct = 2;     // Precisión para redondear porcentajes

            for ($i = 0; $i < $numIntervalos; $i++) {
                $frec = $frecuencias[$i];
                $frecAcumulada += $frec;

                $lim_inf = $limites[$i][0];
                $lim_sup = $limites[$i][1];
                $marca_clase = ($lim_inf + $lim_sup) / 2;
                
                // Evitar división por cero si n=0 (aunque ya validamos min:2)
                $frec_rel_pct = ($n > 0) ? ($frec / $n) * 100 : 0;
                $frec_rel_acum_pct = ($n > 0) ? ($frecAcumulada / $n) * 100 : 0;

                // Esto reemplaza tu `Object test[] = { ... }`
                $tablaResultados[] = [
                    'clase' => "Clase " . ($i + 1),
                    'limite_inferior' => round($lim_inf, $prec_limites),
                    'limite_superior' => round($lim_sup, $prec_limites),
                    'marca_de_clase' => round($marca_clase, $prec_limites),
                    'frecuencia_absoluta' => (int) $frec,
                    'frecuencia_abs_acumulada' => (int) $frecAcumulada,
                    'frecuencia_relativa_pct' => round($frec_rel_pct, $prec_pct),
                    'frecuencia_rel_acumulada_pct' => round($frec_rel_acum_pct, $prec_pct),
                ];
            }
            
            // 5. Devolver un JSON estructurado
            $response = [
                'info_intervalos' => [
                    'numero_intervalos' => $numIntervalos,
                    'ancho_intervalo' => round($ancho, $prec_limites),
                ],
                'tabla_frecuencias' => $tablaResultados
            ];
            
            return response()->json($response, 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'Ocurrió un error inesperado.', 'error' => $e->getMessage()], 500);
        }
    }


    // --- MÉTODOS PRIVADOS (Helpers) ---

    // --- Helpers para 'calculate' ---
    private function promedio(array $lista, int $n): float {
        if ($n === 0) return 0.0;
        return array_sum($lista) / $n;
    }
    private function valormin(array $lista, int $n): float { return min($lista); }
    private function valormax(array $lista, int $n): float { return max($lista); }
    private function obtenerVarianza(array $lista, int $n, float $promedio): float {
        if ($n === 0) return 0.0;
        $res = 0.0;
        foreach ($lista as $valor) { $res += pow($valor - $promedio, 2); }
        return $res / $n;
    }
    private function obtenerCurtosis(array $lista, int $n, float $promedio, float $desviacion): float {
        if ($desviacion == 0 || $n == 0) return 0.0;
        $res = 0.0;
        foreach ($lista as $valor) { $res += pow($valor - $promedio, 4); }
        $denominador = ($n * pow($desviacion, 4));
        if ($denominador == 0) return 0.0;
        return ($res / $denominador) - 3;
    }
    
    // --- NUEVOS Helpers para 'calculateFrequencyTable' ---

    /**
     * Equivalente a prepararMatriz() de Java.
     * Crea un array donde cada item tiene el valor y un flag 'counted'.
     */
    private function prepararListaConFlags(array $lista): array
    {
        $listaConFlags = [];
        foreach ($lista as $valor) {
            // Reemplaza A[i][0] = lista[i] y A[i][1] = 0.0
            $listaConFlags[] = ['value' => $valor, 'counted' => false];
        }
        return $listaConFlags;
    }

    /**
     * Equivalente a obtenerFrecuencias() de Java.
     * Importante: $listaConFlags se pasa por referencia (&) para
     * que los cambios en 'counted' se mantengan.
     */
    private function obtenerFrecuencias(array &$listaConFlags, array $limites, int $n, int $numIntervalos): array
    {
        $frecuencias = array_fill(0, $numIntervalos, 0); // Inicializa el array de frecuencias en 0

        for ($i = 0; $i < $numIntervalos; $i++) {
            $elems = 0;
            $lim_inf = $limites[$i][0];
            $lim_sup = $limites[$i][1];

            for ($j = 0; $j < $n; $j++) {
                
                // Esta es la réplica exacta de tu lógica 'if'
                if ($listaConFlags[$j]['value'] >= $lim_inf &&
                    $listaConFlags[$j]['value'] <= $lim_sup &&
                    !$listaConFlags[$j]['counted'])
                {
                    $elems += 1;
                    $listaConFlags[$j]['counted'] = true; // Marcar como contado (equiv. a lista[j][1] = 1)
                }
            }
            $frecuencias[$i] = $elems;
        }
        
        // La lógica de Java (contar en el primer intervalo que coincide) es robusta
        // para valores que caen justo en los límites. No se necesita código extra.
        return $frecuencias;
    }
}