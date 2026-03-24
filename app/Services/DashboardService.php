<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Center\Center;
use App\Models\ManageSubservice;
use App\Models\Offer;
use App\Models\Section;
use Illuminate\Support\Facades\Cache;

class DashboardService extends Service
{
    /**
     * Get home page dashboard data and cache it in database store.
     *
     * @return array
     */
    public function getHomePageData(): array
    {
        return [
            'offers' => $this->fetchOffers(),
            'ads' => $this->fetchAds(),
            'sections' => $this->fetchSections(),
            'centers' => $this->fetchCenters(),
            'sub_services_has_points' => $this->fetchSubservicesHasPoints(),
        ];
    }

    protected function fetchOffers()
    {
        return Offer::with([
            'center:id,name,logo,rating',
            'manageSubservices:id,price',
        ])
            ->select('id', 'center_id', 'image', 'description', 'discount_value')
            ->get()
            ->map(function (Offer $offer) {
                $center = $offer->center ? $offer->center->only(['id', 'name', 'logo', 'rating']) : null;

                return [
                    'id' => $offer->id,
                    'image' => $offer->image,
                    'description' => $offer->description,
                    'price' => $offer->discount_value,
                    'old_price' => $offer->manageSubservices->sum('price'),
                    'center' => $center,
                ];
            });
    }

    protected function fetchAds()
    {
        return Ad::select('id', 'image')->get();
    }

    protected function fetchSections()
    {
        return Section::select('id', 'name', 'image')->get();
    }

    protected function fetchCenters()
    {
        return Center::select('id', 'name', 'logo')->get();
    }

    protected function fetchSubservicesHasPoints()
    {
        $subservice = ManageSubservice::with([
            'center:id,name,logo,rating',
            'subservice:id,name,image',
        ])
            ->select('id', 'center_id', 'subservice_id', 'points', 'from', 'to')
            ->where('activating_points', 1)
            ->get();

        return $subservice->map(function (ManageSubservice $manageSubservice) {
            $center = $manageSubservice->center ? $manageSubservice->center->only(['id', 'name', 'logo', 'rating']) : null;
            $subservice = $manageSubservice->subservice ? $manageSubservice->subservice->only(['id', 'name', 'image']) : null;

            return [
                'id' => $manageSubservice->id,
                'name' => $subservice['name'],
                'image' => $subservice['image'],
                'points' => $manageSubservice->points,
                'from' => $manageSubservice->from,
                'to' => $manageSubservice->to,
                'center' => $center,
            ];
        });
    }
}
