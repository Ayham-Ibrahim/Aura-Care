<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:services,name',
            'section_id' => 'required|exists:sections,id',
            'image' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الخدمة مطلوب.',
            'name.string' => 'اسم الخدمة يجب أن يكون نصًا.',
            'name.max' => 'اسم الخدمة لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم الخدمة مستخدم بالفعل.',
            'section_id.required' => 'القسم مطلوب.',
            'section_id.exists' => 'القسم المحدد غير موجود.',
            'image.required' => 'صورة الخدمة مطلوبة.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
