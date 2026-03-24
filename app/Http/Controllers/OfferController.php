<?php

namespace App\Http\Controllers;

use App\Http\Requests\Center\StoreOfferRequest;
use App\Models\Offer;
use App\Services\Center\OfferService;

class OfferController extends Controller
{

    protected $offerService;
    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }
    /**
     * Display a listing of offers for a given center.
     *
     * Accepts either a center_id query parameter or assumes
     * the authenticated user's associated center if available.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $offers = $this->offerService->getOffersForCenter();
        return $this->success($offers, 'تم جلب العروض بنجاح');
    }

    /**
     * Store a new offer for the authenticated center.
     */
    public function storeOffer(StoreOfferRequest $request)
    {
        $offer = $this->offerService->createOffer($request->validated());
        return $this->success($offer, 'تم إنشاء العرض بنجاح', 201);
    }

    /**
     * Delete an offer for the authenticated center.
     */
    public function destroyOffer(Offer $offer)
    {
        $this->offerService->deleteOffer($offer);
        return $this->success(null, 'تم حذف العرض بنجاح', 204);
    }

    /**
     * Return active subservices for the authenticated center.
     *
     * Each item includes the subservice (id, name, image) and the
     * managed subservice price used by the center.
     */
    public function getActiveSubservice()
    {
        $subservices = $this->offerService->getActiveSubservices();
        return $this->success($subservices, 'تم جلب الخدمات الفرعية النشطة بنجاح');
    }
}
