<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CognitoHelper;
use App\Models\Favorite;
use App\Models\Spot;
use App\Services\GooglePlacesService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    protected $googlePlacesService;

    public function __construct(GooglePlacesService $googlePlacesService)
    {
        $this->googlePlacesService = $googlePlacesService;
    }

    /**
     * ユーザーのお気に入り一覧を取得（ページネーション付き）
     */
    public function index(Request $request): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $perPage = $request->input('per_page', 10);

        $favorites = Favorite::where('user_id', $userId)
            ->with(['spot.spotBenefits.benefitType'])
            ->latest()
            ->paginate($perPage);

        // サムネイル画像を取得
        $favorites->getCollection()->transform(function ($favorite) {
            $favorite->thumbnail_image = $this->getThumbnailImage($favorite->spot);

            // ご利益のラベルを取得
            if ($favorite->spot && $favorite->spot->spotBenefits) {
                $favorite->benefits = $favorite->spot->spotBenefits->map(function ($spotBenefit) {
                    return $spotBenefit->benefitType->label ?? $spotBenefit->benefitType->name;
                })->toArray();
            } else {
                $favorite->benefits = [];
            }

            return $favorite;
        });

        return response()->json($favorites);
    }

    /**
     * お気に入り登録
     */
    public function store(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $spot = Spot::findOrFail($spotId);

        // 既にお気に入りに登録されているかチェック
        $existingFavorite = Favorite::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->first();

        if ($existingFavorite) {
            return response()->json(['message' => 'Already favorited'], 200);
        }

        // お気に入り登録
        Favorite::create([
            'user_id' => $userId,
            'spot_id' => $spotId,
        ]);

        return response()->json(['message' => 'Added to favorites'], 201);
    }

    /**
     * お気に入り削除
     */
    public function destroy(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $favorite = Favorite::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->first();

        if (!$favorite) {
            return response()->json(['error' => 'Favorite not found'], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Removed from favorites'], 200);
    }

    /**
     * お気に入り状態をチェック
     */
    public function check(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['is_favorited' => false], 200);
        }

        $isFavorited = Favorite::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->exists();

        return response()->json(['is_favorited' => $isFavorited], 200);
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
