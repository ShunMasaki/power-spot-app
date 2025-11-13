<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpotController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\UserController;
use App\Models\Spot;

Route::middleware('api')->group(function () {
    Route::get('/spots', [SpotController::class, 'index']);
    Route::get('/spots/{spot}', [SpotController::class, 'show']);
    Route::get('/spots/{spot}/reviews', [ReviewController::class, 'index']);
    Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store']);
    Route::get('/spots/{spot}/google-places', [SpotController::class, 'googlePlaces']);
    Route::get('/benefit-types', function () {
        return \App\Models\BenefitType::orderBy('sort_order')->get();
    });

    // お気に入り関連のルート
    Route::get('/user/favorites', [FavoriteController::class, 'index']); // マイページ用
    Route::get('/spots/{spot}/favorite/check', [FavoriteController::class, 'check']);
    Route::post('/spots/{spot}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/spots/{spot}/favorite', [FavoriteController::class, 'destroy']);

    // 訪問履歴関連のルート
    Route::get('/user/visits', [VisitController::class, 'index']); // マイページ用
    Route::get('/spots/{spot}/visit/check', [VisitController::class, 'check']);
    Route::post('/spots/{spot}/visit', [VisitController::class, 'store']);
    Route::delete('/spots/{spot}/visit', [VisitController::class, 'destroy']);

    // レビュー関連のルート
    Route::get('/user/reviews', [ReviewController::class, 'getUserReviews']); // マイページ用

    // 画像関連のルート
    Route::get('/user/images', [ImageController::class, 'getAllUserImages']); // マイページ用
    Route::get('/spots/{spot}/user-images', [ImageController::class, 'getUserImages']);
    Route::post('/spots/{spot}/images', [ImageController::class, 'store']); // 旧方式（後方互換性のため残す）
    Route::post('/spots/{spot}/images/presigned-url', [ImageController::class, 'getPresignedUrl']); // Presigned URL取得
    Route::post('/spots/{spot}/images/from-s3', [ImageController::class, 'storeFromS3']); // S3直接アップロード後のメタデータ保存
    Route::delete('/spots/{spot}/images/{image}', [ImageController::class, 'destroy']);
    Route::get('/spots/{spot}/images/{type}/{filename}', [ImageController::class, 'serveImage'])->where('filename', '.*');

    // ユーザー関連のルート
    Route::get('/user/stats', [UserController::class, 'getStats']); // マイページ用
    Route::put('/user/profile', [UserController::class, 'updateProfile']);

    // 一時的なレビュー削除エンドポイント（削除後は削除してください）
    Route::delete('/admin/reviews/{review}', function ($reviewId) {
        // セキュリティキーをチェック（本番環境では環境変数から取得）
        $key = request()->header('X-Admin-Key');
        if ($key !== env('ADMIN_DELETE_KEY', 'temp_delete_key_2024')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $review = \App\Models\Review::find($reviewId);
        if (!$review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $review->delete();
        return response()->json(['message' => 'Review deleted successfully'], 200);
    });
});
