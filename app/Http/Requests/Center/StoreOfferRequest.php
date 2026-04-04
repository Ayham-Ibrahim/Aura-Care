<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
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
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'description' => 'nullable|string|max:2000',
            'image' => 'nullable||image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
            'subservice' => 'required|array|min:1',
            'subservice.*' => 'required|integer|exists:subservices,id',
            // TODO: Add validation to make sure the selected subservices belong to the center and are active
        ];
    }

    public function messages()
    {
        return [
            'discount_percentage.required' => 'حقل نسبة الخصم مطلوب.',
            'discount_percentage.numeric' => 'حقل نسبة الخصم يجب أن يكون رقمًا.',
            'discount_percentage.min' => 'حقل نسبة الخصم لا يمكن أن يكون أقل من 0.',
            'discount_percentage.max' => 'حقل نسبة الخصم لا يمكن أن يكون أكثر من 100.',
            'from.required' => 'حقل تاريخ البداية مطلوب.',
            'from.date' => 'حقل تاريخ البداية يجب أن يكون تاريخًا صالحًا.',
            'to.required' => 'حقل تاريخ النهاية مطلوب.',
            'to.date' => 'حقل تاريخ النهاية يجب أن يكون تاريخًا صالحًا.',
            'to.after_or_equal' => 'حقل تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية.',
            'description.string' => 'حقل الوصف يجب أن يكون نصًا.',
            'description.max' => 'حقل الوصف لا يمكن أن يتجاوز 2000 حرف.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع image/jpeg, image/png, image/jpg.',
            'image.max' => 'الملف المرفق لا يمكن أن يتجاوز 5 ميجابايت.',
            'subservice.required' => 'يجب اختيار خدمة فرعية واحدة على الأقل.',
            'subservice.array' => 'حقل الخدمات الفرعية يجب أن يكون مصفوفة.',
            'subservice.*.required' => 'كل خدمة فرعية مختارة يجب أن تكون موجودة.',
            'subservice.*.integer' => 'كل خدمة فرعية مختارة يجب أن تكون رقمًا صحيحًا.',
            'subservice.*.exists' => 'الخدمة الفرعية المختارة غير موجودة في النظام.',
        ];
    }
}
