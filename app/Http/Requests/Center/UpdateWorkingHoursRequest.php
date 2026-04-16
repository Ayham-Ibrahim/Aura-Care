<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkingHoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'working_hours' => 'required|array|min:1',
            'working_hours.*.day' => 'required|integer|min:0|max:6',
            'working_hours.*.is_active' => 'nullable|boolean',
            'working_hours.*.open_time' => 'nullable|date_format:H:i',
            'working_hours.*.close_time' => 'nullable|date_format:H:i|after:working_hours.*.open_time',
        ];
    }

    public function messages(): array
    {
        return [
            'working_hours.required' => 'ساعات العمل مطلوبة.',
            'working_hours.array' => 'ساعات العمل يجب أن تكون مصفوفة.',
            'working_hours.min' => 'يجب إضافة ساعة عمل واحدة على الأقل.',
            'working_hours.*.day.required' => 'اليوم مطلوب.',
            'working_hours.*.day.integer' => 'اليوم يجب أن يكون رقمًا صحيحًا.',
            'working_hours.*.day.min' => 'اليوم يجب أن يكون بين 0 و 6.',
            'working_hours.*.day.max' => 'اليوم يجب أن يكون بين 0 و 6.',
            'working_hours.*.is_active.boolean' => 'حالة التفعيل يجب أن تكون صحيحة أو خاطئة.',
            'working_hours.*.open_time.date_format' => 'وقت الافتتاح يجب أن يكون بصيغة HH:MM.',
            'working_hours.*.close_time.date_format' => 'وقت الإغلاق يجب أن يكون بصيغة HH:MM.',
            'working_hours.*.close_time.after' => 'وقت الإغلاق يجب أن يكون بعد وقت الافتتاح.',
        ];
    }
}
