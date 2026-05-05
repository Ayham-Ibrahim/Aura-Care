<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\User;

class NotificationService
{
    protected FcmService $fcmService;

    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    // Example method to send a notification to a center
    public function notiReservationCreateForCenter(Reservation $reservation)
    {
        return $this->fcmService->sendToCenter(
            $reservation->center,
            'حجز جديد',
            "لديك حجز جديد بتاريخ: {$reservation->date->format('Y-m-d H:i')}",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationCreateForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'تم إنشاء الحجز',
            "تم إنشاء حجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
            بانتظار موافقة المركز
            ",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationCancellForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'تحديث حالة الحجز',
            "تم إلغاء حجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
             ",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationCancellForCenter(Reservation $reservation)
    {
        return $this->fcmService->sendToCenter(
            $reservation->center,
            ' الغاء حجز',
            "تم إلغاء حجز بتاريخ: {$reservation->date->format('Y-m-d H:i')}",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationAcceptForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'تمت الموافقة على الحجز',
            "تمت الموافقة حجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
             ",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationRejectForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'تم رفض الحجز',
            "السبب: {$reservation->reason_for_cancellation}.
              لديك 30 دقيقة لتصحيح الخطاء قبل الغاء الحجز
             ",
            ['reservation_id' => $reservation->id]
        );
    }

    public function notiReservationDepositRefundForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            "ارجاع رعبون الحجز",
            "تم ارجاع القيمة المدفوعة لحجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
             ",
            ['reservation_id' => $reservation->id]
        );
    }
}
