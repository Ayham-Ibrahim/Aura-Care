<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\ManageSubservice;
use App\Models\Subservice;
use App\Traits\DistanceTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserService extends Service
{
    use DistanceTrait;
    /**
     * Retrieve detailed data for a center intended for user view.
     *
     * @param \App\Models\Center\Center $center
     * @return array
     */
    public function getCenterDetailForUser(Center $center)
    {
        $center->load([
            'workingHours',
            'services:id,name,image',
            'works.service:id,name,image',
        ]);

        $is_verified = $center->verification_status == 'accepted';

        $basic = $center->only([
            'id',
            'rating',
            'name',
            'logo',
            'location_v',
            'location_h',
            'cover_image',
            'about_center',
            'phone',
        ]);

        $basic['is_verified'] = $is_verified;
        if (Auth::check()) {
            $basic['is_favorited'] =  Auth::guard('api')->user()->favoriteCenters()->where('center_id', $center->id)->exists() ?? false;
            $basic['distance'] = $this->calculateDistance(Auth::guard('api')->user(), $center);
        } else {
            $basic['is_favorited'] = false;
            $basic['distance'] = (float) 0;
        }

        $basic['working_hours'] = $center->workingHours;
        $basic['services'] = $center->services->makeHidden('pivot');
        $basic['works'] =  $center->works->map(function ($work) {
            $image = $work->files()->first()->path ?? null;
            return [
                'id' => $work->id,
                'name' => $work->service->name,
                'image' => $image ??  null,

            ];
        });

        $service_ids = $center->services->pluck('id')->toArray();

        $basic['subservices'] = ManageSubservice::with(['offers' => function ($query) {
                $query->where('from', '<=', now())
                    ->where('to', '>=', now())->with('manageSubservices');
            }, 'subservice'])->where('center_id', $center->id)->where('is_active', true)
                ->whereHas('subservice', function ($q) use ($service_ids) {
                    $q->whereIn('service_id', $service_ids);
                })
                ->get()
                ->map(function ($manage) {
                    $sub = $manage->subservice;
                    $offer = $manage->offers->map(function ($offer) use ($manage) {
                        $count = $offer->manageSubservices->count();
                        if ($count == 1) {
                            return $offer;
                        }
                    })->filter()->first();
                    $has_offer = false;
                    if ($offer) {
                        $has_offer = true;
                    }
                    $new_price = $has_offer ? $manage->price - $offer->discount_value : null;
                    $has_points = $manage->activating_points && $manage->points > 0 && $manage->from <= now() && $manage->to >= now();


                    return [
                        'id' => $manage ? $manage->id : null,
                        'name' => $sub->name,
                        'image' => $manage->image ?? $sub->image,
                        'price' =>  $manage->price,
                        'has_active_offers' => $has_offer,
                        'new_price' => $has_offer ? $new_price : null,
                        'has_points' => $has_points,
                        'points' => $manage->points ?? null,
                    ];
                });


        return $basic;
    }



    /**
     * Get all subservices associated with a specific center and service
     * where the pivot row is active. This is a more restrictive query
     * than the authenticated-center version above and is useful for
     * administrator reporting.
     *
     * @param int $centerId
     * @param int $serviceId
     * @return \Illuminate\Support\Collection
     */
    public function getSubservices($centerId, $serviceId)
    {
        try {
            $manage = ManageSubservice::with(['offers' => function ($query) {
                $query->where('from', '<=', now())
                    ->where('to', '>=', now())->with('manageSubservices');
            }, 'subservice'])->where('center_id', $centerId)->where('is_active', true)
                ->whereHas('subservice', function ($q) use ($serviceId) {
                    $q->where('service_id', $serviceId);
                })
                ->get()
                ->map(function ($manage) {
                    $sub = $manage->subservice;
                    $offer = $manage->offers->map(function ($offer) use ($manage) {
                        $count = $offer->manageSubservices->count();
                        if ($count == 1) {
                            return $offer;
                        }
                    })->filter()->first();
                    $has_offer = false;
                    if ($offer) {
                        $has_offer = true;
                    }
                    $new_price = $has_offer ? $manage->price - $offer->discount_value : null;
                    $has_points = $manage->activating_points && $manage->points > 0 && $manage->from <= now() && $manage->to >= now();


                    return [
                        'id' => $manage ? $manage->id : null,
                        'name' => $sub->name,
                        'image' => $manage->image ?? $sub->image,
                        'price' =>  $manage->price,
                        'has_active_offers' => $has_offer,
                        'new_price' => $has_offer ? $new_price : null,
                        'has_points' => $has_points,
                        'points' => $manage->points ?? null,
                    ];
                });

            return $manage;
        } catch (\Exception $e) {
            Log::error('Error fetching subservices for arbitrary center', ['center_id' => $centerId, 'service_id' => $serviceId, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الخدمات الفرعية للمركز');
        }
    }

    /**
     * Return all work records belonging to a particular center & service,
     * including the related files.
     *
     * @param int $centerId
     * @param int $serviceId
     * @return \Illuminate\Support\Collection
     */
    public function getCenterWorks($centerId, $serviceId)
    {
        try {
            return Work::with('files')
                ->where('center_id', $centerId)
                ->where('service_id', $serviceId)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching work for center/service', ['center_id' => $centerId, 'service_id' => $serviceId, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الأعمال الخاصة بالمركز');
        }
    }
}
