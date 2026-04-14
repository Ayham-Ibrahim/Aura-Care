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
}
