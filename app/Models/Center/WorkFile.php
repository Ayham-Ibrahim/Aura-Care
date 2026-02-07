<?php

namespace App\Models\Center;

use Illuminate\Database\Eloquent\Model;

class WorkFile extends Model
{
    //

    protected $fillable = [
        'work_id',
        'path',
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }
}
