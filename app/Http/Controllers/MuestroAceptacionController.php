<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MuestreoAceptacionService;
use App\Http\Requests\MuestroAceptacionInput as MuestroAceptacionInputRequest;
use App\Http\Requests\MuestroAceptacionInputNC as MuestroAceptacionInputNCRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MuestroAceptacionController extends Controller
{
    public function calcular(MuestroAceptacionInputRequest $request){
        //Version que usa AQL y LTPD para conseguir n y c
        $validated = $request->validated();
        
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
        
        // Convertir a float explícitamente
        $AQT = (float) $validated['AQT'];
        $LTPD = (float) $validated['LTPD'];
        $alfa = (float) $validated['1-alpha'];
        $beta = (float) $validated['beta'];
        
        // Calcular el plan óptimo
        $distancias = $muestreoService->calcularPlanOptimo($AQT, $LTPD, $alfa, $beta);
        
        // Generar datos para la gráfica
        $datos = $muestreoService->generarDatosGrafica(
            $distancias["n"], 
            $distancias["c"], 
            $AQT, 
            $LTPD
        );

        return response()->json([
            'distancia_menor' => $distancias,
            'grafica' => $datos
        ], 200);
    
    }
    
    public function calcular2(MuestroAceptacionInputNCRequest $request){
        //Version que usa n y c para conseguir AQL y LTPD
        $validated = $request->validated();
        
        $muestreoService = new MuestreoAceptacionService();
        
        $n = $validated['n'];
        $c = $validated['c'];

        // Convertir a float explícitamente
        $alfa = (float) $validated['1-alpha'];
        $beta = (float) $validated['beta'];
        
        // Calcular AQL y LTPD
        $distancias = $muestreoService->calcularAQLyLTPD($n, $c, $alfa, $beta);
        
        // Se restauran los valores para mantener compatibilidad con la graficación
        $AQT = $distancias["AQL"];
        $LTPD = $distancias["LTPD"];
        
        // Generar datos para la gráfica
        $datos = $muestreoService->generarDatosGrafica($n, $c, $AQT, $LTPD);

        return response()->json([
            'distancia_menor' => $distancias,
            'grafica' => $datos
        ], 200);
    
    }
}
