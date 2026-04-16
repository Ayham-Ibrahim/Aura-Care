<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class UserPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'points' => 'nullable|integer|min:0',
        ];
    }
    public function messages(): array
    {
        return [
            'points.integer' => 'يجب أن يكون عدد النقاط رقماً صحيحاً.',
            'points.min' => 'يجب أن يكون عدد النقاط 0 أو أكثر.',
        ];
    }   
}
