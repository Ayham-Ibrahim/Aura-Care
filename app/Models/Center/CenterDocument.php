<?php

namespace App\Models\Center;

use Illuminate\Database\Eloquent\Model;

class CenterDocument extends Model
{
    protected $fillable = [
        'center_id',
        'id_front',
        'id_back',
        'commercial_record',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
}