<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function store(Request $request, Spot $spot)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:255',
        ]);
        $review = $spot->reviews()->create([
            // 'user_id' => auth()->id(),
            'user_id' => 1, // ダミーID
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json($review, 201);
    }
}
