<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatisticsCorreccionRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

use App\Services\StatisticService;
use App\Services\FrequencyService;
use App\Services\CorreccionService;

class CorreccionStatisticsController extends Controller
{
    protected CorreccionService $correccionService;

    public function __construct(CorreccionService $correccionService)
    {
        $this->correccionService = $correccionService;
    }
    /**
     * Recibe una lista de números y calcula sus estadísticas.
     *
     * @param StatisticsCorreccionRequest $request
     * @return JsonResponse
     */
    public function corregir(StatisticsCorreccionRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Procesar datos de entrada
        $numbers = $this->correccionService->procesarDatos($data);

        // Validar si se requieren datos cuando modo es infinito
        if (empty($numbers)) {
            if (!isset($data['infinito']) || $data['infinito'] == true) {
                return response()->json([
                    'message' => 'Ocurrió un error.',
                    'error' => "Proporcione valores de la muestra, o cambie el metodo de correccion de varianza."
                ], 422);
            }
        }

        try {
            Log::info('Varianza muestral');

            // Extraer parámetros
            $modo = isset($data['infinito']) && $data['infinito'] ? 1 : 0;
            $modovarianza = isset($data['modovarianza']) && $data['modovarianza'] ? 1 : 0;
            $cantdatos = $data['cantdatos'] ?? 0;
            $error = $data['error'] ?? 0;
            $confiabilidad = $data['confiabilidad'] ?? 0;
            $cantdatoscorregido = $data['cantdatoscorregido'] ?? 0;
            $varianzanueva = $data['varianza'] ?? 0;
            $promedionuevo = $data['promedio'] ?? 0;

            // Validar confiabilidad
            $this->correccionService->validarConfiabilidad($confiabilidad);

            // Ejecutar cálculo según el modo
            if ($modo == 1) {
                // Modo infinito (paso 1)
                $resultados = $this->correccionService->calcularModoInfinito(
                    $numbers,
                    $cantdatos,
                    $confiabilidad,
                    $error
                );
            } else {
                // Modo sin infinito (paso 2)
                if ($modovarianza == 1) {
                    // Sin datos de muestra
                    $resultados = $this->correccionService->calcularSinInfinitoSinMuestra(
                        $cantdatos,
                        $cantdatoscorregido,
                        $confiabilidad,
                        $varianzanueva,
                        $promedionuevo
                    );
                } else {
                    // Con datos de muestra
                    $resultados = $this->correccionService->calcularSinInfinitoConMuestra(
                        $numbers,
                        $cantdatos,
                        $confiabilidad,
                        $varianzanueva,
                        $promedionuevo
                    );
                }
            }

            return response()->json($resultados, 200);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error inesperado.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

?>