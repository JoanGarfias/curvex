<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use MathPHP\Probability\Distribution\Continuous;

class FrequencyService
{
    /**
     * Genera la tabla de frecuencias completa.
     * 
     * @param array $lista Array de números
     * @return array Tabla de frecuencias con info de intervalos
     */
    public function generateFrequencyTable(array $lista, float $promedio): array
    {
        $n = count($lista);
        
        if ($n === 0) {
            return [
                'info_intervalos' => [],
                'tabla_frecuencias' => []
            ];
        }

        $minimo = min($lista);
        $maximo = max($lista);
        $rango = $maximo - $minimo;
        
        // Validar que haya un rango (evita división por cero si todos los números son iguales)
        if ($rango == 0) {
            return [
                'info_intervalos' => [
                    'numero_intervalos' => 0,
                    'ancho_intervalo' => 0,
                    'mensaje' => 'Todos los valores son idénticos'
                ],
                'tabla_frecuencias' => []
            ];
        }

        // Regla de Sturges: numIntervalos = 1 + 3.3 * log10(n)
        $numIntervalos = (int) round(1 + 3.3 * log10($n));
        $ancho = $rango / $numIntervalos;

        // Preparar lista con flags
        $listaConFlags = $this->prepararListaConFlags($lista);

        // Calcular límites de cada intervalo
        $limites = [];
        for ($i = 0; $i < $numIntervalos; $i++) {
            $lim_inf = $minimo + ($i * $ancho);
            $lim_sup = $lim_inf + $ancho;
            $limites[] = [$lim_inf, $lim_sup];
        }

        // Obtener frecuencias
        $frecuencias = $this->obtenerFrecuencias($listaConFlags, $limites, $n, $numIntervalos);

        // Construir tabla de resultados
        $tablaResultados = $this->construirTablaResultados($limites, $frecuencias, $numIntervalos, $n, $promedio);

        return [
            'info_intervalos' => [
                'numero_intervalos' => $numIntervalos,
                'ancho_intervalo' => round($ancho, 3),
            ],
            'tabla_frecuencias' => $tablaResultados
        ];
    }

    /**
     * Prepara la lista con flags para marcar elementos ya contados.
     * Equivalente a prepararMatriz() de Java.
     * 
     * @param array $lista
     * @return array
     */
    private function prepararListaConFlags(array $lista): array
    {
        $listaConFlags = [];
        foreach ($lista as $valor) {
            $listaConFlags[] = ['value' => $valor, 'counted' => false];
        }
        return $listaConFlags;
    }

    /**
     * Calcula las frecuencias para cada intervalo.
     * Equivalente a obtenerFrecuencias() de Java.
     * 
     * @param array $listaConFlags Array con valores y flags (pasado por referencia)
     * @param array $limites Array de límites [inferior, superior] para cada intervalo
     * @param int $n Número total de elementos
     * @param int $numIntervalos Número de intervalos
     * @return array Frecuencias para cada intervalo
     */
    private function obtenerFrecuencias(array &$listaConFlags, array $limites, int $n, int $numIntervalos): array
    {
        $frecuencias = array_fill(0, $numIntervalos, 0);

        for ($i = 0; $i < $numIntervalos; $i++) {
            $elems = 0;
            $lim_inf = $limites[$i][0];
            $lim_sup = $limites[$i][1];

            for ($j = 0; $j < $n; $j++) {
                if (round($listaConFlags[$j]['value'], 8) >= round($lim_inf, 8) &&
                    round($listaConFlags[$j]['value'], 8) <= round($lim_sup, 8) &&
                    !$listaConFlags[$j]['counted'])
                {
                    $elems += 1;
                    $listaConFlags[$j]['counted'] = true;
                }
            }
            $frecuencias[$i] = $elems;
        }

        return $frecuencias;
    }

    /**
     * Construye la tabla de resultados con todas las columnas.
     * 
     * @param array $limites
     * @param array $frecuencias
     * @param int $numIntervalos
     * @param int $n Total de elementos
     * @return array
     */
    private function construirTablaResultados(array $limites, array $frecuencias, int $numIntervalos, int $n, float $promedio): array
    {
        $normal = new Continuous\Normal(round($promedio), 2);

        $tablaResultados = [];
        $frecAcumulada = 0;
        
        $prec_limites = 3; // Precisión para redondear límites
        $prec_pct = 2;     // Precisión para redondear porcentajes

        for ($i = 0; $i < $numIntervalos; $i++) {
            $frec = $frecuencias[$i];
            $frecAcumulada += $frec;

            $lim_inf = $limites[$i][0];
            $lim_sup = $limites[$i][1];
            $marca_clase = ($lim_inf + $lim_sup) / 2;
            
            $frec_rel_pct = ($n > 0) ? ($frec / $n) * 100 : 0;
            $frec_rel_acum_pct = ($n > 0) ? ($frecAcumulada / $n) * 100 : 0;

            $prob_li = $normal->cdf($lim_inf);
            $prob_sup = $normal->cdf($lim_sup);
            $prob_total = $prob_sup - $prob_li;
            $esp = $prob_total * $n;

            $tablaResultados[] = [
                'clase' => "Clase " . ($i + 1),
                'limite_inferior' => round($lim_inf, $prec_limites),
                'limite_superior' => round($lim_sup, $prec_limites),
                'marca_de_clase' => round($marca_clase, $prec_limites),
                'frecuencia_absoluta' => (int) $frec,
                'frecuencia_abs_acumulada' => (int) $frecAcumulada,
                'frecuencia_relativa_pct' => round($frec_rel_pct, $prec_pct),
                'frecuencia_rel_acumulada_pct' => round($frec_rel_acum_pct, $prec_pct),
                'prob_li' => $prob_li,
                'prob_ls' => $prob_sup,
                'prob_total' => $prob_total,
                'esperado' => $esp,
                'chisinsuma' => ((((int) $frec) - $esp)**2) / $esp,
            ];
        }

        return $tablaResultados;
    }


