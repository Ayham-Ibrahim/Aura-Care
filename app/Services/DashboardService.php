<?php

namespace App\Services;

use App\Enums\CenterSortType;
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
use Illuminate\Support\Facades\Log;

class DashboardService extends Service
{
    use DistanceTrait;
    /**
     * Get home page dashboard data and cache it in database store.
     *
     * @return array
     */
    public function getHomePageData($search = null , $filter = null): array
    {
        return [
            'offers' => $this->fetchOffers($search),
            'ads' => $this->fetchAds(),
            'services' => $this->fetchServices($search),
            'centers' => $this->fetchCenters($search, $filter),
            'sub_services_has_points' => $this->fetchSubservicesHasPoints($search),
        ];
    }

    protected function fetchOffers($search = null)
    {
        try {
            // return Offer::all();
            return Offer::with([
                'center:id,name,logo,rating,location_v,location_h',
                'manageSubservices:id,price',
            ])
                ->select('id', 'center_id', 'image', 'description', 'discount_value', 'from', 'to', 'discount_percentage')
                ->where('from', '<=', Carbon::now())
                ->where('to', '>=', Carbon::now())
                ->when($search, function ($query) use ($search) {
                    $query->whereHas('center', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                })
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
                    } else {
                        $distance = (float) 0;
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
        } catch (\Exception $e) {
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب المراكز الخاصة بالقسم');
        }
    }

    protected function fetchAds()
    {
        return Ad::select('id', 'image')->get();
    }

    protected function fetchServices($search = null)
    {
        return Service::select('id', 'name', 'image')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })->get();
    }

    protected function fetchCenters($search = null, $filter = null)
    {
        $centers = Center::active()->when($search, function ($query) use ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        });

        if ($filter == CenterSortType::CLOSEST->value) {
            $centers = $centers->get()
            ->map(function (Center $center) {
                $distance = 0;
                if (Auth::check()) {
                    $distance = $this->calculateDistance(Auth::user(), $center);
                } else {
                    $distance = (float) 0;
                }
                $center->distance = $distance;
                return $center
                ;
            })->sortBy('distance')->values()
            ;
        } elseif ($filter == CenterSortType::HAS_OFFERS->value) {
            $centers = $centers->whereHas('offers', function ($query) {
                $query->where('from', '<=', Carbon::now())
                    ->where('to', '>=', Carbon::now());
            })->get();
        } elseif ($filter == CenterSortType::HIGHEST_RATING->value) {
            $centers = $centers->orderByDesc('rating')->get();
        } else {
            $centers = $centers->get();
        }

        return $centers->map(function (Center $center) {
            if (Auth::check()) {
                $distance = $this->calculateDistance(Auth::user(), $center);
            } else {
                $distance = (float) 0;
            }
            return [
                'id' => $center->id,
                'name' => $center->name,
                'logo' => $center->logo,
                'rating' => $center->rating,
                'distance' => $distance,
            ];
        });
    }

    protected function fetchSubservicesHasPoints($search = null)
    {
        $subservice = ManageSubservice::with([
            'center',
            'subservice:id,name,image',
        ])
            ->select('id', 'center_id', 'subservice_id', 'points', 'from', 'to')
            ->when($search, function ($query) use ($search) {
                $query
                    ->WhereHas('subservice', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                // ->orwhereHas('center', function ($query) use ($search) {
                //     $query->where('name', 'like', '%' . $search . '%');
                // })
            })
            ->where('activating_points', 1)
            ->whereHas('center', function ($query) {
                $query->where('is_active', true);
            })
            ->where('from', '<=', Carbon::now())
            ->where('to', '>=', Carbon::now())->orderByDesc('from')
            ->get();

        return $subservice->map(function (ManageSubservice $manageSubservice) {
            $center = $manageSubservice->center ? $manageSubservice->center : null;
            $subservice = $manageSubservice->subservice ? $manageSubservice->subservice->only(['id', 'name', 'image']) : null;
            $distance = 0;
            if (Auth::check() && $center) {
                $distance = $this->calculateDistance(Auth::user(), $center);
            } else {
                $distance = (float) 0;
            }

            return [
                'id' => $manageSubservice->id,
                'name' => $subservice['name'],
                'image' => $subservice['image'],
                'points' => $manageSubservice->points,
                'from' => $manageSubservice->from,
                'to' => $manageSubservice->to,
                'distance' => $distance,
                'center' => $center->only(['id', 'name', 'logo', 'rating']),
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
        return $this->fetchOffers($search = null);
    }


    public function adminHome()
    {
        $usersCount = User::count();
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
