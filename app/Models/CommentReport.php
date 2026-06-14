<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'comment_id',
        'center_id',
        'reason',
        'status',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->belongsTo(Comment::class)->withTrashed();
    }

    public function center()
    {
        return $this->belongsTo(\App\Models\Center\Center::class);
    }
}
