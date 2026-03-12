<?php

namespace App\Http\Requests\UserManagementRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth()->check();
    }

    public function rules(): array
    {
        return [
            'v_location' => 'required|numeric',
            'h_location' => 'required|numeric',
        ];
    }

    public function messages(): array
    {
        return [
            'v_location.required' => 'خط العرض مطلوب.',
            'v_location.numeric' => 'خط العرض يجب أن يكون رقمًا.',
            'h_location.required' => 'خط الطول مطلوب.',
            'h_location.numeric' => 'خط الطول يجب أن يكون رقمًا.',
        ];
    }
}