    public function generateChiData(array $tablaFrecuencias, int $promedio, $desviacionEstandar, $cantidadDatos, $numeroDeClases){
        //alpha es el nivel de significancia, este lo da el usuario.
        //Recorro el array de tablas de frecuencias para obtener limite inferior, superior, frecuencia absoluta, etc
        
        //Chi cuadrada
        $sumaChiCua = 0.0;
        
        Log::info("Generando datos Chi. Promedio: " . $promedio . " - Desviación Estándar: " . round($desviacionEstandar));

        foreach($tablaFrecuencias as $claseData){
            //Calcular la probabilidad de que X esté entre los límites
            $normDistLimiteInferior = self::normDistAcumulado(
                $claseData['limite_inferior'],
                $promedio,
                round($desviacionEstandar)
            );

            $normDistLimiteSuperior = self::normDistAcumulado(
                $claseData['limite_superior'],
                $promedio,
                round($desviacionEstandar)
            );

            Log::info("Limite Inferior: " . $claseData['limite_inferior'] . " - NormDist: " . $normDistLimiteInferior);
            Log::info("Limite Superior: " . $claseData['limite_superior'] . " - NormDist: " . $normDistLimiteSuperior);
            
            $probabilidadLimites = $normDistLimiteSuperior - $normDistLimiteInferior;

            //SumaChi = (Oi-Ei)² / Ei 
            //Ei = ProbabilidadLimites * cantidad de datos
            //Oi = Frecuencia absoluta
            $Ei = $probabilidadLimites * $cantidadDatos;
            $Oi = $claseData["frecuencia_absoluta"];
            $sumaChiCua += (pow(1.0 * ($Oi-$Ei), 2) ) / $Ei;
        }

        //Grados de libertad = desviacion - 1 + numero de clases

        $gradosDeLibertad = round($desviacionEstandar) - 1 + $numeroDeClases;

        /*Por defecto 
        $probIzquierda = 1 - $alpha;
        $which = STATS_CDF_X;
        $xACalcular = 0.0;
        
        $valorCritico = stats_cdf_chisquare(
            $xACalcular,
            $gradosDeLibertad,
            $probIzquierda,
            $which
        );*/

        return [
            "chicua" => $sumaChiCua,
            "grados_libertad" => $gradosDeLibertad
        ];

    }


    public static function normDistAcumulado(float $x, float $promedio, float $desviacionEstandar){
        //Esta función asume que el parámetro de acumulado = VERDADERO y utiliza la integral
        //Se -infinito hasta x

        if($desviacionEstandar <= 0.0){
            return NAN;
        }

        $z = ($x - $promedio) / $desviacionEstandar;
        
        $phiZ = 0.5 * (1.0 + self::erf($z / sqrt(2.0)));

        return $phiZ;

    }


    /**
     * Implementa la función de Error (erf) para PHP.
     * Se basa en una aproximación polinómica de alta precisión
     * para evaluar la integral Gaussiana.
     * @param float $y El valor para el cual calcular la erf.
     * @return float El valor de erf(y).
     */
    public static function erf($y) {
        // Constantes de la aproximación de Abramowitz & Stegun (7.1.26)
        $a1 = 0.254829592;
        $a2 = -0.284496736;
        $a3 = 1.421413741;
        $a4 = -1.453152027;
        $a5 = 1.061405429;
        $p = 0.3275911;

        // Manejar el signo: erf(-y) = -erf(y)
        $sign = 1.0;
        if ($y < 0) {
            $sign = -1.0;
            $y = -$y;
        }

        // Cálculo del factor t
        $t = 1.0 / (1.0 + $p * $y);

        // Evaluación del polinomio
        $erf = 1.0 - (((($a5 * $t + $a4) * $t + $a3) * $t + $a2) * $t + $a1) * $t * exp(-$y * $y);

        return $sign * $erf;
    }

}
