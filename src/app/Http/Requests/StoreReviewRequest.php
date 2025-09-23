<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\NoBannedWords;
use App\Rules\NoEmailOrUrl;
use App\Rules\NoSpamContent;

class StoreReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => [
                'required',
                'string',
                'max:300',
                new NoBannedWords,
                new NoEmailOrUrl,
                new NoSpamContent
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは300文字以内で入力してください。',
        ];
    }
}
