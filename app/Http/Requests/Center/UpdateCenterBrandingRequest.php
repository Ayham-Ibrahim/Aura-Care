<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterBrandingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:5000',
            'cover_image' => 'nullable|image|mimes:png,jpg,jpeg|max:5000',
            'about_center' => 'nullable|nullable|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'شعار المركز يجب أن يكون صورة.',
            'logo.mimes' => 'شعار المركز يجب أن يكون من نوع png, jpg, jpeg.',
            'logo.max' => 'حجم شعار المركز لا يجب أن يتجاوز 5 ميجابايت.',
            'cover_image.image' => 'صورة الغلاف يجب أن تكون صورة.',
            'cover_image.mimes' => 'صورة الغلاف يجب أن تكون من نوع png, jpg, jpeg.',
            'cover_image.max' => 'حجم صورة الغلاف لا يجب أن يتجاوز 5 ميجابايت.',
            'about_center.string' => 'النبذة يجب أن تكون نصًا.',
            'about_center.max' => 'النبذة لا يجب أن تتجاوز 2000 حرفًا.',
        ];
    }

    public function attributes(): array
    {
        return [
            'logo' => 'شعار المركز',
            'cover_image' => 'صورة الغلاف',
            'about_center' => 'نبذة المركز',
        ];
    }
}
