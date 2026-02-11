<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'sometimes|exists:sections,id',
            'name' => 'sometimes|string|max:255|unique:centers,name,' . $this->route('center')->id,
            'logo' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'location_h' => 'sometimes|numeric',
            'location_v' => 'sometimes|numeric',
            'phone' => 'sometimes|string|max:255',
            'password' => 'sometimes|string|max:255',
            'owner_name' => 'sometimes|string|max:255',
            'owner_number' => 'sometimes|string|max:255',
            'services' => 'sometimes|array',
            'services.*' => 'integer|exists:services,id',
        ];
    }
}
