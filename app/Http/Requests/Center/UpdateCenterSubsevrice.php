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
}
