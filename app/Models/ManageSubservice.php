<?php

namespace App\Models;

use App\Models\Center\Center;
use App\Models\ManageSubserviceImage;
use Illuminate\Database\Eloquent\Model;

class ManageSubservice extends Model
{
    //

    protected $fillable = [
        'center_id',
        'subservice_id',
        'price',
        'is_active',
        'activating_points',
        'points',
        'from',
        'to',
        'image',
        'description',
        'completion_time',
        'equipment',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function subservice()
    {
        return $this->belongsTo(Subservice::class);
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'reservation_manage_subservice');
    }

    public function images()
    {
        return $this->hasMany(ManageSubserviceImage::class);
    }

    /**
     * Offers that include this managed subservice.
     */
    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'manage_subservice_offer');
    }
}
