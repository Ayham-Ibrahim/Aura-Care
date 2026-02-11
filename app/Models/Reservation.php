<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'center_id',
        'user_id',
        'total_amount',
        'status',
        'date',
        'payment_image',
        'cancellation_image',
        'reason_for_cancellation',
    ];

    protected $casts = [
        'date' => 'date',
        'hour' => 'string',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
