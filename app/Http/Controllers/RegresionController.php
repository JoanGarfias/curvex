<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoordsRequest;
use App\Services\RegresionService;
use App\ValueObjects\Point;
use Illuminate\Http\Request;

class RegresionController extends Controller
{
    public function calcular(CoordsRequest $request)
    {
        
        $data = $request->validated();

        $data['values'] = array_map(function ($coordStr) {
            [$x, $y] = explode(',', $coordStr);
            return new Point((float)$x, (float)$y);
        }, explode(';', $data['values']));
        $regresionService = new RegresionService($data['values'], $data['method'] ?? 'lineal');
        $R2 = $regresionService->calculateR2();
        $result = [
            'R2' => $R2
        ];

        return response()->json([
            'message' => 'Cálculo de regresión realizado con éxito.',
            'data' => $result, // Aquí irían los resultados del cálculo
        ]);
    }
}
