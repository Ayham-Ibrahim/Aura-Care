<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CommentService extends Service
{
    public function store(Reservation $reservation, array $data)
    {
        if ($reservation->user_id !== Auth::id()) {
            $this->throwExceptionJson('غير مصرح لك باضافة تعليق على هذا الحجز', 403);
        }
        $comments = Comment::where('reservation_id', $reservation->id)
            ->where('user_id', Auth::id())
            ->first();

        if($comments) {
            $this->throwExceptionJson('لقد قمت بإضافة تعليق مسبقاً', 400);
        }

        try{
            $comment = Comment::create([
                'user_id' => Auth::id(),
                'center_id' => $reservation->center_id,
                'reservation_id' => $reservation->id,
                'text' => $data['text'],
                'is_edited' => false,
            ]);

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

        if($comment->is_edited) {
            $this->throwExceptionJson('لا يمكن تعديل التعليق بعد تعديله مرة واحدة', 400);
        }
        try {
            $comment->update([
                'text' => $data['text'],
                'is_edited' => true,
            ]);
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
}
