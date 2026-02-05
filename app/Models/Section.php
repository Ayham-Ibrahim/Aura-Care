<?php

namespace App\Models;

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
}
