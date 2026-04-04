<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class CancelReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'سبب الإلغاء مطلوب.',
            'reason.string' => 'سبب الإلغاء يجب أن يكون نصًا.',
            'reason.max' => 'سبب الإلغاء لا يمكن أن يتجاوز 1000 حرف.',
        ];
    }
}
