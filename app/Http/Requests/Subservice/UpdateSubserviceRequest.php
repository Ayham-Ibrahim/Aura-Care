<?php

namespace App\Http\Requests\Subservice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubserviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|unique:subservices,name,' . ($this->subservice->id ?? null),
            'service_id' => 'sometimes|exists:services,id',
            'image' => 'sometimes|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'اسم الخدمة الفرعية يجب أن يكون نصًا.',
            'name.max' => 'اسم الخدمة الفرعية لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم الخدمة الفرعية مستخدم بالفعل.',
            'service_id.exists' => 'الخدمة الرئيسية المحددة غير موجودة.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
