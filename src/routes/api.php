<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SpotController;
use App\Models\Spot;

Route::middleware('api')->group(function () {
    Route::get('/spots', [SpotController::class, 'index']);
});

Route::get('/spots/{id}', function ($id) {
    return Spot::with(['spotBenefits.benefitType'])->findOrFail($id);
});
