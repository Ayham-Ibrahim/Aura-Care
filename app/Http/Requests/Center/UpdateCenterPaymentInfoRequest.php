<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterPaymentInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sham_code' => 'nullable|string|max:255',
            'sham_image' => 'nullable|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function attributes(): array
    {
        return [
            'sham_code' => 'رمز الشام',
            'sham_image' => 'صورة الشام',
        ];
    }

    public function messages(): array
    {
        return [
            'sham_code.string' => 'رمز الشام يجب أن يكون نصًا.',
            'sham_code.max' => 'رمز الشام لا يجب أن يتجاوز 255 حرفًا.',
            'sham_image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'sham_image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'sham_image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'sham_image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}