<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CognitoHelper;
use App\Models\Spot;
use App\Models\OmikujiImage;
use App\Models\GoshuinImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ImageController extends Controller
{
    /**
     * S3への直接アップロード用Presigned URLを生成
     */
    public function getPresignedUrl(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:omikuji,goshuin',
            'filename' => 'required|string',
            'content_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $type = $request->input('type');
        $filename = $request->input('filename');
        $contentType = $request->input('content_type');

        // アップロード制限をチェック（各タイプ最大2枚）
        if ($type === 'omikuji') {
            $existingCount = OmikujiImage::where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->count();

            if ($existingCount >= 2) {
                return response()->json([
                    'error' => 'Maximum 2 omikuji images allowed per spot'
                ], 422);
            }
        } elseif ($type === 'goshuin') {
            $existingCount = GoshuinImage::where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->count();

            if ($existingCount >= 2) {
                return response()->json([
                    'error' => 'Maximum 2 goshuin images allowed per spot'
                ], 422);
            }
        }

        // S3クライアントを作成
        $s3Config = config('filesystems.disks.s3');
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $s3Config['region'],
            'credentials' => [
                'key' => $s3Config['key'],
                'secret' => $s3Config['secret'],
            ],
        ]);

        // S3パスを生成（ユニークなファイル名）
        $s3Key = "images/{$type}/" . time() . '_' . uniqid() . '_' . $filename;

        // Presigned URLを生成（15分有効）
        $cmd = $s3Client->getCommand('PutObject', [
            'Bucket' => $s3Config['bucket'],
            'Key' => $s3Key,
            'ContentType' => $contentType,
        ]);

        $presignedRequest = $s3Client->createPresignedRequest($cmd, '+15 minutes');
        $presignedUrl = (string) $presignedRequest->getUri();

        return response()->json([
            'presigned_url' => $presignedUrl,
            's3_key' => $s3Key,
            'expires_in' => 900, // 15分
        ]);
    }

    /**
     * S3に直接アップロードされた画像のメタデータを保存
     */
    public function storeFromS3(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:omikuji,goshuin',
            's3_key' => 'required|string',
            'original_filename' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $type = $request->input('type');
        $s3Key = $request->input('s3_key');
        $originalFilename = $request->input('original_filename', '');

        // S3にファイルが存在するか確認
        $s3Disk = Storage::disk('s3');
        if (!$s3Disk->exists($s3Key)) {
            return response()->json(['error' => 'File not found in S3'], 404);
        }

        // データベースに保存
        if ($type === 'omikuji') {
            $imageRecord = OmikujiImage::create([
                'user_id' => $userId,
                'spot_id' => $spotId,
                'image_path' => $s3Key,
                'original_filename' => $originalFilename,
            ]);
        } else {
            $imageRecord = GoshuinImage::create([
                'user_id' => $userId,
                'spot_id' => $spotId,
                'image_path' => $s3Key,
                'original_filename' => $originalFilename,
            ]);
        }

        // S3の公開URLを返す（HTTPSを強制）
        $publicUrl = Storage::disk('s3')->url($s3Key);
        // HTTPの場合はHTTPSに変換
        if (strpos($publicUrl, 'http://') === 0) {
            $publicUrl = str_replace('http://', 'https://', $publicUrl);
        }

        return response()->json([
            'id' => $imageRecord->id,
            'url' => $publicUrl,
            'type' => $type,
            'created_at' => $imageRecord->created_at
        ], 201);
    }

    /**
     * 画像ファイルを配信
     */
    public function serveImage($spotId, $type, $filename)
    {
        $filePath = storage_path("app/public/images/{$type}/{$filename}");

        if (!file_exists($filePath)) {
            abort(404);
        }

        $mimeType = mime_content_type($filePath);

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
            'Access-Control-Allow-Origin' => '*', // CORS対応（スマホからのリクエストで必要）
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ]);
    }

    /**
     * ユーザーの全画像一覧を取得（マイページ用・ページネーション付き）
     */
    public function getAllUserImages(Request $request): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);

        // ベースURLを取得（CloudFront経由でもHTTPSを確実に返す）
        // CloudFront経由の場合は必ずHTTPSを使用
        $scheme = $request->header('X-Forwarded-Proto', $request->getScheme());
        if ($request->header('X-Forwarded-Proto') === 'https' ||
            strpos($request->header('Host', ''), 'cloudfront.net') !== false ||
            strpos($request->header('X-Forwarded-Host', ''), 'cloudfront.net') !== false) {
            $scheme = 'https';
        }

        // CloudFront経由の場合、X-Forwarded-HostまたはHostヘッダーから取得
        // Originヘッダーも確認（スマホからのリクエストで有用）
        $host = $request->header('X-Forwarded-Host')
            ?: $request->header('Host')
            ?: ($request->header('Origin') ? parse_url($request->header('Origin'), PHP_URL_HOST) : null)
            ?: $request->getHost();
        $baseUrl = $scheme . '://' . $host;

        // おみくじ画像を取得（スポット情報も含める）
        $omikujiImages = OmikujiImage::with('spot')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($image) {
                // S3の公開URLを生成（HTTPSを強制）
                $url = Storage::disk('s3')->url($image->image_path);
                // HTTPの場合はHTTPSに変換
                if (strpos($url, 'http://') === 0) {
                    $url = str_replace('http://', 'https://', $url);
                }
                return [
                    'id' => $image->id,
                    'url' => $url,
                    'type' => 'omikuji',
                    'spot_id' => $image->spot_id,
                    'spot_name' => $image->spot->name,
                    'spot_address' => $image->spot->address,
                    'taken_at' => $image->taken_at,
                    'created_at' => $image->created_at
                ];
            });

        // 御朱印画像を取得（スポット情報も含める）
        $goshuinImages = GoshuinImage::with('spot')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($image) {
                // S3の公開URLを生成（HTTPSを強制）
                $url = Storage::disk('s3')->url($image->image_path);
                // HTTPの場合はHTTPSに変換
                if (strpos($url, 'http://') === 0) {
                    $url = str_replace('http://', 'https://', $url);
                }
                return [
                    'id' => $image->id,
                    'url' => $url,
                    'type' => 'goshuin',
                    'spot_id' => $image->spot_id,
                    'spot_name' => $image->spot->name,
                    'spot_address' => $image->spot->address,
                    'taken_at' => $image->taken_at,
                    'created_at' => $image->created_at
                ];
            });

        // 両方の画像を結合して作成日時でソート
        $allImages = $omikujiImages->concat($goshuinImages)
            ->sortByDesc('created_at')
            ->values();

        // 手動でページネーション
        $total = $allImages->count();
        $offset = ($page - 1) * $perPage;
        $paginatedImages = $allImages->slice($offset, $perPage)->values();

        return response()->json([
            'current_page' => (int)$page,
            'data' => $paginatedImages,
            'per_page' => (int)$perPage,
            'total' => $total,
            'last_page' => (int)ceil($total / $perPage),
        ]);
    }

    /**
     * ユーザーの画像一覧を取得
     */
    public function getUserImages(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $spot = Spot::findOrFail($spotId);

        // ベースURLを取得（CloudFront経由でもHTTPSを確実に返す）
        // CloudFront経由の場合は必ずHTTPSを使用
        $scheme = $request->header('X-Forwarded-Proto', $request->getScheme());
        if ($request->header('X-Forwarded-Proto') === 'https' ||
            strpos($request->header('Host', ''), 'cloudfront.net') !== false ||
            strpos($request->header('X-Forwarded-Host', ''), 'cloudfront.net') !== false) {
            $scheme = 'https';
        }

        // CloudFront経由の場合、X-Forwarded-HostまたはHostヘッダーから取得
        // Originヘッダーも確認（スマホからのリクエストで有用）
        $host = $request->header('X-Forwarded-Host')
            ?: $request->header('Host')
            ?: ($request->header('Origin') ? parse_url($request->header('Origin'), PHP_URL_HOST) : null)
            ?: $request->getHost();
        $baseUrl = $scheme . '://' . $host;

        // おみくじ画像を取得
        $omikujiImages = OmikujiImage::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->get()
            ->map(function ($image) {
                // S3の公開URLを生成（HTTPSを強制）
                $url = Storage::disk('s3')->url($image->image_path);
                // HTTPの場合はHTTPSに変換
                if (strpos($url, 'http://') === 0) {
                    $url = str_replace('http://', 'https://', $url);
                }
                return [
                    'id' => $image->id,
                    'url' => $url,
                    'type' => 'omikuji',
                    'created_at' => $image->created_at
                ];
            });

        // 御朱印画像を取得
        $goshuinImages = GoshuinImage::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->get()
            ->map(function ($image) {
                // S3の公開URLを生成（HTTPSを強制）
                $url = Storage::disk('s3')->url($image->image_path);
                // HTTPの場合はHTTPSに変換
                if (strpos($url, 'http://') === 0) {
                    $url = str_replace('http://', 'https://', $url);
                }
                return [
                    'id' => $image->id,
                    'url' => $url,
                    'type' => 'goshuin',
                    'created_at' => $image->created_at
                ];
            });

        // 両方の画像を結合
        $allImages = $omikujiImages->concat($goshuinImages);

        return response()->json($allImages);
    }

    /**
     * 画像をアップロード
     */
    public function store(Request $request, $spotId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $spot = Spot::findOrFail($spotId);

        $type = $request->input('type');

        // 画像ファイルを取得（imageまたはimages[]から）
        $imageFiles = [];
        if ($request->hasFile('image')) {
            $imageFiles = [$request->file('image')];
        } elseif ($request->hasFile('images')) {
            $imageFiles = $request->file('images');
        } elseif (isset($request->allFiles()['image'])) {
            $file = $request->allFiles()['image'];
            if (is_array($file)) {
                $file = $file[0];
            }
            if ($file && $file->isValid() && $file->getSize() > 0) {
                $imageFiles = [$file];
            } else {
                try {
                    $retryFile = $request->file('image');
                    if ($retryFile && $retryFile->isValid()) {
                        $imageFiles = [$retryFile];
                    }
                } catch (\Exception $e) {
                    // エラーは無視
                }
            }
        } elseif (isset($request->allFiles()['images'])) {
            $imageFiles = $request->allFiles()['images'];
        }

        // $_FILESを直接確認（Laravelが認識できない場合のフォールバック）
        if (empty($imageFiles) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpFile = $_FILES['image']['tmp_name'];
            if ($tmpFile && file_exists($tmpFile) && is_uploaded_file($tmpFile)) {
                $originalName = $_FILES['image']['name'];
                $mimeType = $_FILES['image']['type'] ?: mime_content_type($tmpFile);

                $uploadedFile = new \Illuminate\Http\UploadedFile(
                    $tmpFile,
                    $originalName,
                    $mimeType,
                    null,
                    false
                );

                if ($uploadedFile->isValid()) {
                    $imageFiles = [$uploadedFile];
                }
            }
        }

        if (empty($imageFiles)) {
            return response()->json([
                'error' => '画像ファイルが正しくアップロードされませんでした。',
                'message' => 'ファイルが無効です。',
            ], 422);
        }

        // ファイルの拡張子をチェック（MIMEタイプが空でも拡張子で判定）
        foreach ($imageFiles as $index => $file) {
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            // MIMEタイプを安全に取得
            $mimeType = '';
            try {
                $mimeType = $file->getMimeType();
            } catch (\Exception $e) {
                // MIMEタイプの取得に失敗した場合、ファイルの内容から判定
                try {
                    $mimeType = mime_content_type($file->getRealPath());
                } catch (\Exception $e2) {
                    \Log::warning('Could not determine MIME type', [
                        'error' => $e2->getMessage(),
                    ]);
                }
            }

            // ファイル名が空の場合、MIMEタイプから拡張子を推測
            if (empty($extension) || empty($originalName)) {
                $mimeToExt = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'image/heic' => 'heic',
                    'image/heif' => 'heif',
                ];

                if (isset($mimeToExt[$mimeType])) {
                    $extension = $mimeToExt[$mimeType];
                } else {
                    $extension = 'jpg';
                }
            }

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
            if (!in_array($extension, $allowedExtensions)) {
                \Log::error('Invalid file extension', [
                    'extension' => $extension,
                    'filename' => $originalName,
                    'mime_type' => $mimeType,
                ]);
                return response()->json([
                    'error' => 'Invalid file type',
                    'message' => '許可されていないファイル形式です: ' . $extension
                ], 422);
            }
        }

        // バリデーション（スマホ対応：imageまたはimages[]を許可、iPhoneのHEIC/HEIFも許可）
        // MIMEタイプが空でも拡張子で判定できるように、カスタムバリデーションを使用
        $validator = Validator::make($request->all(), [
            'image' => [
                'sometimes',
                function ($attribute, $value, $fail) {
                    if (!$value || !$value->isValid()) {
                        $fail('画像ファイルが無効です');
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    // 拡張子が空の場合はMIMEタイプから推測
                    if (empty($extension)) {
                        // MIMEタイプを安全に取得
                        $mimeType = '';
                        try {
                            $mimeType = $value->getMimeType();
                        } catch (\Exception $e) {
                            // MIMEタイプの取得に失敗した場合、ファイルの内容から判定
                            try {
                                $mimeType = mime_content_type($value->getRealPath());
                            } catch (\Exception $e2) {
                                // MIMEタイプが取得できない場合はスキップ
                            }
                        }
                        $mimeToExt = [
                            'image/jpeg' => 'jpg',
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/webp' => 'webp',
                            'image/heic' => 'heic',
                            'image/heif' => 'heif',
                        ];
                        $extension = $mimeToExt[$mimeType] ?? 'jpg';
                    }
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('許可されていないファイル形式です: ' . $extension);
                        return;
                    }
                    if ($value->getSize() > 5 * 1024 * 1024) {
                        $fail('ファイルサイズは5MB以下にしてください');
                        return;
                    }
                }
            ],
            'images' => 'sometimes|array',
            'images.*' => [
                function ($attribute, $value, $fail) {
                    if (!$value || !$value->isValid()) {
                        $fail('画像ファイルが無効です');
                        return;
                    }
                    $extension = strtolower($value->getClientOriginalExtension());
                    // 拡張子が空の場合はMIMEタイプから推測
                    if (empty($extension)) {
                        // MIMEタイプを安全に取得
                        $mimeType = '';
                        try {
                            $mimeType = $value->getMimeType();
                        } catch (\Exception $e) {
                            // MIMEタイプの取得に失敗した場合、ファイルの内容から判定
                            try {
                                $mimeType = mime_content_type($value->getRealPath());
                            } catch (\Exception $e2) {
                                // MIMEタイプが取得できない場合はスキップ
                            }
                        }
                        $mimeToExt = [
                            'image/jpeg' => 'jpg',
                            'image/jpg' => 'jpg',
                            'image/png' => 'png',
                            'image/gif' => 'gif',
                            'image/webp' => 'webp',
                            'image/heic' => 'heic',
                            'image/heif' => 'heif',
                        ];
                        $extension = $mimeToExt[$mimeType] ?? 'jpg';
                    }
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'heic', 'heif'];
                    if (!in_array($extension, $allowedExtensions)) {
                        $fail('許可されていないファイル形式です: ' . $extension);
                        return;
                    }
                    if ($value->getSize() > 5 * 1024 * 1024) {
                        $fail('ファイルサイズは5MB以下にしてください');
                        return;
                    }
                }
            ],
            'type' => 'required|in:omikuji,goshuin'
        ]);

        if ($validator->fails()) {
            \Log::error('Image upload validation failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->except(['image', 'images']),
                'image_files_info' => array_map(function($file) {
                    return [
                        'name' => $file->getClientOriginalName(),
                        'mime' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'extension' => $file->getClientOriginalExtension(),
                    ];
                }, $imageFiles),
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // アップロード制限をチェック（各タイプ最大2枚）
        if ($type === 'omikuji') {
            $existingCount = OmikujiImage::where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->count();

            if ($existingCount >= 2) {
                return response()->json([
                    'error' => 'Maximum 2 omikuji images allowed per spot'
                ], 422);
            }
        } elseif ($type === 'goshuin') {
            $existingCount = GoshuinImage::where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->count();

            if ($existingCount >= 2) {
                return response()->json([
                    'error' => 'Maximum 2 goshuin images allowed per spot'
                ], 422);
            }
        }

        try {
            // ファイルを保存（$imageFilesから取得）
            $image = $imageFiles[0];
            if (!$image || !$image->isValid()) {
                \Log::error('Invalid image file', [
                    'has_image' => $request->hasFile('image'),
                    'has_images' => $request->hasFile('images'),
                    'image_files_count' => count($imageFiles),
                ]);
                return response()->json([
                    'error' => 'Invalid image file',
                    'message' => 'The uploaded file is not valid'
                ], 422);
            }

            // MIMEタイプを安全に取得（HEIC判定のため）
            $mimeType = '';
            try {
                $mimeType = $image->getMimeType();
            } catch (\Exception $e) {
                // MIMEタイプの取得に失敗した場合、ファイルの内容から判定
                try {
                    $mimeType = mime_content_type($image->getRealPath());
                } catch (\Exception $e2) {
                    // MIMEタイプの取得に失敗した場合はデフォルトを使用
                }
            }

            // 拡張子を取得（空の場合はMIMEタイプから推測）
            $extension = strtolower($image->getClientOriginalExtension());
            if (empty($extension)) {
                $mimeToExt = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                    'image/heic' => 'heic',
                    'image/heif' => 'heif',
                ];
                $extension = $mimeToExt[$mimeType] ?? 'jpg';
            }

            $filename = time() . '_' . uniqid() . '.' . $extension;
            $directory = "images/{$type}";

            // S3に保存（公開読み取り）
            $mimeTypeForS3 = 'image/jpeg';
            try {
                $mimeTypeForS3 = $image->getMimeType();
            } catch (\Exception $e) {
                try {
                    $mimeTypeForS3 = mime_content_type($image->getRealPath());
                } catch (\Exception $e2) {
                    $mimeTypeForS3 = 'image/jpeg';
                }
            }

            try {
                // AWS SDKを直接使用してより詳細なエラーを取得
                $fileContents = file_get_contents($image->getRealPath());
                $fullPath = $directory . '/' . $filename;

                // Storageファサードを使用
                $s3Disk = Storage::disk('s3');

                // バケットが存在するか確認
                try {
                    $s3Path = $s3Disk->put($fullPath, $fileContents);

                    // putがtrueを返した場合は、fullPathを使用
                    if ($s3Path === true) {
                        $s3Path = $fullPath;
                    }

                    if (!$s3Path || $s3Path === false) {
                        // putがfalseを返した場合、AWS SDKを直接使用してテスト
                        \Log::error('S3 upload failed - put returned false, trying direct SDK', [
                            'full_path' => $fullPath,
                            'file_size' => strlen($fileContents),
                            'bucket' => config('filesystems.disks.s3.bucket'),
                            'region' => config('filesystems.disks.s3.region'),
                        ]);

                        // AWS SDKを直接使用
                        $s3Config = config('filesystems.disks.s3');
                        $s3Client = new S3Client([
                            'version' => 'latest',
                            'region' => $s3Config['region'],
                            'credentials' => [
                                'key' => $s3Config['key'],
                                'secret' => $s3Config['secret'],
                            ],
                        ]);

                        try {
                            $s3Client->putObject([
                                'Bucket' => $s3Config['bucket'],
                                'Key' => $fullPath,
                                'Body' => $fileContents,
                                'ContentType' => $mimeTypeForS3,
                            ]);
                            $s3Path = $fullPath;
                        } catch (AwsException $s3e) {
                            \Log::error('Direct S3 upload failed', [
                                'error' => $s3e->getMessage(),
                                'code' => $s3e->getAwsErrorCode(),
                                'request_id' => $s3e->getAwsRequestId(),
                            ]);
                            return response()->json([
                                'error' => 'Failed to upload image to S3',
                                'message' => $s3e->getAwsErrorCode() . ': ' . $s3e->getMessage()
                            ], 500);
                        }
                    }
                } catch (AwsException $s3e) {
                    \Log::error('S3 exception during upload', [
                        'error' => $s3e->getMessage(),
                        'code' => $s3e->getAwsErrorCode(),
                        'request_id' => $s3e->getAwsRequestId(),
                    ]);
                    return response()->json([
                        'error' => 'Failed to upload image to S3',
                        'message' => $s3e->getAwsErrorCode() . ': ' . $s3e->getMessage()
                    ], 500);
                }
            } catch (\Exception $e) {
                \Log::error('S3 upload exception', [
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'error' => 'Failed to upload image to S3',
                    'message' => $e->getMessage()
                ], 500);
            }

            // データベースに保存
            if ($type === 'omikuji') {
                $imageRecord = OmikujiImage::create([
                    'user_id' => $userId,
                    'spot_id' => $spotId,
                    'image_path' => $s3Path,
                    'original_filename' => $image->getClientOriginalName(),
                ]);
            } else {
                $imageRecord = GoshuinImage::create([
                    'user_id' => $userId,
                    'spot_id' => $spotId,
                    'image_path' => $s3Path,
                    'original_filename' => $image->getClientOriginalName(),
                ]);
            }

            // S3の公開URLを返す（HTTPSを強制）
            $publicUrl = Storage::disk('s3')->url($s3Path);
            // HTTPの場合はHTTPSに変換
            if (strpos($publicUrl, 'http://') === 0) {
                $publicUrl = str_replace('http://', 'https://', $publicUrl);
            }

            return response()->json([
                'id' => $imageRecord->id,
                'url' => $publicUrl,
                'type' => $type,
                'created_at' => $imageRecord->created_at
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Image upload error', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['image', 'images']),
                'has_image' => $request->hasFile('image'),
                'has_images' => $request->hasFile('images'),
            ]);
            return response()->json([
                'error' => 'Failed to upload image',
                'message' => $e->getMessage(),
                'debug' => app()->environment('local') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * 画像を削除
     */
    public function destroy(Request $request, $spotId, $imageId): JsonResponse
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // おみくじ画像から検索
            $omikujiImage = OmikujiImage::where('id', $imageId)
                ->where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->first();

            if ($omikujiImage) {
                // ファイルを削除
                if (Storage::disk('public')->exists($omikujiImage->image_path)) {
                    Storage::disk('public')->delete($omikujiImage->image_path);
                }

                $omikujiImage->delete();

                return response()->json(['message' => 'Omikuji image deleted successfully']);
            }

            // 御朱印画像から検索
            $goshuinImage = GoshuinImage::where('id', $imageId)
                ->where('user_id', $userId)
                ->where('spot_id', $spotId)
                ->first();

            if ($goshuinImage) {
                // ファイルを削除
                if (Storage::disk('public')->exists($goshuinImage->image_path)) {
                    Storage::disk('public')->delete($goshuinImage->image_path);
                }

                $goshuinImage->delete();

                return response()->json(['message' => 'Goshuin image deleted successfully']);
            }

            return response()->json(['error' => 'Image not found'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete image',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
