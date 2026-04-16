<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'center_id' => 'sometimes|exists:centers,id',
            'user_id' => 'sometimes|exists:users,id',
            'total_amount' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'date' => 'sometimes|date',
            'hour' => 'sometimes',
            'payment_image' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'cancellation_image' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'reason_for_cancellation' => 'sometimes|string',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.exists' => 'المركز المحدد غير موجود.',
            'user_id.exists' => 'المستخدم المحدد غير موجود.',
            'total_amount.numeric' => 'المبلغ الإجمالي يجب أن يكون رقمًا.',
            'total_amount.min' => 'المبلغ الإجمالي يجب أن يكون 0 أو أكثر.',
            'status.in' => 'الحالة يجب أن تكون واحدة من: pending, confirmed, cancelled, completed.',
            'date.date' => 'التاريخ يجب أن يكون تاريخًا صالحًا.',
            'payment_image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'payment_image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'payment_image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'cancellation_image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'cancellation_image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'cancellation_image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'reason_for_cancellation.string' => 'سبب الإلغاء يجب أن يكون نصًا.',
        ];
    }
}
