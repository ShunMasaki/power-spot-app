<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CognitoHelper;
use App\Models\User;
use App\Models\Visit;
use App\Models\Favorite;
use App\Models\OmikujiImage;
use App\Models\GoshuinImage;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * ユーザー統計情報を取得
     */
    public function getStats(Request $request): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::findOrFail($userId);

        // 各種カウントを取得
        $visitCount = Visit::where('user_id', $userId)->count();
        $favoriteCount = Favorite::where('user_id', $userId)->count();
        $reviewCount = Review::where('user_id', $userId)->count();

        $omikujiImageCount = OmikujiImage::where('user_id', $userId)->count();
        $goshuinImageCount = GoshuinImage::where('user_id', $userId)->count();
        $imageCount = $omikujiImageCount + $goshuinImageCount;

        return response()->json([
            'nickname' => $user->nickname ?? 'ユーザー',
            'visits' => $visitCount,
            'favorites' => $favoriteCount,
            'reviews' => $reviewCount,
            'images' => $imageCount,
        ]);
    }

    /**
     * ユーザープロフィールを更新
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::findOrFail($userId);

        $request->validate([
            'nickname' => 'nullable|string|max:50',
        ]);

        if ($request->has('nickname')) {
            $user->nickname = $request->input('nickname');
            $user->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
