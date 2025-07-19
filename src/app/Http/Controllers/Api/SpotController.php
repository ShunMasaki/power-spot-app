<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spot;
use Illuminate\Http\JsonResponse;

class SpotController extends Controller
{
    public function index(): JsonResponse
    {
        // 緯度・経度含めてスポット一覧を返す
        $spots = Spot::select('id', 'name', 'latitude', 'longitude')->get();
        return response()->json($spots);
    }
    public function show($id)
    {
        $spot = Spot::with('spotBenefits.benefitType')->findOrFail($id);
        return response()->json($spot);
    }
}
