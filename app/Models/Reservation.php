<?php

namespace App\Models;

use App\Models\Center\Center;
use App\Models\ReservationPaymentImage;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $appends = ['remaining_amount'];
    protected $fillable = [
        'center_id',
        'user_id',
        'total_amount',
        'status',
        'date',
        'cancellation_image',
        'reason_for_cancellation',
        'deposit_amount',
        'rejection_time',
        'is_return',
    ];

    protected $casts = [
        'date' => 'datetime',
        'rejection_time' => 'datetime',
        'is_return' => 'boolean',
    ];

    protected $hidden = [
        'payment_image',
        'cancellation_image',
    ];

    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manageSubservices()
    {
        return $this->belongsToMany(ManageSubservice::class, 'reservation_manage_subservice');
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'reservation_offer');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function paymentImages()
    {
        return $this->hasMany(ReservationPaymentImage::class);
    }

    public function scopeForCenter($query, int $centerId)
    {
        return $query->where('center_id', $centerId);
    }

    public function scopeExcludePending($query)
    {
        return $query->where('status', '!=', 'pending');
    }

    public function scopeFilterStatus($query, ?string $status)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeFilterDateRange($query, ?string $startDate, ?string $endDate = null)
    {
        if (!$startDate) {
            return $query;
        }

        if (!$endDate) {
            return $query->whereDate('date', $startDate);
        }

        return $query->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate);
    }

    /**
     * حساب المبلغ المتبقي للدفع
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->deposit_amount;
    }

    /**
     * التحقق من انتهاء فترة الـ 30 دقيقة
     */
    public function isPaymentVerificationExpired(): bool
    {
        if (!$this->rejection_time) {
            return false;
        }

        return now()->diffInMinutes($this->rejection_time) >= 30;
    }
}
