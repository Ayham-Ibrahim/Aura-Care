<?php

namespace App\Http\Requests\Subservice;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubserviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:subservices,name',
            'service_id' => 'required|exists:services,id',
            'image' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الخدمة الفرعية مطلوب',
            'name.string' => 'اسم الخدمة الفرعية يجب أن يكون نصًا',
            'name.max' => 'اسم الخدمة الفرعية لا يجب أن يتجاوز 255 حرفًا',
            'name.unique' => 'اسم الخدمة الفرعية مستخدم بالفعل',
            'service_id.required' => 'معرف الخدمة الرئيسية مطلوب',
            'service_id.exists' => 'معرف الخدمة الرئيسية غير موجود في قاعدة البيانات',
            'image.required' => 'صورة الخدمة الفرعية مطلوبة',
            'image.image' => 'الملف المرفق يجب أن يكون صورة',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg',
            'image.max' => 'حجم الصورة لا يجب أن يتجاوز 5 ميجابايت',
        ];
    }
}
