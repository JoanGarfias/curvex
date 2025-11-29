<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MuestreoAceptacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MuestroAceptacionController extends Controller
{
    public function calcular(Request $request){
        $validated = $request->validate([
            'AQT' => 'required|numeric|min:0|max:1',
            'LTPD' => 'required|numeric|min:0|max:1',
            '1-alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
        ]);
        
        // Validar que k <= n después de la validación inicial
        if ($validated['LTPD'] < $validated['AQT']) {
            return response()->json([
                'message' => 'El valor de LTPD no puede ser mayor que AQT.',
                'errors' => [
                    'AQT' => ['El AQT debe ser menor o igual al LTPD.']
                ]
            ], 422);
        }
        
        $muestreoService = new MuestreoAceptacionService();
        
        // Convertir p a float explícitamente
        $AQT = (float) $validated['AQT'];
        $LTPD = (float) $validated['LTPD'];
        $alfa = (float) $validated['1-alpha'];
        $beta = (float) $validated['beta'];
        
        $distancias = array();
        $distancias["AQT"] = $AQT;
        $distancias["LTPD"] = $LTPD;
        $distancias["1-alpha"] = $alfa;
        $distancias["beta"] = $beta;

        $distanciamenor = -1;
        $n = 10;
        $k = 1;
        while($n < 500){
            $chequeo = array();
            $menordist = -1;
            $menorc = -1;
            foreach([1,2,3,5,7] as $k){
                
                $probabilidadaqt = $muestreoService->binomDistAcum($n, $k, $AQT);
                $probabilidadltpd = $muestreoService->binomDistAcum($n, $k, $LTPD);
                $distancia = sqrt((($alfa - $probabilidadaqt) ** 2) + ($beta - $probabilidadltpd)**2);

                if(($menordist == -1) || ($menordist > $distancia)){
                    $menordist = $distancia;
                    $menorc = $k;
                }
            }

            if(($distanciamenor == -1) || ($distanciamenor > $menordist)){
                $distancias["n"] = $n;
                $distancias["c"] = $menorc;
                $distancias["distancia"] = $menordist;
                $distanciamenor = $menordist;
            }
            
            $n+=1;
        }

        /*Insertar aqui codigo para obtener la grafica*/

        return response()->json([
            'distancia_menor' => $distancias,
        ], 200);
    
    }
    /*
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
    }*/
}
