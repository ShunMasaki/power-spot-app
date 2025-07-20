<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpotController;
use App\Http\Controllers\Api\ReviewController;
use App\Models\Spot;

Route::middleware('api')->group(function () {
    Route::get('/spots', [SpotController::class, 'index']);
});

Route::get('/spots/{spot}/reviews', [ReviewController::class, 'index']);

Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store']);
// });

Route::get('/spots/{id}', function ($id) {
    return Spot::with(['spotBenefits.benefitType'])->findOrFail($id);
});
