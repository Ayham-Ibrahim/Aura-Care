<?php

namespace App\Http\Requests\Section;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255|unique:sections,name,' . $this->section->id,
            'image' => 'sometimes|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'اسم القسم يجب أن يكون نصًا.',
            'name.max' => 'اسم القسم لا يجب أن يتجاوز 255 حرفًا.',
            'name.unique' => 'اسم القسم مستخدم بالفعل.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }
}
