<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'يرجى كتابة سبب البلاغ',
            'reason.string' => 'سبب البلاغ يجب أن يكون نصاً',
            'reason.max' => 'سبب البلاغ طويل جداً',
        ];
    }
}
