<?php

namespace App\Http\Requests\Center;

use App\Models\Center\Center;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreCenterDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_front' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
            'id_back' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',
            'commercial_record' => 'required|image
                        |mimes:png,jpg,jpeg
                        |mimetypes:image/jpeg,image/png,image/jpg
                        |max:5000',

                      
        ];
    }

    public function attributes(): array
    {
        return [
            'id_front' => 'صورة الهوية الأمامية',
            'id_back' => 'صورة الهوية الخلفية',
            'commercial_record' => 'صورة السجل التجاري',
        ];
    }

    public function messages(): array
    {
        return [
            'id_front.required' => 'صورة الهوية الأمامية مطلوبة.',
            'id_back.required' => 'صورة الهوية الخلفية مطلوبة.',
            'commercial_record.required' => 'صورة السجل التجاري مطلوبة.',
            'id_front.image' => 'الملف المرفوع لهوية الأمامية يجب أن يكون صورة.',
            'id_back.image' => 'الملف المرفوع لهوية الخلفية يجب أن يكون صورة.',
            'commercial_record.image' => 'الملف المرفوع للسجل التجاري يجب أن يكون صورة.',
            'id_front.mimes' => 'الملف المرفوع لهوية الأمامية يجب أن يكون من نوع png, jpg, أو jpeg.',
            'id_back.mimes' => 'الملف المرفوع لهوية الخلفية يجب أن يكون من نوع png, jpg, أو jpeg.',
            'commercial_record.mimes' => 'الملف المرفوع للسجل التجاري يجب أن يكون من نوع png, jpg, أو jpeg.',
            'id_front.max' => 'حجم الملف المرفوع لهوية الأمامية لا يجب أن يتجاوز 5 ميجابايت.',
            'id_back.max' => 'حجم الملف المرفوع لهوية الخلفية لا يجب أن يتجاوز 5 ميجابايت.',
            'commercial_record.max' => 'حجم الملف المرفوع للسجل التجاري لا يجب أن يتجاوز 5 ميجابايت.',
        ];
    }

    public function after(): array
{
    return [
        function (Validator $validator) {
            // إذا كان المستخدم الحالي هو مركز
            $center = Auth::guard('center')->user();
            
            if ($center && $center instanceof Center) {
                if ($center->verification_status === 'pending') {
                    $validator->errors()->add(
                        'verification_status',
                        'لا يمكن رفع المستندات لأن حالة التحقق معلقة.'
                    );
                }
                if ($center->verification_status === 'Approved') {
                    $validator->errors()->add(
                        'verification_status',
                        'لا يمكن رفع المستندات لأن المركز تم التحقق منه بالفعل.'
                    );
                }
            }
        },
    ];
}
}