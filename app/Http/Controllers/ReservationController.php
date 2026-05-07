<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reservation\CancelReservationRequest;
use App\Http\Requests\Reservation\GetCenterPaymentInfoRequest;
use App\Http\Requests\Reservation\GetSubserviceWithTime;
use App\Http\Requests\Reservation\RatingCenterRequest;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationRequest;
use App\Http\Requests\Reservation\UpdateReservationStatusRequest;
use App\Http\Requests\Reservation\ConfirmReservationRequest;
use App\Http\Requests\Reservation\GetCenterReservationsRequest;
use App\Http\Requests\Reservation\UserPointsRequest;
use App\Models\Center\Center;
use App\Models\Reservation;
use App\Models\ReservationPaymentImage;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Http\Request;

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


    public function store(StoreReservationRequest $request)
    {
        $reservation = $this->reservationService->createReservation($request->validated());
        return $this->success($reservation, 'تم إنشاء الحجز بنجاح');
    }

    public function getCenterPaymentInfo(Reservation $reservation)
    {
        $payload = $this->reservationService->getCenterPaymentInfo($reservation);
        return $this->success($payload, 'تم جلب معلومات الدفع الخاصة بالمركز بنجاح');
    }

    public function confirmedReservation(ConfirmReservationRequest $request,Reservation $reservation)
    {
        $updated = $this->reservationService->confirmReservation($reservation, $request->validated());
        return $this->success($updated, 'تم تأكيد الحجز بنجاح');
    }

    // for customers: only change status (e.g., cancel)
    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation)
    {
        $reservation = $this->reservationService->updateReservationStatus($reservation, $request->validated()['status']);
        return $this->success($reservation, 'تم تعديل حالة الحجز');
    }

    public function cancelReservationForUser(Reservation $reservation, CancelReservationRequest $request)
    {
        $reservation = $this->reservationService->cancelReservationForUser($reservation, $request->validated());
        return $this->success($reservation, 'تم إلغاء الحجز بنجاح');
    }

    public function ratingCenter(Reservation $reservation, Center $center, RatingCenterRequest $request)
    {
        $center = $this->reservationService->rateCenter($reservation, $center, (float) $request->validated()['rating']);
        return $this->success($center, 'تم تقييم المركز بنجاح');
    }

    public function getCenterReservation(GetCenterReservationsRequest $request)
    {
        $reservation = $this->reservationService->centerReservation($request->validated());
        return $this->success($reservation, 'تم الحصول على حجوزات المركز بنجاح');
    }

    public function getSubserviceWithTime(Center $center,GetSubserviceWithTime $request)
    {
        $response = $this->reservationService->getSubserviceWithTime($center, $request->validated());
        return $this->success($response, 'تم جلب بيانات الخدمات والأوقات المتاحة بنجاح');
    }

    public function getUserReservation()
    {
        $reservations = $this->reservationService->getUserReservation();
        return $this->success($reservations, 'تم جلب حجوزات المستخدم بنجاح');
    }

    public function getUserReservationById(Reservation $reservation)
    {
        $res = $this->reservationService->getUserReservationById($reservation);
        return $this->success($res, 'تم الحصول على تفاصيل الحجز بنجاح');
    }

    public function getReservationById(Reservation $reservation)
    {
        $res = $this->reservationService->ReservationById($reservation);
        return $this->success($res, 'تم الحصول على الحجز بنجاح');
    }

    public function getReservationpaymentImages(Reservation $reservation)
    {
        $images = $this->reservationService->getReservationPaymentImages($reservation);
        return $this->success($images, 'تم جلب صور الدفع الخاصة بالحجز بنجاح');
    }

    public function deletePaymentImage(ReservationPaymentImage $image)
    {
        $this->reservationService->deletePaymentImage($image);
        return $this->success(null, 'تم حذف صورة الدفع بنجاح');
    }

    public function acceptReservation(Reservation $reservation)
    {
        $res = $this->reservationService->acceptReservation($reservation);
        return $this->success($res, 'تم قبول الحجز بنجاح');
    }

    public function reservationCompleted(Reservation $reservation, UserPointsRequest $request)
    {
        $res = $this->reservationService->reservationCompleted($reservation,$request->validated());
        return $this->success($res, 'تم تعديل حالة الحجز');
    }

    public function ReservationIncomplete( Reservation $reservation)
    {
        $res = $this->reservationService->ReservationIncomplete($reservation);
        return $this->success($res, 'تم تعديل الحجز بنجاح');
    }
    
    public function confirmDepositRefund(Reservation $reservation)
    {
        $reservation = $this->reservationService->confirmDepositRefund($reservation);
        return $this->success($reservation, 'تم تأكيد رد العربون بنجاح');
    }

    // public function cancelReservation(Reservation $reservation)
    // {
    //     $res = $this->reservationService->cancelReservation($reservation);
    //     return $this->success($res, 'تم إلغاء الحجز بنجاح');
    // }

    public function rejectReservation(Reservation $reservation, CancelReservationRequest $request)
    {
        $res = $this->reservationService->rejectReservation($reservation, $request->validated());
        return $this->success($res, 'تم رفض الحجز بنجاح، بانتظار رفع صورة الدفع خلال 30 دقيقة');
    }

    //TODO: ليزم نخزن صورة المرتجع للزبون في حقل ال  cancellation_image
    public function getReservationUserInfo(Reservation $reservation)
    {
        $user = $this->reservationService->ReservationUserInfo($reservation);
        return $this->success($user, 'تم الحصول على بيانات المستخدم بنجاح');
    }

        /**
     * Admin: return reservations for a specific user.
     */
    public function getUserReservationsForAdmin(User $user)
    {
        $reservations = $this->reservationService->getUserReservationsForAdmin($user);
        return $this->success($reservations, 'تم جلب الحجوزات الخاصة بالمستخدم بنجاح');
    }

    public function adminCancelUserReservation(Reservation $reservation)
    {
        $res = $this->reservationService->adminCancelUserReservation($reservation);
        return $this->success($res, 'تم إلغاء الحجز بنجاح');
    }
}
