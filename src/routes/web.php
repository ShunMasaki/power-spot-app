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

// デバッグエンドポイント（一時的に復活）
Route::get('/debug/assets', function () {
    $buildDir = public_path('build/assets');
    $files = [];

    if (is_dir($buildDir)) {
        $jsFiles = glob($buildDir . '/app-*.js');
        $cssFiles = glob($buildDir . '/app-*.css');

        $files = [
            'js_files' => array_map(function($file) {
                return basename($file);
            }, $jsFiles),
            'css_files' => array_map(function($file) {
                return basename($file);
            }, $cssFiles),
        ];
    }

    return response()->json($files, 200, [], JSON_PRETTY_PRINT);
});

// SPA フォールバックルート（最後に定義して、他のルートにマッチしなかった場合のみ適用）
Route::fallback(function () {
    return view('welcome');
});
