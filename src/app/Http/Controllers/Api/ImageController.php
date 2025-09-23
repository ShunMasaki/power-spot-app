<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spot;
use App\Models\OmikujiImage;
use App\Models\GoshuinImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
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
        ]);
    }

    /**
     * ユーザーの全画像一覧を取得（マイページ用）
     */
    public function getAllUserImages(): JsonResponse
    {
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

        // おみくじ画像を取得（スポット情報も含める）
        $omikujiImages = OmikujiImage::with('spot')
            ->where('user_id', $userId)
            ->get()
            ->map(function ($image) {
                $filename = basename($image->image_path);
                return [
                    'id' => $image->id,
                    'url' => url("/api/spots/{$image->spot_id}/images/omikuji/{$filename}"),
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
                $filename = basename($image->image_path);
                return [
                    'id' => $image->id,
                    'url' => url("/api/spots/{$image->spot_id}/images/goshuin/{$filename}"),
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

        return response()->json($allImages);
    }

    /**
     * ユーザーの画像一覧を取得
     */
    public function getUserImages($spotId): JsonResponse
    {
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

        $spot = Spot::findOrFail($spotId);

        // おみくじ画像を取得
        $omikujiImages = OmikujiImage::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->get()
            ->map(function ($image) use ($spotId) {
                $filename = basename($image->image_path);
                return [
                    'id' => $image->id,
                    'url' => url("/api/spots/{$spotId}/images/omikuji/{$filename}"),
                    'type' => 'omikuji',
                    'created_at' => $image->created_at
                ];
            });

        // 御朱印画像を取得
        $goshuinImages = GoshuinImage::where('user_id', $userId)
            ->where('spot_id', $spotId)
            ->get()
            ->map(function ($image) use ($spotId) {
                $filename = basename($image->image_path);
                return [
                    'id' => $image->id,
                    'url' => url("/api/spots/{$spotId}/images/goshuin/{$filename}"),
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
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

        $spot = Spot::findOrFail($spotId);

        // バリデーション
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB制限
            'type' => 'required|in:omikuji,goshuin'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = $request->input('type');

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
            // ファイルを保存
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs("images/{$type}", $filename, 'public');

            // データベースに保存
            if ($type === 'omikuji') {
                $imageRecord = OmikujiImage::create([
                    'user_id' => $userId,
                    'spot_id' => $spotId,
                    'image_path' => $path,
                    'original_filename' => $image->getClientOriginalName(),
                ]);
            } else {
                $imageRecord = GoshuinImage::create([
                    'user_id' => $userId,
                    'spot_id' => $spotId,
                    'image_path' => $path,
                    'original_filename' => $image->getClientOriginalName(),
                ]);
            }

            $filename = basename($path);
            return response()->json([
                'id' => $imageRecord->id,
                'url' => url("/api/spots/{$spotId}/images/{$type}/{$filename}"),
                'type' => $type,
                'created_at' => $imageRecord->created_at
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to upload image',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 画像を削除
     */
    public function destroy(Request $request, $spotId, $imageId): JsonResponse
    {
        // ダミー認証（フロントエンドの認証状態に依存）
        $userId = 1; // ダミーID

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
