<?php

namespace App\Http\Requests\Subservice;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubserviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255|unique:subservices,name,' . ($this->subservice->id ?? null),
            'service_id' => 'sometimes|exists:services,id',
            'image' => 'sometimes|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }
}
