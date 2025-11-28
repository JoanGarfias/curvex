<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MuestreoAceptacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MuestroAceptacionController extends Controller
{
    public function test(Request $request){
        Log::info('Muestro Aceptacion test endpoint hit.');
        $validated = $request->validate([
            'n' => 'required|integer|min:1',
            'k' => 'required|integer|min:0',
            'p' => 'required|numeric|min:0|max:1',
        ]);
        
        // Validar que k <= n después de la validación inicial
        if ($validated['k'] > $validated['n']) {
            return response()->json([
                'message' => 'El valor de k no puede ser mayor que n.',
                'errors' => [
                    'k' => ['El número de éxitos (k) debe ser menor o igual al número de ensayos (n).']
                ]
            ], 422);
        }

        Log::info('Muestro Aceptacion test received:', $validated);

        $muestreoService = new MuestreoAceptacionService();
        
        // Convertir p a float explícitamente
        $n = (int) $validated['n'];
        $k = (int) $validated['k'];
        $p = (float) $validated['p'];
        
        // Calcular distribución binomial acumulativa
        $resultadoAcumulado = $muestreoService->binomDistAcum($n, $k, $p);
        
        // Calcular distribución binomial puntual para referencia
        $resultadoPuntual = $muestreoService->binomDist($n, $k, $p);

        return response()->json([
            'n' => $n,
            'k' => $k,
            'p' => $p,
            'probabilidad_acumulada' => $resultadoAcumulado,  // P(X ≤ k)
            'probabilidad_puntual' => $resultadoPuntual,      // P(X = k)
            'message' => 'Distribución binomial calculada correctamente.'
        ], 200);
    }
}
