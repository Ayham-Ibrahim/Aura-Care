<?php

namespace App\Models\Center;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class Work extends Model
{
    //

    protected $fillable = [
        'center_id',
        'service_id',
        'description',
        'video_path',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function files()
    {
        return $this->hasMany(WorkFile::class);
    }
}
