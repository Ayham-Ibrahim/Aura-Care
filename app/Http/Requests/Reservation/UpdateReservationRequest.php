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
}
