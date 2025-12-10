<?php
/* Codigo corresponde con la tabla 2.3 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PruebaHipotesisNoVarianzaRequests as PruebaHipotesisNoVarianzaCaso012;
use App\Http\Requests\PruebaHipotesisNoVarianzaRequests2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use MathPHP\Probability\Distribution\Continuous\StudentT;
use MathPHP\Probability\Distribution\Continuous\StandardNormal;

class PruebaHipotesisNoVarianzaController extends Controller
{
    public function decidir(Request $request){
        if(isset($request["modo"])){
            $modo = (int)$request["modo"];

            switch($modo){
            case 0:
            case 1:
            case 2:
                return app()->call([$this, 'calcular']);
                break;
            case 3:
            case 4:
            case 5:
                return app()->call([$this, 'calcular2']);
                break;
            default:
                return response()->json(['error' => 'Modo no especificado o inválido'], 400);
                break;
            }
        }
    }

    public function calcular(PruebaHipotesisNoVarianzaCaso012 $request){
        //Casos 1,2 y 3 de la tabla 2.3
        $validated = $request->validated();
        $promedio = (float)$validated["promedio"];
        $u0 = (float)$validated["u0"];
        $varianza = (float)$validated["varianza"];
        $confiabilidad = (float)$validated["confiabilidad"];
        $cantidad = $validated["cantidad"];

        $t0 = ($promedio - $u0) / ($varianza/sqrt($cantidad));
        $distribution = new StudentT($cantidad - 1);

        $modo = $validated["modo"];
        switch($modo){
            case 0:
                //Criterio de rechazo abs(z0) > z(a/2)
                $ta = $distribution->inverse2Tails((1-$confiabilidad));

                if(abs($t0) > $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }
                break;
            case 1:
                //Criterio de rechazo z0 < -za
                $ta = -1 * $distribution->inverse2Tails((1-$confiabilidad)*2);

                if($t0 < $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            case 2:
                //Criterio de rechazo z0 > za
                $ta = $distribution->inverse2Tails((1-$confiabilidad)*2);

                if($t0 > $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            default:
                //Invalido. Pedir al usuario que elija un modo valido
                return response()->json(['error' => 'Modo no especificado o inválido'], 400);
                break;
        }
            

        return response()->json([
            't0' => $t0,
            'ta' => $ta,
            'veredicto' => $veredicto

        ], 200);
    
    }
    
    public function calcular2(PruebaHipotesisNoVarianzaRequests2 $request){
        //Casos 4 y 5 de la tabla 2.2
        $validated = $request->validated();
        if(isset($validated["varianzap"]) || (isset($validated["boolEsVarianzaUnica"]) && $validated["boolEsVarianzaUnica"] === true)){
            $promedio1 = (float)$validated["promedio1"];
            $cantidad1 = $validated["cantidad1"];
            $promedio2 = (float)$validated["promedio2"];
            $cantidad2 = $validated["cantidad2"];

            if(isset($validated["boolEsVarianzaUnica"]) && $validated["boolEsVarianzaUnica"] === true){
                $varianza1 = (float)$validated["varianza1"];
                $varianza2 = (float)$validated["varianza2"];

                $varianzap = (float)sqrt(((($cantidad1-1)*$varianza1) + (($cantidad2-1)*$varianza2))/($cantidad1 + $cantidad2 - 2));
            }else{
                $varianzap = (float)$validated["varianzap"];
            }

            $modovarp = true;

        }else{
            $promedio1 = (float)$validated["promedio1"];
            $varianza1 = (float)$validated["varianza1"];
            $cantidad1 = $validated["cantidad1"];
            $promedio2 = (float)$validated["promedio2"];
            $varianza2 = (float)$validated["varianza2"];
            $cantidad2 = $validated["cantidad2"];
            $modovarp = false;
        }
        

        $confiabilidad = (float)$validated["confiabilidad"];

        if($modovarp){
            $t0 = ($promedio1 - $promedio2) / ($varianzap * sqrt((1/$cantidad1) + (1/$cantidad2)));
            $v = $cantidad1 + $cantidad2 - 2;
            Log::info($varianzap);
        }else{
            $t0 = ($promedio1 - $promedio2) / sqrt(($varianza1**2/$cantidad1) + ($varianza2**2/$cantidad2));
            $v = ((($varianza1**2/$cantidad1) + ($varianza2**2/$cantidad2))**2)/((((($varianza1**2)/$cantidad1)**2)/($cantidad1-1)) + (((($varianza2**2)/$cantidad2)**2)/($cantidad2-1)));
        }

        $distribution = new StudentT($v);

        $modo = $validated["modo"];
        switch($modo){
            case 3:
                //Criterio de rechazo abs(z0) > z(a/2)
                $ta = $distribution->inverse2Tails((1-$confiabilidad));

                if(abs($t0) > $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }
                break;
            case 4:
                //Criterio de rechazo z0 < -za
                $ta = -1 * $distribution->inverse2Tails((1-$confiabilidad)*2);
                if($t0 < $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            case 5:
                //Criterio de rechazo z0 > za
                $ta = $distribution->inverse2Tails((1-$confiabilidad)*2);

                if($t0 > $ta){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            default:
                //Invalido. Pedir al usuario que elija un modo valido
                return response()->json(['error' => 'Modo no especificado o inválido'], 400);
                break;
        }
            

        return response()->json([
            't0' => $t0,
            'ta' => $ta,
            'veredicto' => $veredicto

        ], 200);
    
    }
}
