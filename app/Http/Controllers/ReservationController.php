<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    protected $reservationService;

    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    public function index()
    {
        $reservations = $this->reservationService->getAllReservations();
        return $this->success($reservations, 'تم الحصول على جميع الحجوزات بنجاح');
    }

    // public function store(StoreReservationRequest $request)
    // {
    //     $reservation = $this->reservationService->createReservation($request->validated());
    //     return $this->createdResponse($reservation, 'تم إنشاء الحجز بنجاح');
    // }

    // public function show(Reservation $reservation)
    // {
    //     return $this->success($reservation->load('center:id,name','user:id,name'), 'تم جلب الحجز بنجاح');
    // }

    // public function update(UpdateReservationRequest $request, Reservation $reservation)
    // {
    //     $data = $this->reservationService->updateReservation($reservation, $request->validated());
    //     return $this->success($data, 'تم تعديل الحجز بنجاح');
    // }

    // for customers: only change status (e.g., cancel)
    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation)
    {
        $reservation = $this->reservationService->updateReservationStatus($reservation, $request->validated()['status']);
        return $this->success($reservation, 'تم تعديل حالة الحجز');
    }

    // public function destroy(Reservation $reservation)
    // {
    //     $this->reservationService->deleteReservation($reservation);
    //     return $this->success(null, 'تم حذف الحجز بنجاح', 204);
    // }
}
