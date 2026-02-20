<?php

namespace App\Http\Requests\Center;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:services,id',
            'description' => 'required|string|max:255',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file
                |mimes:jpeg,png,jpg,mp4,webm,ogg,mov,wmv
                |mimetypes:image/jpeg,image/png,image/jpg,video/mp4,video/webm,video/ogg,video/quicktime,video/x-ms-wmv
                |max:51200',
        ];
    }
}
