<?php
/* Codigo corresponde con la tabla 2.2 */
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PruebaHipotesisVarianzaRequests as PruebaHipotesisVarianzaCaso012;
use App\Http\Requests\PruebaHipotesisVarianzaRequests2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use MathPHP\Probability\Distribution\Continuous\StudentT;
use MathPHP\Probability\Distribution\Continuous\StandardNormal;

class PruebaHipotesisVarianzaController extends Controller
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

    public function calcular(PruebaHipotesisVarianzaCaso012 $request){
        //Casos 1,2 y 3 de la tabla 2.2
        $validated = $request->validated();
        $promedio = (float)$validated["promedio"];
        $u0 = (float)$validated["u0"];
        $desviacion = (float)$validated["desviacion"];
        $confiabilidad = (float)$validated["confiabilidad"];
        $cantidad = $validated["cantidad"];

        $z0 = ($promedio - $u0) / ($desviacion/sqrt($cantidad));
        $distribution = new StandardNormal();

        $modo = $validated["modo"];
        switch($modo){
            case 0:
                //Criterio de rechazo abs(z0) > z(a/2)
                $za = -1 * $distribution->inverse((1-$confiabilidad)/2);

                if(abs($z0) > $za){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }
                break;
            case 1:
                //Criterio de rechazo z0 < -za
                $za = $distribution->inverse(1-$confiabilidad);
                

                if($z0 < $za){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            case 2:
                //Criterio de rechazo z0 > za
                $za = -1 * $distribution->inverse(1-$confiabilidad);

                if($z0 > $za){
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
            'z0' => $z0,
            'za' => $za,
            'veredicto' => $veredicto

        ], 200);
    
    }
    
    public function calcular2(PruebaHipotesisVarianzaRequests2 $request){
        //Casos 4,5 y 6 de la tabla 2.2
        $validated = $request->validated();
        $promedio1 = (float)$validated["promedio1"];
        $desviacion1 = (float)$validated["desviacion1"];
        $cantidad1 = $validated["cantidad1"];
        $promedio2 = (float)$validated["promedio2"];
        $desviacion2 = (float)$validated["desviacion2"];
        $cantidad2 = $validated["cantidad2"];

        $confiabilidad = (float)$validated["confiabilidad"];

        $z0 = ($promedio1 - $promedio2) / sqrt(($desviacion1**2/$cantidad1) + ($desviacion2**2/$cantidad2));
        $distribution = new StandardNormal();

        $modo = $validated["modo"];
        switch($modo){
            case 3:
                //Criterio de rechazo abs(z0) > z(a/2)
                $za = -1 * $distribution->inverse((1-$confiabilidad)/2);

                if(abs($z0) > $za){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }
                break;
            case 4:
                //Criterio de rechazo z0 < -za
                $za = $distribution->inverse(1-$confiabilidad);
                if($z0 < $za){
                    $veredicto = "Se rechaza la hipotesis nula.";
                }else{
                    $veredicto = "Se aplica la hipotesis nula.";
                }

                break;
            case 5:
                //Criterio de rechazo z0 > za
                $za = -1 * $distribution->inverse(1-$confiabilidad);

                if($z0 > $za){
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
            'z0' => $z0,
            'za' => $za,
            'veredicto' => $veredicto

        ], 200);
    
    }
}
