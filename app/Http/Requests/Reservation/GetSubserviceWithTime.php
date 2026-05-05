<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;

class GetSubserviceWithTime extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // 'center_id' => 'required|exists:centers,id',
            'offer' => 'nullable|integer|exists:offers,id',
            'manage_subservice' => 'nullable|array|min:1',
            'manage_subservice.*' => 'required|integer|exists:manage_subservices,id',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.required' => 'يرجى تحديد المركز',
            'center_id.exists' => 'المركز المحدد غير موجود',
            'offer_id.exists' => 'العرض المحدد غير موجود',
            'manage_subservice.array' => 'قائمة الخدمات غير صالحة',
            'manage_subservice.*.exists' => 'الخدمة المختارة غير موجودة',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasSubservices = $this->filled('manage_subservice') && !empty($this->manage_subservice);
            $hasOffer = $this->filled('offer') && !empty($this->offer);

            if (!$hasSubservices && !$hasOffer) {
                $validator->errors()->add('manage_subservice', 'يجب إرسال خدمات أو عرض واحد على الأقل.');
                $validator->errors()->add('offer', 'يجب إرسال خدمات أو عرض واحد على الأقل.');
            }

            if ($hasSubservices && $hasOffer) {
                $validator->errors()->add('manage_subservice', 'لا يمكن إرسال خدمات وعرض معًا. يرجى اختيار إما خدمات أو عرض فقط.');
                $validator->errors()->add('offer', 'لا يمكن إرسال خدمات وعرض معًا. يرجى اختيار إما خدمات أو عرض فقط.');
            }
        });
    }
}
