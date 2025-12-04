<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\MuestreoAceptacionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MuestroAceptacionController extends Controller
{
    public function calcular(Request $request){
        //Version que usa AQL y LTPD para conseguir n y c
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
        $datos = array();
        if($LTPD <= 0.1){
            $i = 0.01;
            while($i < 0.10){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], round($i, 4));
                $datos[] = $punto;
                $i+=0.01;
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $AQT);
            $datos[] = $punto;

        }else{
            $constante = round(($LTPD / 10  ), 8);
            $i =  round($constante, 4);
            while($i < $LTPD){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], round($i, 4));
                $datos[] = $punto;
                $i+=round($constante, 4);
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $AQT);
            $datos[] = $punto;
        }

        return response()->json([
            'distancia_menor' => $distancias,
            'grafica' => $datos
        ], 200);
    
    }
    
    public function calcular2(Request $request){
        //Version que usa n y c para conseguir AQL y LTPD
        $validated = $request->validate([
            'n' => 'required|integer|min:2',
            'c' => 'required|integer|min:0|max:7',
            '1-alpha' => 'required|numeric|min:0|max:1',
            'beta' => 'required|numeric|min:0|max:1',
        ]);
        
        $muestreoService = new MuestreoAceptacionService();
        
        $n =  $validated['n'];
        $c =  $validated['c'];

        // Convertir p a float explícitamente
        $alfa = (float) $validated['1-alpha'];
        $beta = (float) $validated['beta'];
        
        $distancias = array();
        $distancias["n"] = $n;
        $distancias["c"] = $c;
        $distancias["1-alpha"] = $alfa;
        $distancias["beta"] = $beta;

        $distanciamenor = -1;
        $menoraql = -1;
        $menorltpd = -1;
        $menoraqldist = -1;
        $menorltpddist = -1;
        $p = 0.01;
        //Obtencion de AQL y LTPD
        while($p < 1){
            $chequeo = array();
            $probabilidad = $muestreoService->binomDistAcum($n, $c, $p);

            if(($menoraql == -1) || ($menoraqldist > ($alfa - $probabilidad)**2)){
                    $menoraql = round($p,8);
                    $menoraqldist = ($alfa - $probabilidad)**2;
                }
            else if(($menorltpd == -1) || ($menorltpddist > ($beta - $probabilidad)**2)){
                    $menorltpd = round($p,8);
                    $menorltpddist = ($beta - $probabilidad)**2;
                }
            
            $p+=0.01;
        }

        //Se restauran los valores para ser similares a la funciona calcular, para mantener compatibilidad con la graficacion
        $AQT = $menoraql;
        $LTPD = $menorltpd;

        $distancias["AQL"] = $menoraql;
        $distancias["LTPD"] = $menorltpd;

        $probabilidadaqt = $muestreoService->binomDistAcum($n, $c, $AQT);
        $probabilidadltpd = $muestreoService->binomDistAcum($n, $c, $LTPD);
        $distancia = sqrt((($alfa - $probabilidadaqt) ** 2) + ($beta - $probabilidadltpd)**2);
        $distancias["distancia"] = $distancia;
        
        $datos = array();
        if($LTPD <= 0.1){
            $i = 0.01;
            while($i < 0.10){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], round($i, 4));
                $datos[] = $punto;
                $i+=0.01;
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $AQT);
            $datos[] = $punto;

        }else{
            $constante = round(($LTPD / 10  ), 8);
            $i =  round($constante, 4);
            while($i < $LTPD){
                $punto = array();
                $punto["p"] = round($i, 4);
                $punto["LTPD"] = false;
                $punto["AQT"] = false;
                $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], round($i, 4));
                $datos[] = $punto;
                $i+=round($constante, 4);
            }

            $punto = array();
            $punto["p"] = $LTPD;
            $punto["LTPD"] = true;
            $punto["AQT"] = false;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $LTPD);
            $datos[] = $punto;

            $punto = array();
            $punto["p"] = $AQT;
            $punto["LTPD"] = false;
            $punto["AQT"] = true;
            $punto["res"] = $muestreoService->binomDistAcum($distancias["n"], $distancias["c"], $AQT);
            $datos[] = $punto;
        }

        return response()->json([
            'distancia_menor' => $distancias,
            'grafica' => $datos
        ], 200);
    
    }
}
