<?php

namespace App\Models;

use App\Models\Center\Center;
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
    ];

    public function center(){
        return $this->belongsTo(Center::class);
    }

    public function subservice(){
        return $this->belongsTo(Subservice::class);
    }
}
