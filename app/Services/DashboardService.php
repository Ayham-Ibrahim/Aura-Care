<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\Center\Center;
use App\Models\ManageSubservice;
use App\Models\Offer;
use App\Models\Section;
use App\Models\Service;
use App\Models\Subservice;
use App\Models\User;
use App\Traits\DistanceTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService extends Service
{
    use DistanceTrait;
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
            'center:id,name,logo,rating,location_v,location_h',
            'manageSubservices:id,price',
        ])
            ->select('id', 'center_id', 'image', 'description', 'discount_value', 'from', 'to', 'discount_percentage')
            ->where('from', '<=', Carbon::now())
            ->where('to', '>=', Carbon::now())
            ->whereHas('center', function ($query) {
                $query->where('is_active', true);
            })
            ->get()
            ->map(function (Offer $offer) {
                $center = $offer->center ? $offer->center : null;
                $price = $offer->manageSubservices->sum('price') - $offer->discount_value;
                $distance = null;

                if (Auth::check() && $center) {
                    $distance = $this->calculateDistance(Auth::user(), $center);
                }
                return [
                    'id' => $offer->id,
                    'image' => $offer->image,
                    'description' => $offer->description,
                    'price' => $price,
                    'old_price' => $offer->manageSubservices->sum('price'),
                    'discount_percentage' => $offer->discount_percentage,
                    'from' => $offer->from,
                    'to' => $offer->to,
                    'distance' => $distance,
                    'center' => $center->only(['id', 'name', 'logo', 'rating']),
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
        return Center::active()->select('id', 'name', 'logo')->get();
    }

    protected function fetchSubservicesHasPoints()
    {
        $subservice = ManageSubservice::with([
            'center:id,name,logo,rating',
            'subservice:id,name,image',
        ])
            ->select('id', 'center_id', 'subservice_id', 'points', 'from', 'to')
            ->where('activating_points', 1)
            ->whereHas('center', function ($query) {
                $query->where('is_active', true);
            })
            ->where('from', '<=', Carbon::now())
            ->where('to', '>=', Carbon::now())
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

    public function getAllServices()
    {
        return Service::select('id', 'name', 'image', 'section_id')->with('section:id,name,image')->get();
    }

    public function getAllSections()
    {
        return Section::with('services')->select('id', 'name', 'image')->get();
    }

    public function getOffers()
    {
        return $this->fetchOffers();
    }


    public function adminHome()
    {
        $usersCount= User::count();
        $centersCount = Center::count();
        $servicesCount = Service::count();
        $sectionsCount = Section::count();
        $subservicesCount = Subservice::count();

        $sections = Section::select('id', 'name', 'image')->get();

        return [
            'users_count' => $usersCount,
            'centers_count' => $centersCount,
            'services_count' => $servicesCount,
            'sections_count' => $sectionsCount,
            'subservices_count' => $subservicesCount,
            'sections' => $sections,
        ];
    }
}
