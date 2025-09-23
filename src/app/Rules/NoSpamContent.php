<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSpamContent implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 連続した同じ文字をチェック（5文字以上）
        if (preg_match('/(.)\1{4,}/', $value)) {
            $fail('同じ文字の連続は控えめにお願いします。');
            return;
        }

        // 過度な絵文字や記号の連続をチェック
        if (preg_match('/[!！？?]{4,}/', $value)) {
            $fail('記号の連続使用は控えめにお願いします。');
            return;
        }

        // 意味のない文字列パターンをチェック
        $meaninglessPatterns = [
            '/^[あ]{3,}$/',           // あああ
            '/^[う]{3,}$/',           // うううう
            '/^[え]{3,}$/',           // ええええ
            '/^[お]{3,}$/',           // おおおお
            '/^[w]{4,}$/',            // wwww
            '/^[W]{4,}$/',            // WWWW
            '/^[123456789]{4,}$/',    // 1234567
            '/^[abc]{4,}$/i',         // abcabc
            '/^[xyz]{3,}$/i',         // xyzxyz
        ];

        foreach ($meaninglessPatterns as $pattern) {
            if (preg_match($pattern, trim($value))) {
                $fail('意味のある内容を入力してください。');
                return;
            }
        }

        // 極端に短いコメント（5文字未満）をチェック
        if (mb_strlen(trim($value)) < 5) {
            $fail('コメントは5文字以上で入力してください。');
            return;
        }

        // 全角/半角スペースのみの場合をチェック
        if (preg_match('/^\s*$/', $value)) {
            $fail('コメントを入力してください。');
            return;
        }
    }
}
