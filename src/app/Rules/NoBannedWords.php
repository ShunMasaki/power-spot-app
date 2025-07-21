<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Config;

class NoBannedWords implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $bannedWords = Config::get('banwords.words', []);

        foreach ($bannedWords as $word) {
            if (str_contains($value, $word)) {
                $fail('不適切な表現が含まれています。別の表現をお試しください。');
                break;
            }
        }
    }
}
