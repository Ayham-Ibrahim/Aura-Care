<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterSubsevrice extends FormRequest
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
            'subservice_id' => 'required|exists:subservices,id',
            'price' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'activating_points' => 'sometimes|boolean',
            'points' => 'sometimes|integer|min:0',
            'from' => 'sometimes|date',
            'to' => 'sometimes|date|after_or_equal:from',
            'image' => 'nullable||image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
            'description' => 'required|string|max:2000',
            'completion_time' => 'nullable|string|max:255',
            'equipment' => 'nullable|string|max:255',
            //بدي دخل array من الصور
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:png,jpg,jpeg|mimetypes:image/jpeg,image/png,image/jpg|max:5000',
        ];
    }

    public function withvalidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->filled('activating_points') && $this->activating_points) {
                if (!$this->filled('points')) {
                    $validator->errors()->add('points', 'يجب تحديد عدد النقاط عند تفعيل النقاط.');
                }
                if (!$this->filled('from')) {
                    $validator->errors()->add('from', 'يجب تحديد تاريخ بدء تفعيل النقاط.');
                }
                if (!$this->filled('to')) {
                    $validator->errors()->add('to', 'يجب تحديد تاريخ انتهاء تفعيل النقاط.');
                }
            }

            if ($this->filled('is_active') && $this->is_active) {
                if (!$this->filled('price')) {
                    $validator->errors()->add('price', 'يجب تحديد السعر عند تفعيل الخدمة.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'subservice_id.required' => 'معرف الخدمة الفرعية مطلوب.',
            'subservice_id.exists' => 'الخدمة الفرعية المحددة غير موجودة.',
            'price.numeric' => 'السعر يجب أن يكون رقمًا.',
            'price.min' => 'السعر لا يمكن أن يكون أقل من 0.',
            'is_active.boolean' => 'حالة التفعيل يجب أن تكون صحيحة أو خاطئة.',
            'activating_points.boolean' => 'تفعيل النقاط يجب أن يكون صحيحًا أو خاطئًا.',
            'points.integer' => 'عدد النقاط يجب أن يكون رقمًا صحيحًا.',
            'points.min' => 'عدد النقاط لا يمكن أن يكون أقل من 0.',
            'from.date' => 'تاريخ البدء يجب أن يكون تاريخًا صالحًا.',
            'to.date' => 'تاريخ الانتهاء يجب أن يكون تاريخًا صالحًا.',
            'to.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء.',
            'image.image' => 'الملف المرفق يجب أن يكون صورة.',
            'image.mimes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.mimetypes' => 'الملف المرفق يجب أن يكون من نوع png, jpg, jpeg.',
            'image.max' => 'حجم الملف لا يجب أن يتجاوز 5 ميجابايت.',
            'description.required' => 'الوصف مطلوب.',
            'description.string' => 'الوصف يجب أن يكون نصًا.',
            'description.max' => 'الوصف لا يجب أن يتجاوز 2000 حرفًا.',
            'completion_time.string' => 'وقت الإنجاز يجب أن يكون نصًا.',
            'completion_time.max' => 'وقت الإنجاز لا يجب أن يتجاوز 255 حرفًا.',
            'equipment.string' => 'المعدات يجب أن تكون نصًا.',
            'equipment.max' => 'المعدات لا يجب أن تتجاوز 255 حرفًا.',
        ];
    }
}
