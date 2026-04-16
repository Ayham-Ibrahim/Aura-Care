<?php

namespace App\Http\Requests\Ad;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'sometimes|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
