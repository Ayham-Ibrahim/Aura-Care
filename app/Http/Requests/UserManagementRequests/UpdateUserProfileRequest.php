<?php

namespace App\Http\Requests\UserManagementRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'phone' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'phone')->ignore(Auth::id())
            ],
            'avatar' =>'nullable|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',

        ];
    }
    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم المستخدم',
            'phone' => 'رقم الواتساب',
            'avatar' => 'الصورة الشخصية',
        ];
    }
    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'اسم المستخدم يجب أن يكون نصًا.',
            'name.max' => 'اسم المستخدم لا يجب أن يتجاوز 255 حرفًا.',
            'phone.string' => 'رقم الواتساب يجب أن يكون نصًا.',
            'phone.max' => 'رقم الواتساب لا يجب أن يتجاوز 255 حرفًا.',
            'phone.unique' => 'رقم الواتساب مستخدم بالفعل.',
            'avatar.image' => 'الصورة الشخصية يجب أن تكون صورة.',
            'avatar.mimes' => 'الصورة الشخصية يجب أن تكون من نوع png, jpg, أو jpeg.',
            'avatar.mimetypes' => 'الصورة الشخصية يجب أن تكون من نوع image/jpeg, image/png, أو image/jpg.',
            'avatar.max' => 'حجم الصورة الشخصية لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'غير مصرح لك بالقيام بهذا الإجراء.'
        ], 403));
    }
}
