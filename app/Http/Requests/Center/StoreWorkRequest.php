<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|max:255',
            'video_path' => 'nullable|string|max:255',
            'images' => 'required|array|min:1',
            'images.*' => 'required|file
                |mimes:jpeg,png,jpg
                |mimetypes:image/jpeg,image/png,image/jpg
                |max:51200',
        ];
    }
}
