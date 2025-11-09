<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CognitoHelper;
use App\Models\Visit;
use App\Models\Spot;
use App\Services\GooglePlacesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VisitController extends Controller
{
    protected $googlePlacesService;

    public function __construct(GooglePlacesService $googlePlacesService)
    {
        $this->googlePlacesService = $googlePlacesService;
    }

    /**
     * ユーザーの訪問履歴を取得（ページネーション付き）
     */
    public function index(Request $request): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $perPage = $request->input('per_page', 10);

        $visits = Visit::where('user_id', $userId)
            ->with(['spot.spotBenefits.benefitType'])
            ->orderBy('visited_at', 'desc')
            ->paginate($perPage);

        // サムネイル画像を取得
        $visits->getCollection()->transform(function ($visit) {
            $visit->thumbnail_image = $this->getThumbnailImage($visit->spot);
            return $visit;
        });

        return response()->json($visits);
    }

    /**
     * 訪問済みとしてマーク
     */
    public function store(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $spot = Spot::findOrFail($spotId);

        // 既に訪問済みかチェック
        $existingVisit = Visit::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->first();

        if ($existingVisit) {
            return response()->json(['message' => 'Already visited'], 200);
        }

        // 訪問履歴を作成
        Visit::create([
            'user_id' => $userId,
            'spot_id' => $spotId,
            'visited_at' => now(),
        ]);

        return response()->json(['message' => 'Marked as visited'], 201);
    }

    /**
     * 訪問済み解除
     */
    public function destroy(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $visit = Visit::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->first();

        if (!$visit) {
            return response()->json(['error' => 'Visit not found'], 404);
        }

        $visit->delete();

        return response()->json(['message' => 'Visit removed'], 200);
    }

    /**
     * 訪問状態をチェック
     */
    public function check(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['is_visited' => false], 200);
        }

        $isVisited = Visit::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->exists();

        return response()->json(['is_visited' => $isVisited], 200);
    }

    /**
     * Google Places APIからサムネイル画像を取得
     */
    private function getThumbnailImage($spot): ?string
    {
        if (!$spot) {
            return null;
        }

        return $this->googlePlacesService->getPlacePhotoUrl(
            $spot->name,
            $spot->latitude,
            $spot->longitude
        );
    }
}
