<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255|unique:centers,name',
            'logo' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'location_h' => 'required|numeric',
            'location_v' => 'required|numeric',
            'phone' => 'unique:centers,phone|required|string|max:255',
            'password' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_number' => 'required|string|max:255',
            'services' => 'sometimes|array',
            'services.*' => 'integer|exists:services,id',
        ];
    }

    public function messages(): array
    {
        return [
            'section_id.required' => 'القسم مطلوب.',
            'section_id.exists' => 'القسم المحدد غير موجود.',
            'name.required' => 'اسم المركز مطلوب.',
            'name.string' => 'اسم المركز يجب أن يكون نصًا.',
            'name.max' => 'اسم المركز لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم المركز مستخدم بالفعل.',
            'logo.image' => 'الملف المرفق يجب أن يكون صورة.',
            'logo.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'logo.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'location_h.required' => 'خط الطول مطلوب.',
            'location_h.numeric' => 'خط الطول يجب أن يكون رقمًا.',
            'location_v.required' => 'خط العرض مطلوب.',
            'location_v.numeric' => 'خط العرض يجب أن يكون رقمًا.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max' => 'رقم الهاتف لا يجب أن يتجاوز 255 حرفًا.',
            'phone.unique' => 'رقم الهاتف مستخدم بالفعل.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.string' => 'كلمة المرور يجب أن تكون نصًا.',
            'password.max' => 'كلمة المرور لا يجب أن تتجاوز 255 حرفًا.',
            'owner_name.required' => 'اسم المالك مطلوب.',
            'owner_name.string' => 'اسم المالك يجب أن يكون نصًا.',
            'owner_name.max' => 'اسم المالك لا يجب أن يتجاوز 255 حرفًا.',
            'owner_number.required' => 'رقم المالك مطلوب.',
            'owner_number.string' => 'رقم المالك يجب أن يكون نصًا.',
            'owner_number.max' => 'رقم المالك لا يجب أن يتجاوز 255 حرفًا.',
            'services.array' => 'الخدمات يجب أن تكون مصفوفة.',
            'services.*.integer' => 'الخدمة يجب أن تكون رقمًا صحيحًا.',
            'services.*.exists' => 'الخدمة المحددة غير موجودة.',
        ];
    }
}
