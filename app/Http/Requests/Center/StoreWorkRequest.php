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

    public function messages(): array
    {
        return [
            'description.required' => 'الوصف مطلوب.',
            'description.string' => 'الوصف يجب أن يكون نصًا.',
            'description.max' => 'الوصف لا يجب أن يتجاوز 255 حرفًا.',
            'video_path.string' => 'رابط الفيديو يجب أن يكون نصًا.',
            'video_path.max' => 'رابط الفيديو لا يجب أن يتجاوز 255 حرفًا.',
            'images.required' => 'الصور مطلوبة.',
            'images.array' => 'الصور يجب أن تكون مصفوفة.',
            'images.min' => 'يجب إضافة صورة واحدة على الأقل.',
            'images.*.required' => 'كل صورة مطلوبة.',
            'images.*.file' => 'الملف المرفق يجب أن يكون ملفًا.',
            'images.*.mimes' => 'الملف المرفق يجب أن يكون من نوع jpeg, png, jpg.',
            'images.*.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'images.*.max' => 'حجم الملف لا يجب أن يتجاوز 50 ميجابايت.',
        ];
    }
}
