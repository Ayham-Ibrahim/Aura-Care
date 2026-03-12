<?php

namespace App\Http\Requests\UserManagementRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPaymentInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth()->check();
    }

    public function rules(): array
    {
        return [
            'sham_code' => 'required|string|max:255',
            'sham_image' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'sham_code.required' => 'رمز الشام مطلوب.',
            'sham_code.string' => 'رمز الشام يجب أن يكون نصًا.',
            'sham_code.max' => 'رمز الشام لا يجب أن يتجاوز 255 حرفًا.',
            'sham_image.required' => 'صورة الشام مطلوبة.',
            'sham_image.image' => 'الملف المرفوع لصورة الشام يجب أن يكون صورة.',
            'sham_image.mimes' => 'الملف المرفوع لصورة الشام يجب أن يكون من نوع png, jpg, أو jpeg.',
            'sham_image.mimetypes' => 'الملف المرفوع لصورة الشام يجب أن يكون من نوع image/jpeg, image/png, أو image/jpg.',
            'sham_image.max' => 'حجم الملف المرفوع لصورة الشام لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }

    public function attributes(): array
    {
        return [
            'sham_code' => 'رمز الشام',
            'sham_image' => 'صورة الشام',
        ];
    }
}