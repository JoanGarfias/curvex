<?php

namespace App\Services;

use MathPHP\Probability\Distribution\Continuous\StudentT;
use MathPHP\Probability\Distribution\Continuous\StandardNormal;
use Exception;

class CorreccionService
{
    protected StatisticService $statisticService;

    public function __construct(StatisticService $statisticService)
    {
        $this->statisticService = $statisticService;
    }

    /**
     * Procesa datos desde un archivo o string y retorna array de números
     */
    public function procesarDatos($data): array
    {
        if (isset($data['file'])) {
            return $this->procesarArchivo($data['file']);
        } else if (isset($data['values'])) {
            return $this->procesarTexto($data['values']);
        }
        
        return [];
    }

    /**
     * Procesa un archivo y extrae los números
     */
    private function procesarArchivo($file): array
    {
        $numbers = [];
        $path = $file->getRealPath();
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
        
        return $numbers;
    }

    /**
     * Procesa texto con números separados por espacios
     */
    private function procesarTexto(string $values): array
    {
        $numbers = preg_split('/\s+/', trim($values));
        return array_filter(array_map('floatval', $numbers));
    }

    /**
     * Valida que la confiabilidad esté en rango válido
     */
    public function validarConfiabilidad(float $confiabilidad): void
    {
        if ($confiabilidad < 0 || $confiabilidad > 100) {
            throw new Exception("La confiabilidad no puede ser menor a 0 o mayor a 100. Ingresa otro valor para tu confiabilidad.");
        }
    }

    /**
     * Valida que la muestra no sea mayor al total
     */
    public function validarTamanoMuestra(int $tamanoMuestra, int $totalDatos): void
    {
        if ($tamanoMuestra >= $totalDatos) {
            throw new Exception("La cantidad de la muestra de datos es mayor al total ingresado de datos. Ingresa otro total o coloca menos datos en la muestra.");
        }
    }

    /**
     * Calcula el valor crítico según el tamaño de muestra
     */
    public function calcularValorCritico(int $tamanoMuestra, float $alpha): array
    {
        $valorCritico = 0;
        $alphaFinal = $alpha;
        $distribucion = '';

        if ($tamanoMuestra <= 30) {
            // Distribución T de Student
            $distribution = new StudentT($tamanoMuestra - 1);
            $valorCritico = $distribution->inverse2Tails($alpha);
            $distribucion = 'Distribución T';
        } else {
            // Distribución Normal Estándar
            $distribution = new StandardNormal();
            $alpha_half = $alpha / 2;
            $p_acumulada_positiva = 1 - $alpha_half;
            $valorCritico = $distribution->inverse($p_acumulada_positiva);
            $alphaFinal = $alpha_half;
            $distribucion = 'Distribución Normal Estándar';
        }

        return [
            'valor_critico' => $valorCritico,
            'alpha' => $alphaFinal,
            'distribucion' => $distribucion
        ];
    }

    /**
     * Calcula varianza corregida
     */
    public function calcularVarianzaCorregida(int $totalDatos, int $tamanoMuestra, float $varianza): float
    {
        return (($totalDatos - $tamanoMuestra) / $totalDatos) * ($varianza / $tamanoMuestra);
    }

    /**
     * Modo infinito (paso 1) - Calcula el tamaño de muestra corregido
     */
    public function calcularModoInfinito(
        array $numeros,
        int $cantdatos,
        float $confiabilidad,
        float $error
    ): array {
        $n = count($numeros);
        $this->validarTamanoMuestra($n, $cantdatos);

        $promedio = $this->statisticService->promedio($numeros, $n);
        $varianza = $this->statisticService->obtenerVarianza($numeros, $n, $promedio, 1);

        $alpha = 1 - ($confiabilidad / 100);
        $distribution = new StudentT($n - 1);
        $valorCritico = $distribution->inverse2Tails($alpha);

        $h = (($cantdatos * ($valorCritico ** 2)) * $varianza) / 
             ((1000 * ($error ** 2)) + (($valorCritico ** 2) * $varianza));

        $varianza2 = (($cantdatos - $h) / $cantdatos) * (($varianza ** 2) / $h);

        return [
            'count' => $n,
            'mean' => $promedio,
            'variance' => $varianza,
            'alpha' => $alpha,
            'valor_critico' => $valorCritico,
            'h' => $h,
            'hreal' => ceil($h),
        ];
    }

    /**
     * Modo sin infinito con datos de muestra
     */
    public function calcularSinInfinitoConMuestra(
        array $numeros,
        int $cantdatos,
        float $confiabilidad,
        float $varianzanueva,
        float $promedionuevo
    ): array {
        $n = count($numeros);
        $this->validarTamanoMuestra($n, $cantdatos);

        $promedio = $this->statisticService->promedio($numeros, $n);
        $varianza = $this->statisticService->obtenerVarianza($numeros, $n, $promedio, 1);

        $alpha = 1 - ($confiabilidad / 100);
        $cantdatoscorregido = $n;

        return $this->calcularResultadosFinales(
            $cantdatos,
            $cantdatoscorregido,
            $varianzanueva,
            $alpha,
            $confiabilidad,
            $promedionuevo
        );
    }

    /**
     * Modo sin infinito sin datos de muestra (solo parámetros)
     */
    public function calcularSinInfinitoSinMuestra(
        int $cantdatos,
        int $cantdatoscorregido,
        float $confiabilidad,
        float $varianzanueva,
        float $promedionuevo
    ): array {
        if ($cantdatoscorregido < 0) {
            throw new Exception("La cantidad de la muestra corregida de datos debe ser mayor a cero. Ingresa otro total de datos en la muestra corregida.");
        }

        if ($cantdatoscorregido >= $cantdatos) {
            throw new Exception("La cantidad de la muestra corregida de datos es mayor al total ingresado de datos. Ingresa otro total o coloca un total menor de datos en la muestra corregida.");
        }

        $alpha = 1 - ($confiabilidad / 100);

        return $this->calcularResultadosFinales(
            $cantdatos,
            $cantdatoscorregido,
            $varianzanueva,
            $alpha,
            $confiabilidad,
            $promedionuevo
        );
    }

    /**
     * Calcula los resultados finales comunes
     */
    private function calcularResultadosFinales(
        int $cantdatos,
        int $cantdatoscorregido,
        float $varianzanueva,
        float $alpha,
        float $confiabilidad,
        float $promedionuevo
    ): array {
        $varianza2 = $this->calcularVarianzaCorregida($cantdatos, $cantdatoscorregido, $varianzanueva);
        $desviacion2 = sqrt($varianza2);

        $criticalData = $this->calcularValorCritico($cantdatoscorregido, $alpha);
        $limite = $desviacion2 * $criticalData['valor_critico'];

        return [
            'variance' => $varianzanueva,
            'alpha' => $criticalData['alpha'],
            'valor_critico' => $criticalData['valor_critico'],
            'variance2' => $varianza2,
            'desviacion2' => $desviacion2,
            'limite' => $limite,
            'promedio' => $promedionuevo,
            'cosa' => $criticalData['distribucion'],
        ];
    }
}
