<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class GetCenterReservationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'status' => 'nullable|string|in:processing,confirmed,cancelled,completed,partially_rejected,incompleted',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date_format' => 'صيغة تاريخ البداية يجب أن تكون YYYY-MM-DD.',
            'end_date.date_format' => 'صيغة تاريخ النهاية يجب أن تكون YYYY-MM-DD.',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون مساويًا أو بعد تاريخ البداية.',
            'status.in' => 'حالة الحجز غير مدعومة.',
        ];
    }
}
