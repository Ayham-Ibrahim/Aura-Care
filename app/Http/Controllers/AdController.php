<?php

namespace App\Http\Controllers;

use App\Http\Requests\Ad\StoreAdRequest;
use App\Http\Requests\Ad\UpdateAdRequest;
use App\Models\Ad;
use App\Services\AdService;

class AdController extends Controller
{
    protected $adService;

    public function __construct(AdService $adService)
    {
        $this->adService = $adService;
    }

    public function index()
    {
        $ads = $this->adService->getAllAds();
        return $this->success($ads, 'تم الحصول على جميع الاعلانات بنجاح');
    }

    public function store(StoreAdRequest $request)
    {
        $ad = $this->adService->createAd($request->validated());
        return $this->createdResponse($ad, 'تم انشاء الاعلان بنجاح');
    }

    public function show(Ad $ad)
    {
        return $this->success($ad, 'تم جلب الاعلان بنجاح');
    }

    public function update(UpdateAdRequest $request, Ad $ad)
    {
        $data = $this->adService->updateAd($ad, $request->validated());
        return $this->success($data, 'تم تعديل الاعلان بنجاح');
    }

    public function destroy(Ad $ad)
    {
        $this->adService->deleteAd($ad);
        return $this->success(null, 'تم حذف الاعلان بنجاح', 204);
    }
}
