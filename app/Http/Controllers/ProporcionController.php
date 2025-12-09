<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProporcionRequest;
use App\Services\ProporcionService;
use Illuminate\Http\JsonResponse;

class ProporcionController extends Controller
{
    public function test(ProporcionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $x = (int) $data['x'];
        $n = (int) $data['n'];
        $p0 = (float) $data['p0'];
        $alpha = isset($data['alpha']) ? (float) $data['alpha'] : 0.05;
        $tail = $data['tail'] ?? 'two';
        $continuity = isset($data['continuity']) ? (bool) $data['continuity'] : true;

        $service = new ProporcionService();
        $result = $service->testProportion($x, $n, $p0, $alpha, $tail, $continuity);

        return response()->json($result, 200);
    }
}
