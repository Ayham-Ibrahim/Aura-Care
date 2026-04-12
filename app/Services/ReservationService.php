<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\ManageSubservice;
use App\Models\Offer;
use App\Models\Reservation;
use App\Models\Reviews;
use App\Models\Wallet;
use App\Services\FileStorage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReservationService extends Service
{
    public function getAllReservations($perPage)
    {
        $reservations = Reservation::select('id', 'center_id', 'user_id', 'status', 'date')
            ->with('center:id,name,logo', 'user:id,name', 'manageSubservices.subservice:id,name')
            ->paginate($perPage ?? 10);

        // إضافة subservices بعد جلب البيانات
        $reservations->getCollection()->transform(function ($reservation) {
            $reservation['subservices'] = $reservation->manageSubservices
                ->map(fn($item) => $item->subservice->name ?? null)
                ->filter()
                ->values();

            return $reservation;
        });
        $reservations->makeHidden('manageSubservices');
        return $reservations;
    }

    public function createReservation(array $data)
    {

        // return $data;
        try {
            $user_id = Auth::guard('api')->id();

            $totalAmount = 0;
            $discount_value = 0;

            if (!empty($data['offer'])) {
                $discount_value = Offer::where('id', $data['offer'])->value('discount_value');
                $data['subservices'] = Offer::find($data['offer'])->manageSubservices->pluck('id')->toArray();
            }


            if (!empty($data['subservices']) && is_array($data['subservices'])) {
                $totalAmount += ManageSubservice::whereIn('id', $data['subservices'])
                    ->sum('price');
            }

            $totalAmount -= $discount_value;

            DB::beginTransaction();

            $reservation = Reservation::create([
                'center_id' => $data['center_id'],
                'user_id' => $user_id,
                'date' => $data['date'],
                'total_amount' => $totalAmount,
                'deposit_amount' =>  round($totalAmount * config('hypermsg.reservation.deposit_percentage'), 2),
            ]);

            if (!empty($data['subservices']) && is_array($data['subservices'])) {
                $reservation->manageSubservices()->sync(array_unique($data['subservices']));
            }

            if (isset($data['offer']) && !empty($data['offer'])) {
                $reservation->offers()->sync($data['offer']);
            }

            DB::commit();

            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating reservation', ['payload' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إنشاء الحجز');
        }
    }

    public function updateReservationStatus(Reservation $reservation, string $status)
    {
        try {
            $reservation->status = $status;
            $reservation->save();
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error updating reservation status', ['reservation_id' => $reservation->id, 'status' => $status, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل حالة الحجز');
        }
    }
    public function getSubserviceWithTime(array $data)
    {
        $center = Center::with('workingHours')->findOrFail($data['center_id']);

        if (!$center) {
            $this->throwExceptionJson('المركز غير موجود', 404);
        }
        if ($data['offer'] ?? false) {
            $offer = Offer::with('manageSubservices')->find($data['offer']);
            if (!$offer || $offer->center_id != $center->id) {
                $this->throwExceptionJson('العرض غير موجود لنفس المركز', 422);
            }
            $data['manage_subservice'] = $offer->manageSubservices->pluck('id')->toArray();
        }

        $selectedSubservices = ManageSubservice::with('subservice:id,name,image')
            ->where('center_id', $center->id)
            ->where('is_active', true)
            ->whereIn('id', $data['manage_subservice'])
            ->get();

        if ($selectedSubservices->count() !== count($data['manage_subservice'])) {
            $this->throwExceptionJson('يوجد خدمة غير موجودة لنفس المركز', 422);
        }

        $oldAmount = $selectedSubservices->sum('price');
        if (isset($offer)) {
            $newAmount = $oldAmount - $offer->discount_value;
        } else {
            $newAmount = $oldAmount;
        }

        try {

            $subservicesData = $selectedSubservices->map(function ($item) {
                $data = [
                    'id' => $item->id,
                    'image' => $item->subservice->image ?? null,
                    'name' => $item->subservice->name ?? null,
                    'price' => $item->price,
                ];
                if ($item->activating_points &&  Carbon::now()->between(

                    Carbon::parse($item->from),
                    Carbon::parse($item->to)
                )) {
                    $data['points'] = $item->points;
                } else {
                    $data['points'] = 0;
                }
                return $data;
            })->values();



            $now = Carbon::today();
            $endDate = $now->copy()->addMonth();

            $workingHours = $center->workingHours->filter(function ($entry) {
                return (bool) $entry->is_active;
            })->keyBy('day');


            $reservationQuery = Reservation::select('id', 'date')
                ->where('center_id', $center->id)
                ->whereBetween('date', [$now->toDateString(), $endDate->toDateString()])
                ->whereIn('status', ['processing', 'confirmed', 'partially_rejected'])
                ->whereHas('manageSubservices', function ($q) use ($data) {
                    $q->whereIn('manage_subservices.id', $data['manage_subservice']);
                });

            $reservations = $reservationQuery->get();
            // return $reservations;

            $booked = [];
            foreach ($reservations as $reservation) {
                $dateCarbon = Carbon::parse($reservation->date);
                $dateKey = $dateCarbon->format('Y-m-d');
                $hour = $dateCarbon->format('H:00');
                $booked[$dateKey][] = $hour;
            }

            $availableTimes = [];
            for ($date = $now->copy(); $date->lte($endDate); $date->addDay()) {
                $dayOfWeek = $date->dayOfWeek;
                if (!isset($workingHours[$dayOfWeek])) {
                    continue;
                }
                $dateKey = $date->toDateString();
                for ($hour = Carbon::createFromFormat('H:i', substr($workingHours[$dayOfWeek]->open_time, 0, 5)); $hour->lt(Carbon::createFromFormat('H:i', substr($workingHours[$dayOfWeek]->close_time, 0, 5))); $hour->addHour()) {
                    $hourKey = $hour->format('H:i');
                    if (isset($booked[$dateKey])) {
                        if (in_array($hourKey, $booked[$dateKey])) {
                            continue;
                        }
                    }
                    $availableTimes[$dateKey][] = $hourKey;
                }
            }
            return [
                'old_amount' => $oldAmount,
                'new_amount' => $newAmount,
                'subservices' => $subservicesData,
                'available_times' => $availableTimes,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching subservice availability', ['payload' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب أوقات الخدمة');
        }
    }



    public function centerReservation()
    {
        try {
            $centerId = auth('center')->user()->id;
            $reservations = Reservation::with('user:id,name,avatar')->select('id', 'center_id', 'user_id', 'total_amount', 'status', 'deposit_amount')
                ->where('center_id', $centerId)
                ->whereNot('status', 'pending')
                ->get();
            return $reservations;
        } catch (\Exception $e) {
            Log::error('Error fetching center reservations', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب حجوزات المركز');
        }
    }

    public function getUserReservation()
    {
        try {
            $userId = auth('sanctum')->id() ?? auth('api')->id();

            $reservations = Reservation::with('center:id,name,logo')
                ->where('user_id', $userId)
                ->select('id', 'status', 'total_amount', 'deposit_amount', 'center_id')
                ->get();

            return $reservations->map(function ($reservation) {
                return [
                    'id' => $reservation->id,
                    'status' => $reservation->status,
                    'total_amount' => $reservation->total_amount,
                    'deposit_amount' => $reservation->deposit_amount,
                    'center' => $reservation->center ? $reservation->center->only(['id', 'logo', 'name']) : null,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching user reservations', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب حجوزات المستخدم');
        }
    }

    public function getUserReservationById(Reservation $reservation)
    {
        $this->chackUserAuth($reservation);

        try {
            $reservation->load([
                'center:id,name,logo,phone',
                'manageSubservices:id,price,subservice_id',
                'manageSubservices.subservice:id,name,image',
            ]);
            if (in_array($reservation->status, ['incompleted', 'cancelled', 'completed'])) {
                $remaining_amount = 0;
            } else {
                $remaining_amount = $reservation->remaining_amount;
            }

            return [
                'id' => $reservation->id,
                'date' => $reservation->date,
                'status' => $reservation->status,
                'total_amount' => $reservation->total_amount,
                'deposit_amount' => $reservation->deposit_amount,
                'remaining_amount' =>  $remaining_amount,
                'reason_for_cancellation' => $reservation->reason_for_cancellation,
                'subservice' => $reservation->manageSubservices->map(function ($sub) {
                    return [
                        'id' => $sub->id,
                        'price' => $sub->price,
                        'name' => $sub->subservice->name ?? null,
                        'image' => $sub->subservice->image ?? null,
                    ];
                }),
                'center' => $reservation->center ? $reservation->center->only(['id', 'logo', 'name', 'phone']) : null,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching user reservation by id', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب تفاصيل الحجز');
        }
    }

    public function ReservationById($id)
    {
        $reservation = Reservation::with('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image')->findOrFail($id);
        $this->chackCenterAuth($reservation);
        return $reservation;
    }

    public function acceptReservation(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);
        $res = $this->updateReservationStatus($reservation, 'processing');
        return $res->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
    }

    public function reservationCompleted(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);
        if ($reservation->status != 'processing') {
            $this->throwExceptionJson('لا يمكن تعديل حالة الحجز إلى مكتمل إلا إذا كان قيد المعالجة', 422);
        }

        try {

            $profit_percentage = $reservation->center->section->profit_percentage;
            $amount = ($reservation->total_amount * $profit_percentage) / 100;

            DB::beginTransaction();
            $reservation->wallet()->updateOrCreate([
                'reservation_id' => $reservation->id,
            ], [
                'center_id' => $reservation->center_id,
                'is_paid' => false,
                'required_value' => $amount,
            ]);
            $reservation->update(['status' => 'completed']);
            DB::commit();

            return $reservation->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing reservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إكمال الحجز');
        }
    }


    public function ReservationIncomplete(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);
        if ($reservation->status != 'processing') {
            $this->throwExceptionJson('لا يمكن تعديل حالة الحجز إلى مكتمل إلا إذا كان قيد المعالجة', 422);
        }

        try {

            $profit_percentage = $reservation->center->section->profit_percentage;
            $amount = ($reservation->deposit_amount * $profit_percentage) / 100;

            DB::beginTransaction();
            $reservation->wallet()->updateOrCreate([
                'reservation_id' => $reservation->id,
            ], [
                'center_id' => $reservation->center_id,
                'is_paid' => false,
                'required_value' => $amount,
            ]);

            $reservation->update(['status' => 'incompleted']);
            DB::commit();

            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error marking reservation as incomplete', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل حالة الحجز إلى غير مكتمل');
        }
    }

    // public function cancelReservation(Reservation $reservation)
    // {
    //     $this->chackCenterAuth($reservation);
    //     try {
    //         $reservation->update([
    //             'status' => 'cancelled',
    //             'reason_for_cancellation' => 'تم إلغاء الحجز من قبل المركز',
    //         ]);
    //         return $reservation->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
    //     } catch (\Exception $e) {
    //         Log::error('Error cancelling reservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    //         $this->throwExceptionJson('حدث خطأ ما أثناء إلغاء الحجز');

    //     }
    // }

    public function confirmDepositRefund(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);

        if ($reservation->is_return) {
            $this->throwExceptionJson('تم تأكيد رد العربون بالفعل', 422);
        }

        try {
            $reservation->update(['is_return' => true]);
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error confirming deposit refund', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تأكيد رد العربون');
        }
    }

    // public function cancelReservation(Reservation $reservation)
    // {
    //     $this->chackCenterAuth($reservation);
    //     $res = $this->updateReservationStatus($reservation, 'cancelled');
    //     return $res->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
    // }

    public function rejectReservation(Reservation $reservation, array $data)
    {
        $this->chackCenterAuth($reservation);

        try {
            $reservation->update([
                'status' => 'partially_rejected',
                'rejection_time' => now(),
                'reason_for_cancellation' => $data['reason'] ?? null,
            ]);

            // return $reservation->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error rejecting reservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء رفض الحجز');
        }
    }

    public function cancelReservationForUser(Reservation $reservation, array $data)
    {
        $this->chackUserAuth($reservation);

        if ($reservation->status === 'cancelled') {
            $this->throwExceptionJson('تم إلغاء الحجز بالفعل');
        }

        $hoursDiff = Carbon::now()->diffInHours($reservation->date, false);
        if ($hoursDiff <= 24) {
            $this->throwExceptionJson('لا يمكن إلغاء الحجز قبل أقل من 24 ساعة');
        }

        DB::beginTransaction();
        try {
            $reservation->update([
                'status' => 'cancelled',
                'reason_for_cancellation' => $data['reason'],
            ]);
            DB::commit();
            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelling reservation for user', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إلغاء الحجز');
        }
    }

    public function rateCenter(Reservation $reservation, Center $center, float $rating)
    {
        $this->chackUserAuth($reservation);

        if ($reservation->center_id !== $center->id) {
            $this->throwExceptionJson('مركز الحجز غير مطابق للمركز المراد تقييمه', 403);
        }

        if ($reservation->status !== 'completed') {
            $this->throwExceptionJson('لا يمكن تقييم المركز إلا بعد اكتمال الحجز');
        }

        try {
            DB::beginTransaction();

            Reviews::updateOrCreate([
                'user_id' => auth('sanctum')->id(),
                'center_id' => $center->id,
            ], [
                'rating' => $rating,
            ]);

            $rate = Reviews::where('center_id', $center->id)->avg('rating');

            $center->update(['rating' => $rate]);

            DB::commit();
            return $center->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rating center', ['reservation_id' => $reservation->id, 'center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تقييم المركز');
        }
    }

    public function chackCenterAuth($reservation)
    {
        $centerId = auth('center')->user()->id;
        if ($reservation->center_id != $centerId) {
            $this->throwExceptionJson('غير مصرح لك بالوصول إلى هذا الحجز', 403);
        }
    }

    public function chackUserAuth($reservation)
    {
        $userId = auth('sanctum')->id();
        if (!$userId || $reservation->user_id != $userId) {
            $this->throwExceptionJson('غير مصرح لك بالوصول إلى هذا الحجز', 403);
        }
    }

    public function ReservationUserInfo(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);
        try {
            $user = $reservation->user()->select('id', 'name', 'avatar', 'phone', 'sham_image', 'sham_code')->first();
            return $user;
        } catch (\Exception $e) {
            Log::error('Error fetching reservation user info', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب بيانات المستخدم');
        }
    }

    public function getCenterPaymentInfo(Reservation $reservation)
    {
        $this->chackUserAuth($reservation);
        try {

            $center = $reservation->center;

            return [
                'reservation_id' => $reservation->id,
                'deposit_amount' => $reservation->deposit_amount,
                'sham_image' => $center->sham_image,
                'sham_code' => $center->sham_code,
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching center payment info', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب معلومات الدفع');
        }
    }

    public function confirmReservation(Reservation $reservation, $data)
    {
        $this->chackUserAuth($reservation);

        try {
            $reservation->update([
                'payment_image' => FileStorage::storeFile($data['image'], 'reservations/payments', 'img'),
                'status' => 'confirmed',
                'rejection_time' => null, // مسح وقت الرفض
            ]);

            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error confirming reservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تأكيد الحجز');
        }
    }
}
