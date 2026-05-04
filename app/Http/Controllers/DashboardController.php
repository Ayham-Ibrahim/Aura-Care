<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dashboard\HomeRequest;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(HomeRequest $request)
    {
        $data = $this->dashboardService->getHomePageData($request->search,$request->filter);
        return $this->success($data, 'تم جلب بيانات الصفحة الرئيسية بنجاح');
    }

    public function getAllsevices()
    {
        $services = $this->dashboardService->getAllServices();
        return $this->success($services, 'تم جلب جميع الخدمات بنجاح');
    }

    public function getAllSection()
    {
        $sections = $this->dashboardService->getAllSections();
        return $this->success($sections, 'تم جلب جميع الأقسام بنجاح');
    }

    public function getOffers()
    {
        $offers = $this->dashboardService->getOffers();
        return $this->success($offers, 'تم جلب العروض بنجاح');
    }

    public function adminHome()
    {
        $data = $this->dashboardService->adminHome();
        return $this->success($data, 'تم جلب بيانات الصفحة الرئيسية للمشرف بنجاح');
    }
}

