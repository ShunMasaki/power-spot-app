<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GooglePlacesService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_MAPS_API_KEY');
    }

    /**
     * スポットの詳細情報を取得
     */
    public function getPlaceDetails($placeId)
    {
        try {
                    $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                        'place_id' => $placeId,
                        'fields' => 'photos,opening_hours,formatted_address,rating,reviews,types',
                        'key' => $this->apiKey
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['result'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Places API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * スポットの画像を取得
     */
    public function getPlacePhotos($placeId, $maxPhotos = 5)
    {
        try {
            $placeDetails = $this->getPlaceDetails($placeId);

            if (!$placeDetails || !isset($placeDetails['photos'])) {
                return [];
            }

            $photos = [];
            $photoCount = min(count($placeDetails['photos']), $maxPhotos);

            for ($i = 0; $i < $photoCount; $i++) {
                $photo = $placeDetails['photos'][$i];
                $photoUrl = $this->getPhotoUrl($photo['photo_reference'], 800, 600);
                $photos[] = $photoUrl;
            }

            return $photos;
        } catch (\Exception $e) {
            Log::error('Google Places Photos Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 営業時間を取得
     */
    public function getBusinessHours($placeId)
    {
        try {
            $placeDetails = $this->getPlaceDetails($placeId);

            if (!$placeDetails || !isset($placeDetails['opening_hours'])) {
                return [];
            }

            $openingHours = $placeDetails['opening_hours'];
            $businessHours = [];

            // 7日分の営業時間を初期化
            for ($i = 0; $i < 7; $i++) {
                $businessHours[] = [];
            }

            if (isset($openingHours['weekday_text'])) {
                foreach ($openingHours['weekday_text'] as $dayText) {
                    // "Monday: 9:00 AM – 5:00 PM" のような形式をパース
                    if (preg_match('/^([^:]+):\s*(.+)$/', $dayText, $matches)) {
                        $dayName = trim($matches[1]);
                        $hours = trim($matches[2]);

                        $dayIndex = $this->getDayIndex($dayName);
                        if ($dayIndex !== null) {
                            $businessHours[$dayIndex] = $this->parseHours($hours);
                        }
                    }
                }
            }

            return $businessHours;
        } catch (\Exception $e) {
            Log::error('Google Places Business Hours Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 写真URLを生成
     */
    private function getPhotoUrl($photoReference, $maxWidth = 800, $maxHeight = 600)
    {
        $baseUrl = 'https://maps.googleapis.com/maps/api/place/photo';
        $params = [
            'maxwidth' => $maxWidth,
            'maxheight' => $maxHeight,
            'photo_reference' => $photoReference,
            'key' => env('GOOGLE_MAPS_API_KEY')
        ];

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * 曜日名をインデックスに変換
     */
    private function getDayIndex($dayName)
    {
        $dayMap = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6
        ];

        return $dayMap[$dayName] ?? null;
    }

    /**
     * 営業時間文字列をパース
     */
    private function parseHours($hoursText)
    {
        // "9:00 AM – 5:00 PM" のような形式を "9:00-17:00" に変換
        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)\s*–\s*(\d{1,2}):(\d{2})\s*(AM|PM)/', $hoursText, $matches)) {
            $startHour = $this->convertTo24Hour($matches[1], $matches[3]);
            $endHour = $this->convertTo24Hour($matches[4], $matches[6]);

            return [sprintf('%02d:%02d-%02d:%02d', $startHour, $matches[2], $endHour, $matches[5])];
        }

        // "Closed" の場合
        if (stripos($hoursText, 'closed') !== false) {
            return ['定休日'];
        }

        return [$hoursText];
    }

    /**
     * 12時間形式を24時間形式に変換
     */
    private function convertTo24Hour($hour, $period)
    {
        $hour = (int)$hour;

        if ($period === 'AM') {
            return $hour === 12 ? 0 : $hour;
        } else { // PM
            return $hour === 12 ? 12 : $hour + 12;
        }
    }

    /**
     * スポットのジャンル情報を取得
     */
    public function getPlaceTypes($placeId)
    {
        try {
            $placeDetails = $this->getPlaceDetails($placeId);

            if (!$placeDetails || !isset($placeDetails['types'])) {
                return [];
            }

            // 日本語のジャンル名に変換
            $typeMapping = [
                'place_of_worship' => '神社・寺院',
                'hindu_temple' => 'ヒンドゥー寺院',
                'church' => '教会',
                'mosque' => 'モスク',
                'synagogue' => 'シナゴーグ',
                'shrine' => '神社',
                'temple' => '寺院',
                'tourist_attraction' => '観光地',
                'park' => '公園',
                'museum' => '博物館',
                'restaurant' => 'レストラン',
                'cafe' => 'カフェ',
                'shopping_mall' => 'ショッピングモール',
                'store' => '店舗',
                'hospital' => '病院',
                'school' => '学校',
                'university' => '大学',
                'library' => '図書館',
                'post_office' => '郵便局',
                'bank' => '銀行',
                'atm' => 'ATM',
                'gas_station' => 'ガソリンスタンド',
                'parking' => '駐車場',
                'subway_station' => '地下鉄駅',
                'train_station' => '駅',
                'bus_station' => 'バス停',
                'airport' => '空港',
                'lodging' => '宿泊施設',
                'hotel' => 'ホテル',
                'rv_park' => 'キャンプ場',
                'campground' => 'キャンプ場',
                'zoo' => '動物園',
                'aquarium' => '水族館',
                'amusement_park' => '遊園地',
                'stadium' => 'スタジアム',
                'gym' => 'ジム',
                'spa' => 'スパ',
                'beauty_salon' => '美容院',
                'hair_care' => 'ヘアサロン',
                'laundry' => 'コインランドリー',
                'pharmacy' => '薬局',
                'dentist' => '歯科',
                'veterinary_care' => '動物病院',
                'police' => '警察署',
                'fire_station' => '消防署',
                'city_hall' => '市役所',
                'courthouse' => '裁判所',
                'embassy' => '大使館',
                'funeral_home' => '葬儀場',
                'cemetery' => '墓地',
                'real_estate_agency' => '不動産',
                'insurance_agency' => '保険',
                'travel_agency' => '旅行代理店',
                'car_rental' => 'レンタカー',
                'car_dealer' => '自動車販売',
                'car_repair' => '自動車修理',
                'car_wash' => '洗車場',
                'electronics_store' => '家電量販店',
                'furniture_store' => '家具店',
                'clothing_store' => '衣料品店',
                'shoe_store' => '靴店',
                'jewelry_store' => '宝石店',
                'book_store' => '書店',
                'bicycle_store' => '自転車店',
                'hardware_store' => 'ホームセンター',
                'garden_center' => '園芸店',
                'pet_store' => 'ペットショップ',
                'florist' => '花屋',
                'liquor_store' => '酒屋',
                'convenience_store' => 'コンビニ',
                'supermarket' => 'スーパーマーケット',
                'grocery_or_supermarket' => '食料品店',
                'bakery' => 'パン屋',
                'meal_takeaway' => 'テイクアウト',
                'meal_delivery' => 'デリバリー',
                'food' => '飲食店',
                'night_club' => 'ナイトクラブ',
                'bar' => 'バー',
                'casino' => 'カジノ',
                'movie_theater' => '映画館',
                'bowling_alley' => 'ボウリング場',
                'golf_course' => 'ゴルフ場',
                'gym' => 'ジム',
                'spa' => 'スパ',
                'beauty_salon' => '美容院',
                'hair_care' => 'ヘアサロン',
                'laundry' => 'コインランドリー',
                'pharmacy' => '薬局',
                'dentist' => '歯科',
                'veterinary_care' => '動物病院',
                'police' => '警察署',
                'fire_station' => '消防署',
                'city_hall' => '市役所',
                'courthouse' => '裁判所',
                'embassy' => '大使館',
                'funeral_home' => '葬儀場',
                'cemetery' => '墓地',
                'real_estate_agency' => '不動産',
                'insurance_agency' => '保険',
                'travel_agency' => '旅行代理店',
                'car_rental' => 'レンタカー',
                'car_dealer' => '自動車販売',
                'car_repair' => '自動車修理',
                'car_wash' => '洗車場',
                'electronics_store' => '家電量販店',
                'furniture_store' => '家具店',
                'clothing_store' => '衣料品店',
                'shoe_store' => '靴店',
                'jewelry_store' => '宝石店',
                'book_store' => '書店',
                'bicycle_store' => '自転車店',
                'hardware_store' => 'ホームセンター',
                'garden_center' => '園芸店',
                'pet_store' => 'ペットショップ',
                'florist' => '花屋',
                'liquor_store' => '酒屋',
                'convenience_store' => 'コンビニ',
                'supermarket' => 'スーパーマーケット',
                'grocery_or_supermarket' => '食料品店',
                'bakery' => 'パン屋',
                'meal_takeaway' => 'テイクアウト',
                'meal_delivery' => 'デリバリー',
                'food' => '飲食店',
                'night_club' => 'ナイトクラブ',
                'bar' => 'バー',
                'casino' => 'カジノ',
                'movie_theater' => '映画館',
                'bowling_alley' => 'ボウリング場',
                'golf_course' => 'ゴルフ場'
            ];

            $japaneseTypes = [];
            foreach ($placeDetails['types'] as $type) {
                if (isset($typeMapping[$type])) {
                    $japaneseTypes[] = $typeMapping[$type];
                }
            }

            // 重複を削除して返す
            return array_unique($japaneseTypes);
        } catch (\Exception $e) {
            Log::error('Google Places Types Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * スポット名からPlace IDを検索
     */
    public function findPlaceId($spotName, $latitude = null, $longitude = null)
    {
        try {
            // 複数の検索パターンを試す
            $searchPatterns = [
                $spotName, // 元の名前
                $this->convertToEnglishName($spotName), // 英語名に変換
                $spotName . ' 東京', // 東京を追加
                $spotName . ' Tokyo', // Tokyoを追加
            ];

            foreach ($searchPatterns as $query) {
                if ($latitude && $longitude) {
                    $query .= " near {$latitude},{$longitude}";
                }

                $response = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
                    'input' => $query,
                    'inputtype' => 'textquery',
                    'fields' => 'place_id',
                    'key' => $this->apiKey
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['candidates']) && count($data['candidates']) > 0) {
                        return $data['candidates'][0]['place_id'];
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Places Find Place Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 日本語名を英語名に変換（主要な神社・寺院）
     */
    private function convertToEnglishName($japaneseName)
    {
        $nameMapping = [
            '明治神宮' => 'Meiji Jingu',
            '東京大神宮' => 'Tokyo Daijingu',
            '浅草寺' => 'Sensoji Temple',
            '成田山新勝寺' => 'Narita-san Shinshoji Temple',
            '日光東照宮' => 'Nikko Toshogu Shrine',
            '出雲大社' => 'Izumo Taisha',
            '伊勢神宮' => 'Ise Jingu',
            '伏見稲荷大社' => 'Fushimi Inari Taisha',
            '清水寺' => 'Kiyomizu-dera Temple',
            '金閣寺' => 'Kinkaku-ji Temple',
            '銀閣寺' => 'Ginkaku-ji Temple',
            '東大寺' => 'Todai-ji Temple',
            '法隆寺' => 'Horyu-ji Temple',
            '厳島神社' => 'Itsukushima Shrine',
            '春日大社' => 'Kasuga Taisha',
        ];

        return $nameMapping[$japaneseName] ?? $japaneseName;
    }

    /**
     * スポット名と座標から写真URLを取得（サムネイル用）
     */
    public function getPlacePhotoUrl($spotName, $latitude, $longitude, $maxWidth = 400, $maxHeight = 300)
    {
        try {
            // Place IDを検索
            $placeId = $this->findPlaceId($spotName, $latitude, $longitude);

            if (!$placeId) {
                return null;
            }

            // 写真を取得
            $photos = $this->getPlacePhotos($placeId, 1); // 1枚だけ取得

            return $photos[0] ?? null;
        } catch (\Exception $e) {
            Log::error('Get Place Photo URL Error: ' . $e->getMessage());
            return null;
        }
    }
}
