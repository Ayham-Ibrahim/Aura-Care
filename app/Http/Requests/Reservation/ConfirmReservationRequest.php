<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'يرجى إرفاق صورة الدفع',
            'image.image' => 'الملف المرفق يجب أن يكون صورة',
            'image.mimes' => 'أنواع الصور المسموح بها: jpeg,png,jpg',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 10 ميغا',
        ];
    }
}
