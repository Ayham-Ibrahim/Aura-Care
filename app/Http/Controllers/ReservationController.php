<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Services\ReservationService;
use Symfony\Component\HttpFoundation\Request;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index(Request $request)
    {
        $reservations = $this->reservationService->getAllReservations($request->perPage);
        return $this->paginate($reservations, 'تم الحصول على جميع الحجوزات بنجاح');
    }
    // for customers: only change status (e.g., cancel)
    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation)
    {
        $reservation = $this->reservationService->updateReservationStatus($reservation, $request->validated()['status']);
        return $this->success($reservation, 'تم تعديل حالة الحجز');
    }

    public function getCenterReservation()
    {
        $reservation = $this->reservationService->centerReservation();
        return $this->success($reservation,'تم الحصول على حجوزات المركز بنجاح ');
    }

    public function getReservationById(Reservation $reservation)
    {
        $res = $this->reservationService->ReservationById($reservation->id);
        return $this->success($res, 'تم الحصول على الحجز بنجاح');
    }

    public function acceptReservation(Reservation $reservation)
    {
        $res = $this->reservationService->acceptReservation($reservation);
        return $this->success($res, 'تم قبول الحجز بنجاح');
    }

    public function reservationCompleted(Reservation $reservation)
    {
        $res = $this->reservationService->reservationCompleted($reservation);
        return $this->success($res, 'تم تعديل حالة الحجز');
    }

    public function cancelReservation(Reservation $reservation)
    {
        $res = $this->reservationService->cancelReservation($reservation);
        return $this->success($res, 'تم إلغاء الحجز بنجاح');
    }

    //TODO: ليزم نخزن صورة المرتجع للزبون في حقل ال  cancellation_image
    public function getReservationUserInfo(Reservation $reservation)
    {
        $user = $this->reservationService->ReservationUserInfo($reservation);
        return $this->success($user, 'تم الحصول على بيانات المستخدم بنجاح');
    }
}
