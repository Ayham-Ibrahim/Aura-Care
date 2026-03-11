<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'شعار المركز مطلوب',
            'logo.image' => 'الملف يجب أن يكون صورة',
            'logo.mimes' => 'الملف يجب أن يكون من نوع: png, jpg, jpeg',
            'logo.mimetypes' => 'الملف يجب أن يكون من نوع: image/jpeg, image/png, image/jpg',
            'logo.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت',
        ];
    }

    public function attributes(): array
    {
        return [
            'logo' => 'شعار المركز',
        ];
    }
}