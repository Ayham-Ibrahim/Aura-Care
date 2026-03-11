<?php

namespace App\Services;

use App\Models\Center\Center;

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
}
