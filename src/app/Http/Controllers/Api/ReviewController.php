<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\Spot;

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

    public function store(StoreReviewRequest $request, Spot $spot)
    {
        $validated = $request->validated();

        $review = $spot->reviews()->create([
            // 'user_id' => auth()->id(),
            'user_id' => 1, // ダミーID
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json($review, 201);
    }
}
