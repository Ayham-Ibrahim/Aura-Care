<?php

namespace App\Http\Requests\Reservation;

use App\Models\Offer;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'center_id' => 'required|exists:centers,id',
            'date' => 'required|date',
            'subservices' => 'nullable|array',
            'subservices.*' => 'integer|exists:manage_subservices,id',
            'offer' => 'nullable|integer|exists:offers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'center_id.required' => 'يرجى تحديد المركز',
            'center_id.exists' => 'المركز المحدد غير موجود',
            'date.required' => 'يرجى تحديد تاريخ الحجز',
            'date.date' => 'التاريخ غير صالح',
            'subservices.array' => 'قائمة الخدمات غير صالحة',
            'subservices.*.exists' => 'الخدمة المختارة غير موجودة',
            'offer.integer' => 'العرض يجب أن يكون رقماً',
            'offer.exists' => 'العرض المختار غير موجود',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasSubservices = $this->filled('subservices') && !empty($this->subservices);
            $hasOffers = $this->filled('offer') && !empty($this->offer);

            if (!$hasSubservices && !$hasOffers) {
                $validator->errors()->add('subservices', 'يجب إرسال خدمات أو عروض على الأقل.');
                $validator->errors()->add('offer', 'يجب إرسال خدمات أو عروض على الأقل.');
                return;
            }

            if ($hasSubservices && $hasOffers) {
                $validator->errors()->add('subservices', 'لا يمكن إرسال خدمات وعروض معًا. يرجى اختيار إما خدمات أو عروض فقط.');
                $validator->errors()->add('offer', 'لا يمكن إرسال خدمات وعروض معًا. يرجى اختيار إما خدمات أو عروض فقط.');
                return;
            }

            if (!$this->filled('date')) {
                // يتحقق من القاعدة الأساسية في rules() ولا نكمل بدون تاريخ
                return;
            }

            try {
                $reservationDate = Carbon::parse($this->date);
            } catch (\Exception $e) {
                // يترك التحقق لقاعدة date
                return;
            }

            $subserviceIds = collect([]);

            if ($hasSubservices) {
                $subserviceIds = collect($this->subservices);
            } elseif ($hasOffers) {
                $offer = Offer::with('manageSubservices')->find($this->offer);
                if ($offer) {
                    $subserviceIds = $offer->manageSubservices->pluck('id');
                }
            }

            if ($subserviceIds->isEmpty()) {
                return;
            }

            $subserviceIds = $subserviceIds->unique()->filter()->toArray();

            $existing = Reservation::where('center_id', $this->center_id)
                ->where('date', $reservationDate->toDateTimeString())
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where(function ($query) use ($subserviceIds) {
                    // البحث في الخدمات المباشرة
                    $query->whereHas('manageSubservices', function ($q) use ($subserviceIds) {
                        $q->whereIn('manage_subservices.id', $subserviceIds);
                    });

                    // أو الحجوزات التي تحتوي على عروض تشمل الخدمات المطلوبة
                    $query->orWhereHas('offers', function ($q) use ($subserviceIds) {
                        $q->whereHas('manageSubservices', function ($subQ) use ($subserviceIds) {
                            $subQ->whereIn('manage_subservices.id', $subserviceIds);
                        });
                    });
                })
                ->exists();

            if ($existing) {
                $validator->errors()->add('date', 'الوقت المرسل محجوز');
            }
        });
    }


}
