<?php

namespace App\Models\Center;

use App\Models\ManageSubservice;
use App\Models\Section;
use App\Models\Service;
use App\Models\Subservice;
use App\Models\Center\WorkingHour;
use App\Models\Device;
use App\Models\Offer;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Center\WorkingHour[] $workingHours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ManageSubservice[] $manageSubservices
 *
 * @method \Illuminate\Database\Eloquent\Relations\HasMany workingHours()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany manageSubservices()
 */
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
        'owner_name',
        'owner_number',
        'rating',
        'sham_image',
        'sham_code',
        'phone_verified_at',
        'verification_status',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    /**
     * relationship to working hours entries
     */
    public function workingHours()
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    public function manageSubservices()
    {
        return $this->hasMany(ManageSubservice::class);
    }

    /**
     * associated documents for identity/commercial record
     */
    public function documents()
    {
        return $this->hasOne(CenterDocument::class);
    }

    /**
     * points associated with the center
     */
    public function points()
    {
        return $this->hasMany(\App\Models\Point::class);
    }

    /**
     * users who have favorited this center
     */
    public function favoredByUsers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'center_user')
            ->withTimestamps();
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function getWorkingHoursFormattedAttribute()
    {
        $hours = $this->workingHours()->orderBy('day')->get();

        // helper to determine if provided collection represents default 24/7 values
        $isDefaultPattern = function ($coll) {
            if ($coll->count() !== 7) {
                return false;
            }
            foreach ($coll as $entry) {
                if (! $entry->is_active) {
                    return false;
                }
                // normalize string to H:i:s
                $open = substr($entry->open_time, 0, 5);
                $close = substr($entry->close_time, 0, 5);
                if ($open !== '00:00' || $close !== '23:59') {
                    return false;
                }
            }
            return true;
        };

        // if no data or all days inactive OR exact default pattern, treat as default 24/7
        if (
            $hours->isEmpty()
            || $hours->where('is_active', 1)->isEmpty()
            || $isDefaultPattern($hours)
        ) {
            return [
                'default' => true,
                'text' => 'يعمل 24 ساعة، كل أيام الأسبوع',
                'hours' => $hours->map(function ($h) {
                    return [
                        'day' => $h->day,
                        'open_time' => substr($h->open_time, 0, 5),
                        'close_time' => substr($h->close_time, 0, 5),
                        'is_active' => (bool)$h->is_active,
                    ];
                }),
            ];
        }

        return [
            'default' => false,
            'hours' => $hours->map(function ($h) {
                return [
                    'day' => $h->day,
                    'open_time' => substr($h->open_time, 0, 5),
                    'close_time' => substr($h->close_time, 0, 5),
                    'is_active' => (bool)$h->is_active,
                ];
            }),
        ];
    }




    /**
     * Register or update FCM token.
     * Drivers only support single device - previous device will be replaced.
     *
     * @param string $fcmToken
     * @return Device
     */
    public function registerDevice(string $fcmToken): Device
    {
        return Device::registerSingleDevice($this, $fcmToken);
    }
}
