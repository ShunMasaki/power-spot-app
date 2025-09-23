<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpotController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ImageController;
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
    Route::get('/spots/{spot}/favorite/check', [FavoriteController::class, 'check']);
    Route::post('/spots/{spot}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/spots/{spot}/favorite', [FavoriteController::class, 'destroy']);

    // 画像関連のルート
    Route::get('/user/images', [ImageController::class, 'getAllUserImages']); // マイページ用
    Route::get('/spots/{spot}/user-images', [ImageController::class, 'getUserImages']);
    Route::post('/spots/{spot}/images', [ImageController::class, 'store']);
    Route::delete('/spots/{spot}/images/{image}', [ImageController::class, 'destroy']);
    Route::get('/spots/{spot}/images/{type}/{filename}', [ImageController::class, 'serveImage'])->where('filename', '.*');
});
