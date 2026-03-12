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
}
