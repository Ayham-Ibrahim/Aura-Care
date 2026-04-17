<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationPaymentImage extends Model
{
    protected $fillable = [
        'reservation_id',
        'image_path',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
