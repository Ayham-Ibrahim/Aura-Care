<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use App\Models\Reservation;

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
}
