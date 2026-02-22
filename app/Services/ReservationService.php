<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ReservationService extends Service
{
    public function getAllReservations($perPage = 10)
    {
        return Reservation::with('center:id,name', 'user:id,name')->paginate($perPage);
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
    public function centerReservation()
    {
        try {
            $centerId = auth('center')->user()->id;
            $reservations = Reservation::with('user:id,name,avatar')->select('id', 'center_id', 'user_id', 'total_amount', 'status', 'deposit_amount')
                ->where('center_id', $centerId)
                ->get();
            return $reservations;
        } catch (\Exception $e) {
            Log::error('Error fetching center reservations', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب حجوزات المركز');
        }
    }
    public function ReservationById($id)
    {
        try {
            $reservation = Reservation::with('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image')->findOrFail($id);
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error fetching reservation by ID', ['reservation_id' => $id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الحجز');
        }
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
        $res = $this->updateReservationStatus($reservation, 'completed');
        return $res->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
    }
    
    public function cancelReservation(Reservation $reservation)
    {
        $this->chackCenterAuth($reservation);
        $res = $this->updateReservationStatus($reservation, 'cancelled');
        return $res->load('user:id,name,avatar,phone', 'manageSubservices:id,price,subservice_id', 'manageSubservices.subservice:id,name,image');
    }

    public function chackCenterAuth($reservation)
    {
        $centerId = auth('center')->user()->id;
        if ($reservation->center_id != $centerId) {
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
}
