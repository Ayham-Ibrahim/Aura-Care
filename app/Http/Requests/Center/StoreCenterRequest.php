<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreCenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|exists:sections,id',
            'name' => 'required|string|max:255|unique:centers,name',
            'logo' => 'sometimes|image|mimes:png,jpg,jpeg|max:5000',
            'location_h' => 'required|numeric',
            'location_v' => 'required|numeric',
            'phone' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'owner_number' => 'required|string|max:255',
            'services' => 'sometimes|array',
            'services.*' => 'integer|exists:services,id',
        ];
    }
}
