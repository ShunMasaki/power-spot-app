<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Review;

class NoRecentReview implements ValidationRule
{
    protected $spotId;
    protected $userId;

    public function __construct($spotId, $userId)
    {
        $this->spotId = $spotId;
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 同じユーザーが同じスポットに既にレビューを投稿していないかチェック
        $existingReview = Review::where('user_id', $this->userId)
            ->where('spot_id', $this->spotId)
            ->exists();

        if ($existingReview) {
            $fail('このスポットには既にレビューを投稿済みです。');
            return;
        }

        // 注意: 異なるスポットへの連続投稿は許可
        // ユーザーが複数のスポットをまとめて回ってレビューするケースを考慮
    }
}
