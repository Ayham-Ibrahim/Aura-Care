<?php

namespace App\Models\Center;

use App\Models\Section;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Center extends Model
{
    // use SoftDeletes;
    use HasApiTokens;
    protected $fillable = [
        'section_id',
        'name',
        'logo',
        'location_h',
        'location_v',
        'phone',
        'password',
        'reliable',
        'owner_name',
        'owner_number',
        'rating',
        'sham_image',
        'sham_code',
    ];

    protected $hidden = [
        'password',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'center_service');
    }

    public function works()
    {
        return $this->hasMany(Work::class);
    }

    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }
}
