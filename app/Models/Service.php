<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $fillable = [
        'name',
        'image',
        'section_id',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subservices()
    {
        return $this->hasMany(Subservice::class);
    }

    public function centers()
    {
        return $this->belongsToMany(Center::class, 'center_service')->withTimestamps();
    }
} 
