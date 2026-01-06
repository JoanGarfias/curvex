<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordsRequest;
use Illuminate\Http\Request;

class RegresionController extends Controller
{
    public function calcular(CoordsRequest $request)
    {
        
        $data = $request->validated();

        

        return response()->json([
            'message' => 'Cálculo de regresión realizado con éxito.',
            // 'data' => $resultados, // Aquí irían los resultados del cálculo
        ]);
    }
}
