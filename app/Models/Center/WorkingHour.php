<?php

namespace App\Models\Center;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $table = 'center_working_hours';

    protected $fillable = [
        'center_id',
        'day',
        'open_time',
        'close_time',
        'is_active',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}
