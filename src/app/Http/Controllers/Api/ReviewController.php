<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\CognitoHelper;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Spot;
use App\Models\Review;
use App\Services\GooglePlacesService;
use App\Rules\NoDuplicateReview;
use App\Rules\NoExcessivePosting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    protected $googlePlacesService;

    public function __construct(GooglePlacesService $googlePlacesService)
    {
        $this->googlePlacesService = $googlePlacesService;
    }

    public function index(Spot $spot)
    {
        $reviews = $spot->reviews()
            ->with('user')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    /**
     * ユーザーのレビュー一覧を取得（ページネーション付き）
     */
    public function getUserReviews(Request $request)
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $perPage = $request->input('per_page', 10);

        $reviews = Review::where('user_id', $userId)
            ->with(['spot.spotBenefits.benefitType'])
            ->latest()
            ->paginate($perPage);

        // サムネイル画像を取得
        $reviews->getCollection()->transform(function ($review) {
            $review->thumbnail_image = $this->getThumbnailImage($review->spot);
            return $review;
        });

        return response()->json($reviews);
    }

    public function store(Request $request, Spot $spot)
    {
        $userId = CognitoHelper::getUserIdFromRequest($request);
        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // カスタムバリデーションを実行
        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => [
                'required',
                'string',
                'max:300',
                new \App\Rules\NoBannedWords,
                new \App\Rules\NoEmailOrUrl,
                new \App\Rules\NoSpamContent,
                new NoDuplicateReview($spot->id, $userId),
                new NoExcessivePosting($userId)
            ],
        ], [
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは300文字以内で入力してください。',
            'rating.required' => '評価を選択してください。',
            'rating.between' => '評価は1から5の間で選択してください。',
        ]);

        $review = $spot->reviews()->create([
            'user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review, 201);
    }

    /**
     * Google Places APIからサムネイル画像を取得
     */
    private function getThumbnailImage($spot): ?string
    {
        if (!$spot) {
            return null;
        }

        return $this->googlePlacesService->getPlacePhotoUrl(
            $spot->name,
            $spot->latitude,
            $spot->longitude
        );
    }
}
