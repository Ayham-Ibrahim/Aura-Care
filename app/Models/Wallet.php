<?php

namespace App\Models;

use App\Models\Center\Center;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = 'wallets';

    protected $fillable = [
        'reservation_id',
        'center_id',
        'is_paid',
        'required_value',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'required_value' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false);
    }

    public function markAsPaid(): self
    {
        $this->is_paid = true;
        $this->save();

        return $this;
    }
}
