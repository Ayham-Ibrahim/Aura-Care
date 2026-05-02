<?php

namespace App\Models;

use App\Models\Center\Center;
use App\Models\Center\Work;
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
        return $this->belongsToMany(Center::class, 'center_service', 'service_id', 'center_id')->withTimestamps();
    }
    
    public function works()
    {
        return $this->hasMany(Work::class);
    }
} 
