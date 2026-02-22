<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subservice extends Model
{
    protected $fillable = [
        'name',
        'image',
        'service_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function manageSubservices()
    {
        return $this->hasMany(ManageSubservice::class);
    }
}
