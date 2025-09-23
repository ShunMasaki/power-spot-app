<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Spot;
use App\Rules\NoDuplicateReview;
use App\Rules\NoExcessivePosting;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Spot $spot)
    {
        $reviews = $spot->reviews()
            ->with('user')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function store(Request $request, Spot $spot)
    {
        $userId = 1; // ダミーID (本来は auth()->id())

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
}
