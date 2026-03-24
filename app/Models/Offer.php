<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'center_id',
        'discount_value',
        'discount_percentage',
        'from',
        'to',
        'description',
        'image',
    ];

    /**
     * The center that owns the offer.
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * Subservices that are included in the offer.
     */
    public function manageSubservices()
    {
        return $this->belongsToMany(ManageSubservice::class, 'manage_subservice_offer');
    }

    public function getSubserviceAttribute()
    {
        return $this->manageSubservices->subservice ?? null;
    }
}
