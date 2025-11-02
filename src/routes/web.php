<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

// API ルートは api.php で定義

// 静的ファイル配信用のルート（SPAフォールバックより先に定義）
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);

    if (!file_exists($filePath)) {
        abort(404);
    }

    $mimeType = mime_content_type($filePath);

    return Response::file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');

// SPA フォールバックルート（最後に定義して、他のルートにマッチしなかった場合のみ適用）
Route::fallback(function () {
    return view('welcome');
});
