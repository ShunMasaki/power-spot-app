<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Review;
use Carbon\Carbon;

class NoExcessivePosting implements ValidationRule
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 過去30分以内に10件以上投稿している場合は制限
        $recentReviewCount = Review::where('user_id', $this->userId)
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->count();

        if ($recentReviewCount >= 10) {
            $fail('短時間での大量投稿は制限されています。しばらく時間をおいてからお試しください。');
            return;
        }
    }
}
