<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterLocation extends FormRequest
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
            'location_v' => 'required|numeric',
            'location_h' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'location_v.required' => 'خط العرض مطلوب.',
            'location_v.numeric' => 'خط العرض يجب أن يكون رقمًا.',
            'location_h.required' => 'خط الطول مطلوب.',
            'location_h.numeric' => 'خط الطول يجب أن يكون رقمًا.',
        ];
    }
}
