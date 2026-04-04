<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class RatingCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'required|numeric|min:0|max:5',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'التقييم مطلوب.',
            'rating.numeric' => 'التقييم يجب أن يكون رقمًا.',
            'rating.min' => 'التقييم لا يمكن أن يكون أقل من 0.',
            'rating.max' => 'التقييم لا يمكن أن يكون أكثر من 5.',
        ];
    }
}
