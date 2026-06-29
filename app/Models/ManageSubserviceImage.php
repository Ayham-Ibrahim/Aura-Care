<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManageSubserviceImage extends Model
{
    protected $fillable = [
        'manage_subservice_id',
        'image',
    ];

    public function manageSubservice()
    {
        return $this->belongsTo(ManageSubservice::class);
    }
}
