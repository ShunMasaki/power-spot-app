<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spot;
use App\Services\GooglePlacesService;
use Illuminate\Http\JsonResponse;

class SpotController extends Controller
{
    public function index(): JsonResponse
    {
        // 緯度・経度含めてスポット一覧を返す（ご利益情報も含める）
        $spots = Spot::with(['spotBenefits.benefitType'])
            ->select('id', 'name', 'latitude', 'longitude')
            ->get();
        return response()->json($spots);
    }
    public function show($id)
    {
        $spot = Spot::with(['spotBenefits.benefitType', 'reviews'])->findOrFail($id);

        // 平均評価を計算
        $averageRating = $spot->reviews->avg('rating');
        $spot->average_rating = $averageRating ? round($averageRating, 1) : null;

        return response()->json($spot);
    }

    public function googlePlaces($id)
    {
        $spot = Spot::findOrFail($id);

        try {
            // Google Places APIから画像と営業時間を取得
            $googleData = $this->fetchGooglePlacesData($spot);

            return response()->json($googleData);
        } catch (\Exception $e) {
            // エラーが発生した場合は空のデータを返す
            return response()->json([
                'images' => [],
                'businessHours' => []
            ]);
        }
    }

    private function fetchGooglePlacesData($spot)
    {
        $googlePlacesService = new GooglePlacesService();

        try {
            // スポット名からPlace IDを検索
            $placeId = $googlePlacesService->findPlaceId(
                $spot->name,
                $spot->latitude,
                $spot->longitude
            );

            if (!$placeId) {
                // Place IDが見つからない場合はダミーデータを返す
                return $this->getFallbackData();
            }

                    // 実際のGoogle Places APIからデータを取得
                    $images = $googlePlacesService->getPlacePhotos($placeId, 5);
                    $businessHours = $googlePlacesService->getBusinessHours($placeId);
                    $types = $googlePlacesService->getPlaceTypes($placeId);

                    return [
                        'images' => $images,
                        'businessHours' => $businessHours,
                        'types' => $types
                    ];

        } catch (\Exception $e) {
            // エラーが発生した場合はダミーデータを返す
            \Log::error('Google Places API Error: ' . $e->getMessage());
            return $this->getFallbackData();
        }
    }

    private function getFallbackData()
    {
        // 画像がない場合は空の配列を返す（ダミー画像は表示しない）
        return [
            'images' => [],
            'businessHours' => [],
            'types' => []
        ];
    }
}
