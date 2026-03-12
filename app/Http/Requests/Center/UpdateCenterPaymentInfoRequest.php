<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterPaymentInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sham_code' => 'nullable|string|max:255',
            'sham_image' => 'nullable|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
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