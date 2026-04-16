<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'sometimes|exists:sections,id',
            'name' => 'sometimes|string|max:255|unique:centers,name,' . $this->route('center')->id,
            'logo' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'location_h' => 'sometimes|numeric',
            'location_v' => 'sometimes|numeric',
            'phone' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|max:255',
            'owner_name' => 'sometimes|string|max:255',
            'owner_number' => 'sometimes|string|max:255',
            'services' => 'sometimes|array',
            'services.*' => 'integer|exists:services,id',
        ];
    }

    public function messages(): array
    {
        return [
            'section_id.exists' => 'القسم المحدد غير موجود.',
            'name.string' => 'اسم المركز يجب أن يكون نصًا.',
            'name.max' => 'اسم المركز لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم المركز مستخدم بالفعل.',
            'logo.image' => 'الملف المرفق يجب أن يكون صورة.',
            'logo.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'logo.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'location_h.numeric' => 'خط الطول يجب أن يكون رقمًا.',
            'location_v.numeric' => 'خط العرض يجب أن يكون رقمًا.',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصًا.',
            'phone.max' => 'رقم الهاتف لا يجب أن يتجاوز 255 حرفًا.',
            'password.string' => 'كلمة المرور يجب أن تكون نصًا.',
            'password.max' => 'كلمة المرور لا يجب أن تتجاوز 255 حرفًا.',
            'owner_name.string' => 'اسم المالك يجب أن يكون نصًا.',
            'owner_name.max' => 'اسم المالك لا يجب أن يتجاوز 255 حرفًا.',
            'owner_number.string' => 'رقم المالك يجب أن يكون نصًا.',
            'owner_number.max' => 'رقم المالك لا يجب أن يتجاوز 255 حرفًا.',
            'services.array' => 'الخدمات يجب أن تكون مصفوفة.',
            'services.*.integer' => 'الخدمة يجب أن تكون رقمًا صحيحًا.',
            'services.*.exists' => 'الخدمة المحددة غير موجودة.',
        ];
    }
}
