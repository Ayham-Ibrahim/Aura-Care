<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommentReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'comment_id',
        'center_id',
        'reply',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function center()
    {
        return $this->belongsTo(Center::class, 'center_id');
    }
}
