<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => 'nullable|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'text.required' => 'يرجى كتابة التعليق',
            'text.string' => 'التعليق يجب أن يكون نصاً',
            'text.max' => 'التعليق طويل جداً',
        ];
    }
}
