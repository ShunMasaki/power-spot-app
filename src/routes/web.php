<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;

Route::get('/', function () {
    return view('welcome');
});

// 静的ファイル配信用のルート
Route::get('/storage/{path}', function ($path) {
    \Log::info('Storage route accessed', ['path' => $path]);

    $filePath = storage_path('app/public/' . $path);
    \Log::info('File path', ['filePath' => $filePath, 'exists' => file_exists($filePath)]);

    if (!file_exists($filePath)) {
        \Log::warning('File not found', ['path' => $filePath]);
        abort(404);
    }

    $mimeType = mime_content_type($filePath);

    return Response::file($filePath, [
        'Content-Type' => $mimeType,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*');
