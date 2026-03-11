<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\Subservice;
use Illuminate\Support\Facades\Log;

class UserService extends Service
{
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

        $basic = $center->only([
            'id',
            'rating',
            'name',
            'logo',
            'location_v',
            'location_h',
        ]);

        $basic['working_hours'] = $center->workingHours;
        $basic['services'] = $center->services->makeHidden('pivot');
        $basic['works'] =  $center->works->map(function ($work) {
            return $work->service;
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
            $subservices = Subservice::where('service_id', $serviceId)
                ->whereHas('manageSubservices', function ($q) use ($centerId) {
                    $q->where('center_id', $centerId)
                      ->where('is_active', true);
                })
                ->get();

            return $subservices;
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
