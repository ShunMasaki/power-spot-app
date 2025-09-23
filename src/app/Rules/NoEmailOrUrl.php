<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoEmailOrUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // メールアドレスのパターンをチェック
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $value)) {
            $fail('メールアドレスの記載はご遠慮ください。');
            return;
        }

        // URLのパターンをチェック（http/https、www、.comなど）
        $urlPatterns = [
            '/https?:\/\/[^\s]+/',           // http://... https://...
            '/www\.[^\s]+\.[a-zA-Z]{2,}/',   // www.example.com
            '/[a-zA-Z0-9-]+\.(com|net|org|jp|co\.jp|info|biz|me|tv|cc|tk)[^\s]*/',  // domain.com
        ];

        foreach ($urlPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $fail('URLの記載はご遠慮ください。');
                return;
            }
        }

        // SNSアカウント名のパターンをチェック
        if (preg_match('/@[a-zA-Z0-9_]+/', $value)) {
            $fail('SNSアカウント名の記載はご遠慮ください。');
            return;
        }

        // 電話番号のパターンをチェック
        if (preg_match('/\d{2,4}-\d{2,4}-\d{4}/', $value) || preg_match('/\d{10,11}/', $value)) {
            $fail('電話番号の記載はご遠慮ください。');
            return;
        }
    }
}
