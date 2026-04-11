<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class GetCenterWalletDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'perPage' => 'nullable|integer',
        ];
    }

    public function attributes(): array
    {
        return [
            'perPage' => 'عدد العناصر في الصفحة',
        ];
    }

    public function messages(): array
    {
        return [
            'perPage.integer' => 'قيمة perPage يجب أن تكون رقمًا صحيحًا.',
        ];
    }
}
