<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة الحجز مطلوبة.',
            'status.in' => 'الحالة يجب أن تكون واحدة من: pending, confirmed, cancelled, completed.',
        ];
    }
}
