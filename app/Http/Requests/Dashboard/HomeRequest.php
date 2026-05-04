<?php

namespace App\Http\Requests\Dashboard;

use App\Enums\CenterSortType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'filter' => ['nullable', 'string', Rule::in(CenterSortType::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'يجب أن يكون حقل البحث نصًا.',
            'search.max' => 'لا يمكن أن يكون حقل البحث أكثر من 255 حرفًا.',
            'filter.string' => 'يجب أن يكون حقل التصفية نصًا.',
            'filter.in' => 'التصفية المحددة غير صالحة. القيم المسموح بها هي: ' . implode(', ', CenterSortType::values()) . '.',
        ];
    }
}
