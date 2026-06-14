<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCenterReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply' => 'required|string|max:200',
        ];
    }

    public function messages(): array
    {
        return [
            'reply.required' => 'يرجى كتابة الرد',
            'reply.string' => 'الرد يجب أن يكون نصاً',
            'reply.max' => 'الرد طويل جداً',
        ];
    }
}
