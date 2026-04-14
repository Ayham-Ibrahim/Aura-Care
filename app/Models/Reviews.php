<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'center_id',
        'reservation_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
