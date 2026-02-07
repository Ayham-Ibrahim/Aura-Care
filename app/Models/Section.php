<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    //

    protected $fillable = [
        'name',
        'image',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function centers()
    {
        return $this->hasMany(Center::class);
    }
}
