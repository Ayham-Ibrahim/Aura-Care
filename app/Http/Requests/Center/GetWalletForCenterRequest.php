<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class GetWalletForCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string|in:completed,incompleted',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'حالة الحجز',
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'حالة الحجز يجب أن تكون إما "completed" أو "incompleted".',
        ];
    }
    
}
