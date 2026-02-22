<?php

namespace App\Http\Requests\UserManagementRequests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmLoginRequest extends FormRequest
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
            'phone' => 'required|string',
            'otp_code' => 'required|string|size:4',
            'type' => 'required|string|in:user,center'
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب',
            'phone.string' => 'رقم الهاتف يجب أن يكون نصاً',
            'otp_code.required' => 'كود التحقق مطلوب',
            'otp_code.string' => 'كود التحقق يجب أن يكون نصاً',
            'otp_code.size' => 'كود التحقق يجب أن يكون 4 أرقام',
            'type.required' => 'نوع المستخدم مطلوب',
            'type.string' => 'نوع المستخدم يجب أن يكون نصاً',
            'type.in' => 'نوع المستخدم غير صالح'
        ];
    }

    public function attributes(): array
    {
        return [
            'phone' => 'رقم الهاتف',
            'otp_code' => 'كود التحقق',
            'type' => 'نوع المستخدم'
        ];
    }
}
