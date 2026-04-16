<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|unique:services,name,' . ($this->service->id ?? null),
            'section_id' => 'sometimes|exists:sections,id',
            'image' => 'sometimes|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'اسم الخدمة يجب أن يكون نصًا.',
            'name.max' => 'اسم الخدمة لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم الخدمة مستخدم بالفعل.',
            'section_id.exists' => 'القسم المحدد غير موجود.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
