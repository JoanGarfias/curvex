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

        Log::info('Muestro Aceptacion test received:', $validated);

        $muestreoService = new  MuestreoAceptacionService();
        $resultado = $muestreoService->binomDistAcum($validated['n'], $validated['k'], $validated['p']);
        //$resultado = $muestreoService->binomDist($validated['n'], 2);

        return response()->json([
            'n' => $validated['n'],
            'k' => $validated['k'],
            'p' => $validated['p'],
            'resultado' => $resultado,
            'message' => 'Muestro Aceptacion test received successfully.'
        ], 200);
    }
}
