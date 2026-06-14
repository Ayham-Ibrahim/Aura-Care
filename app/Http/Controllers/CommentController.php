<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCenterReplyRequest;
use App\Http\Requests\Comment\StoreCommentReportRequest;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCenterReplyRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Center\Center;
use App\Models\Comment;
use App\Models\CommentReply;
use App\Models\CommentReport;
use App\Models\Reservation;
use App\Services\Center\CommentReplyService;
use App\Services\CommentService;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    public function store(StoreCommentRequest $request, Reservation $reservation)
    {
        $comment = $this->commentService->store($reservation, $request->validated());
        return $this->success($comment,'تم إضافة التعليق بنجاح',201);
    }

    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $updatedComment = $this->commentService->update($comment, $request->validated());
        return $this->success($updatedComment,'تم تعديل التعليق بنجاح',200);
    }

    public function destroy(Comment $comment)
    {
        $this->commentService->deleteComment($comment);
        return $this->success(null,'تم حذف التعليق بنجاح',204);
    }

    public function storeCenterReply(Comment $comment ,StoreCenterReplyRequest $request)
    {
        $reply = $this->commentService->storeCenterReply($comment, $request->validated());
        return $this->success($reply, 'تم إضافة رد المركز بنجاح', 201);
    }

    public function updateCenterReply(UpdateCenterReplyRequest $request, CommentReply $reply)
    {
        $updatedReply = $this->commentService->updateCenterReply($reply, $request->validated());
        return $this->success($updatedReply, 'تم تعديل رد المركز بنجاح', 200);
    }

    public function deleteCenterReply(CommentReply $reply)
    {
        $this->commentService->deleteCenterReply($reply);
        return $this->success(null, 'تم حذف رد المركز بنجاح', 204);
    }

    public function getCommentsForUser(Center $center)
    {
        $comments = $this->commentService->getComments($center);
        return $this->success($comments, 'تم جلب التعليقات بنجاح', 200);
    }

    public function getCommentsForCenter()
    {
        $comments = $this->commentService->getCommentsForCenter();
        return $this->success($comments, 'تم جلب التعليقات بنجاح', 200);
    }

    public function getCommentByIdForCenter(Comment $comment)
    {
        $commentData = $this->commentService->getCommentByIdForCenter($comment);
        return $this->success($commentData, 'تم جلب التعليق بنجاح', 200);
    }

    public function getCenterComment(Center $center)
    {
        $comments = $this->commentService->getCenterCommentsForAdmin($center);
        return $this->success($comments, 'تم جلب تعليقات المركز بنجاح', 200);
    }

    public function getCommentByIdForUser(Comment $comment)
    {
        $commentData = $this->commentService->getCommentByIdForUser($comment);
        return $this->success($commentData, 'تم جلب التعليق بنجاح', 200);
    }

    public function getCommentById(Comment $comment)
    {
        $commentData = $this->commentService->getCommentByIdForAdmin($comment);
        return $this->success($commentData, 'تم جلب التعليق بنجاح', 200);
    }

    public function deletecomment(Comment $comment)
    {
        $this->commentService->deleteCommentForAdmin($comment);
        return $this->success(null, 'تم حذف التعليق بنجاح', 204);
    }

    public function reportAComment(Comment $comment, StoreCommentReportRequest $request)
    {
        $report = $this->commentService->reportComment($comment, $request->validated());
        return $this->success($report, 'تم إرسال البلاغ بنجاح', 201);
    }

    public function getAllReports()
    {
        $reports = $this->commentService->getAllCommentReports();
        return $this->success($reports, 'تم جلب البلاغات بنجاح', 200);
    }

    public function getCenterReports(Center $center)
    {
        $reports = $this->commentService->getCenterCommentReports($center);
        return $this->success($reports, 'تم جلب بلاغات المركز بنجاح', 200);
    }

    public function rejectReport(CommentReport $report)
    {
        $updatedReport = $this->commentService->rejectCommentReport($report);
        return $this->success($updatedReport, 'تم رفض البلاغ بنجاح', 200);
    }

    public function approveReport(CommentReport $report)
    {
        $updatedReport = $this->commentService->approveCommentReport($report);
        return $this->success($updatedReport, 'تم الموافقة على البلاغ وتنظيف التعليق', 200);
    }
}
