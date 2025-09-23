<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Spot;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * お気に入り登録
     */
    public function store(Request $request, $spotId): JsonResponse
    {
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

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
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

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
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

        $isFavorited = Favorite::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->exists();

        return response()->json(['is_favorited' => $isFavorited], 200);
    }
}
