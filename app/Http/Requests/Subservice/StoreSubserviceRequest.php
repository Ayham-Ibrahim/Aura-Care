<?php

namespace App\Http\Requests\Subservice;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubserviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:subservices,name',
            'service_id' => 'required|exists:services,id',
            'image' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
        ];
    }
}
