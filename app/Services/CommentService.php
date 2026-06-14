<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\CommentReport;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CommentService extends Service
{
    protected NotificationService $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function store(Reservation $reservation, array $data)
    {
        if ($reservation->user_id !== Auth::id()) {
            $this->throwExceptionJson('غير مصرح لك باضافة تعليق على هذا الحجز', 403);
        }
        $comments = Comment::where('reservation_id', $reservation->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($comments) {
            $this->throwExceptionJson('لقد قمت بإضافة تعليق مسبقاً', 400);
        }

        try {
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'center_id' => $reservation->center_id,
                'reservation_id' => $reservation->id,
                'text' => $data['text'],
                'is_edited' => false,
            ]);

            $this->notificationService->notiCommentStoreForCenter($comment);

            return $comment;
        } catch (\Exception $e) {
            Log::error('Error creating reservation comment', ['reservation_id' => $reservation->id, 'user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إضافة التعليق');
        }
    }

    public function update(Comment $comment, array $data)
    {
        if ($comment->user_id !== Auth::id()) {
            $this->throwExceptionJson('غير مصرح لك بتعديل هذا التعليق', 403);
        }

        if ($comment->is_edited) {
            $this->throwExceptionJson('لا يمكن تعديل التعليق بعد تعديله مرة واحدة', 400);
        }
        try {
            $comment->update([
                'text' => $data['text'],
                'is_edited' => true,
            ]);

            $this->notificationService->notiCommentUpdateForCenter(Comment::where('id', $comment->id)->with('center')->first());
            return $comment;
        } catch (\Exception $e) {
            Log::error('Error updating comment', ['comment_id' => $comment->id, 'user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل التعليق');
        }
    }


    public function deleteComment(Comment $comment)
    {
        if ($comment->user_id !== Auth::id()) {
            $this->throwExceptionJson('غير مصرح لك بحذف هذا التعليق', 403);
        }
        try {
            $comment->delete();
            return $comment;
        } catch (\Exception $e) {
            Log::error('Error deleting comment', ['comment_id' => $comment->id, 'user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف التعليق');
        }
    }

    public function reportComment(Comment $comment, array $data)
    {
        if ($comment->trashed()) {
            $this->throwExceptionJson('التعليق غير متاح للتبليغ', 404);
        }

        try {
            return CommentReport::create([
                'comment_id' => $comment->id,
                'center_id' => $comment->center_id,
                'reason' => $data['reason'],
                'status' => CommentReport::STATUS_PENDING,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating comment report', ['comment_id' => $comment->id, 'user_id' => Auth::id(), 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إرسال البلاغ');
        }
    }

    public function getAllCommentReports()
    {
        try {
            return CommentReport::with(['comment.user:id,name,avatar', 'center:id,name'])
                ->orderByDesc('created_at')
                ->where('status', CommentReport::STATUS_PENDING)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching comment reports', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب البلاغات');
        }
    }

    public function getCenterCommentReports(Center $center)
    {
        try {
            return CommentReport::with(['comment.user:id,name,avatar', 'center:id,name'])
                ->where('center_id', $center->id)
                ->where('status', CommentReport::STATUS_PENDING)
                ->orderByDesc('created_at')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching center comment reports', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب بلاغات المركز');
        }
    }

    public function rejectCommentReport(CommentReport $report)
    {
        if ($report->status !== CommentReport::STATUS_PENDING) {
            $this->throwExceptionJson('لا يمكن رفض البلاغ في هذه الحالة', 400);
        }

        try {
            $report->update(['status' => CommentReport::STATUS_REJECTED]);
            return $report;
        } catch (\Exception $e) {
            Log::error('Error rejecting comment report', ['report_id' => $report->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء رفض البلاغ');
        }
    }

    public function approveCommentReport(CommentReport $report)
    {
        if ($report->status !== CommentReport::STATUS_PENDING) {
            $this->throwExceptionJson('لا يمكن الموافقة على البلاغ في هذه الحالة', 400);
        }

        try {
            $comment = $report->comment;
            DB::beginTransaction();
            if ($comment) {
                $comment->delete();
            }
            $report->update(['status' => CommentReport::STATUS_APPROVED]);
            DB::commit();
            $this->notificationService->notiCommentReportApproveForCenter($report);

            return $report->load(['comment.user:id,name,avatar', 'center:id,name']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving comment report', ['report_id' => $report->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء الموافقة على البلاغ');
        }
    }


    public function storeCenterReply(Comment $comment, array $data)
    {
        $center = Auth::guard('center')->user();

        if (!$center || $comment->center_id !== $center->id) {
            $this->throwExceptionJson('غير مصرح لك بالرد على هذا التعليق', 403);
        }

        if ($comment->replies()->where('center_id', $center->id)->exists()) {
            $this->throwExceptionJson('لقد تم إضافة رد لهذا التعليق بالفعل', 400);
        }

        try {
            $reply = CommentReply::create([
                'comment_id' => $comment->id,
                'center_id' => $center->id,
                'reply' => $data['reply'],
            ]);
            $this->notificationService->notiCommentReplyStoreForUser($comment);
            return $reply;
        } catch (\Exception $e) {
            Log::error('Error creating center comment reply', ['comment_id' => $comment->id, 'center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إضافة رد المركز');
        }
    }

    public function updateCenterReply(CommentReply $reply, array $data)
    {
        $center = Auth::guard('center')->user();

        if (!$center || $reply->center_id !== $center->id) {
            $this->throwExceptionJson('غير مصرح لك بتعديل هذا الرد', 403);
        }

        try {
            $reply->update([
                'reply' => $data['reply'],
            ]);
            return $reply;
        } catch (\Exception $e) {
            Log::error('Error updating center comment reply', ['reply_id' => $reply->id, 'center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل رد المركز');
        }
    }

    public function deleteCenterReply(CommentReply $reply)
    {
        $center = Auth::guard('center')->user();

        if (!$center || $reply->center_id !== $center->id) {
            $this->throwExceptionJson('غير مصرح لك بحذف هذا الرد', 403);
        }

        try {
            $reply->delete();
            return $reply;
        } catch (\Exception $e) {
            Log::error('Error deleting center comment reply', ['reply_id' => $reply->id, 'center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف رد المركز');
        }
    }

    public function getComments(Center $center)
    {
        try {
            $comments = Comment::with(['user:id,name,avatar', 'replies', 'center:id,name', 'reservation.manageSubservices.subservice'])
                ->where('center_id', $center->id)
                ->get();
            return $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'user_has_this_comment' => $comment->user_id === Auth::id(),
                    'text' => $comment->text,
                    'is_edited' => $comment->is_edited,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'subservices' => $comment->reservation && $comment->reservation->manageSubservices
                        ? $comment->reservation->manageSubservices->map(function ($manageSubservice) {
                            return [
                                'id' => $manageSubservice->subservice_id,
                                'name' => $manageSubservice->subservice ? $manageSubservice->subservice->name : null,
                                // 'image' => $manageSubservice->subservice ? $manageSubservice->subservice->image : null,
                            ];
                        })
                        : [],
                    'replies' => $comment->replies->map(function ($reply,) use ($comment) {
                        return [
                            'id' => $reply->id,
                            'reply' => $reply->reply,
                            'center_name' => $comment->center->name,
                            'created_at' => $reply->created_at,
                        ];
                    }),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching comments', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب التعليقات');
        }
    }

    public function getCommentsForCenter()
    {
        try {
            $center_id =  Auth::guard('center')->user()->id;
            $comments = Comment::with(['user:id,name,avatar', 'replies', 'center:id,name', 'reservation.manageSubservices.subservice'])
                ->where('center_id', $center_id)
                ->get();

            return $comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'text' => $comment->text,
                    'is_edited' => $comment->is_edited,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'subservices' => $comment->reservation && $comment->reservation->manageSubservices
                        ? $comment->reservation->manageSubservices->map(function ($manageSubservice) {
                            return [
                                'id' => $manageSubservice->subservice_id,
                                'name' => $manageSubservice->subservice ? $manageSubservice->subservice->name : null,
                                // 'image' => $manageSubservice->subservice ? $manageSubservice->subservice->image : null,
                            ];
                        })
                        : [],
                    'replies' => $comment->replies->map(function ($reply,) use ($comment) {
                        return [
                            'id' => $reply->id,
                            'reply' => $reply->reply,
                            'center_name' => $comment->center->name,
                            'created_at' => $reply->created_at,
                        ];
                    }),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching comments for center', ['center_id' => Auth::guard('center')->id(), 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب تعليقات المركز');
        }
    }

    public function getCenterCommentsForAdmin(Center $center)
    {
        try {
            $comments = Comment::with(['user:id,name,avatar', 'replies', 'center:id,name', 'reservation.manageSubservices.subservice'])
                ->where('center_id', $center->id)
                ->get();

            return    $comments->map(function (Comment $comment) {
                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'center_id' => $comment->center_id,
                    'reservation_id' => $comment->reservation_id,
                    'text' => $comment->text,
                    'is_edited' => $comment->is_edited,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'center' => [
                        'id' => $comment->center->id,
                        'name' => $comment->center->name,
                    ],
                    'subservices' => $comment->reservation && $comment->reservation->manageSubservices
                        ? $comment->reservation->manageSubservices->map(function ($manageSubservice) {
                            return [
                                'id' => $manageSubservice->subservice_id,
                                'name' => $manageSubservice->subservice ? $manageSubservice->subservice->name : null,
                            ];
                        })
                        : [],
                    'replies' => $comment->replies->map(function ($reply) use ($comment) {
                        return [
                            'id' => $reply->id,
                            'reply' => $reply->reply,
                            'center_name' => $comment->center->name,
                            'created_at' => $reply->created_at,
                        ];
                    }),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching comments for admin center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب تعليقات المركز');
        }
    }

    public function getCommentByIdForAdmin(Comment $comment)
    {
        try {
            $comment->load(['user:id,name,avatar', 'replies', 'center:id,name', 'reservation.manageSubservices.subservice']);
            
                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'center_id' => $comment->center_id,
                    'reservation_id' => $comment->reservation_id,
                    'text' => $comment->text,
                    'is_edited' => $comment->is_edited,
                    'created_at' => $comment->created_at,
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->avatar,
                    ],
                    'center' => [
                        'id' => $comment->center->id,
                        'name' => $comment->center->name,
                    ],
                    'subservices' => $comment->reservation && $comment->reservation->manageSubservices
                        ? $comment->reservation->manageSubservices->map(function ($manageSubservice) {
                            return [
                                'id' => $manageSubservice->subservice_id,
                                'name' => $manageSubservice->subservice ? $manageSubservice->subservice->name : null,
                            ];
                        })
                        : [],
                    'replies' => $comment->replies->map(function ($reply) use ($comment) {
                        return [
                            'id' => $reply->id,
                            'reply' => $reply->reply,
                            'center_name' => $comment->center->name,
                            'created_at' => $reply->created_at,
                        ];
                    }),
                ];
        } catch (\Exception $e) {
            Log::error('Error fetching comment for admin', ['comment_id' => $comment->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب التعليق');
        }
    }

    public function deleteCommentForAdmin(Comment $comment)
    {
        try {
            $comment->delete();
            return $comment;
        } catch (\Exception $e) {
            Log::error('Error deleting comment by admin', ['comment_id' => $comment->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف التعليق');
        }
    }
}
