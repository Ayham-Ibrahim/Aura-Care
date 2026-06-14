<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Comment;
use App\Models\CommentReport;
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
    //الموافقة على توثيق السنتر
    public function notiCenterVerificationAccept(Center $center)
    {
        return $this->fcmService->sendToCenter(
            $center,
            ' طلب التوثيق ',
            "تمت الموافقة على توثيق مركزك بنجاح.",
            []
        );
    }
    //رفض توثيق السنتر
    public function notiCenterVerificationReject(Center $center)
    {
        return $this->fcmService->sendToCenter(
            $center,
            ' طلب التوثيق ',
            "تم رفض توثيق مركزك. ",
            []
        );
    }

    // ارسال اشعار للمستخدمين عند الحضور واكتمال الحجز
    public function notiReservationCompleteForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'تم اكتمال الحجز',
            "شكراً لحضورك حجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
             ",
            ['reservation_id' => $reservation->id]
        );
    }

    // ارسال اشعار للمستخدم عند عدم الحضور
    public function notiReservationNoShowForUser(Reservation $reservation)
    {
        return $this->fcmService->sendToUser(
            $reservation->user,
            'عدم الحضور',
            "لم تحضر حجزك بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
             ",
            ['reservation_id' => $reservation->id]
        );
    }

    // ارسال اشعار للمركز عند يقوم المستخدم بتقييم المركز
    public function notiCenterRatedByUser(Reservation $reservation, float $rating)
    {
        return $this->fcmService->sendToCenter(
            $reservation->center,
            'تقييم جديد',
            "تم تقييم مركزك بحجز بتاريخ:
            {$reservation->date->format('Y-m-d H:i')}
            التقييم: {$rating} نجوم
             ",
            ['reservation_id' => $reservation->id, 'rating' => $rating]
        );
    }

    public function notiCommentStoreForCenter(Comment $comment)
    {
        return $this->fcmService->sendToCenter(
            $comment->center,
            'تعليق جديد',
            "لديك تعليق جديد بتاريخ: {$comment->created_at->format('Y-m-d H:i')}",
            ['comment_id' => $comment->id]
        );
    }
    public function notiCommentUpdateForCenter(Comment $comment)
    {
        return $this->fcmService->sendToCenter(
            $comment->center,
            'تحديث تعليق',
            "تم تعديل تعليق بتاريخ: {$comment->updated_at->format('Y-m-d H:i')}",
            ['comment_id' => $comment->id]
        );
    }

    public function notiCommentReplyStoreForUser(Comment $comment)
    {
        return $this->fcmService->sendToUser(
            $comment->user,
            'رد جديد على تعليقك',
            "لديك رد جديد على تعليقك بتاريخ: {$comment->created_at->format('Y-m-d H:i')}",
            ['comment_id' => $comment->id]
        );
    }

    public function notiCommentReportForAdmin(Comment $comment)
    {
        return $this->fcmService->sendToUser(
            User::where('is_admin', true)->first(),
            'بلاغ جديد',
            "تم تقديم بلاغ على تعليق بتاريخ: {$comment->created_at->format('Y-m-d H:i')}",
            ['comment_id' => $comment->id]
        );
    }

    public function notiCommentReportApproveForCenter(CommentReport $report)
    {
        return $this->fcmService->sendToCenter(
            $report->center,
            'تحديث حالة البلاغ',
            "تم الموافقة على البلاغ المقدم بتاريخ: {$report->created_at->format('Y-m-d H:i')} وتم حذف التعليق",
        );
    }
}
