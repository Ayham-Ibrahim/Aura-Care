<?php

namespace App\Services\Center;

use App\Models\ManageSubservice;
use App\Models\Offer;
use App\Services\FileStorage;
use App\Services\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfferService extends Service
{
    /**
     * Get all offers for the authenticated center.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOffersForCenter()
    {
        $centerId = Auth::guard('center')->user()->id;
        // return $centerId;
        $offers = Offer::where('center_id', $centerId)
            ->select('id', 'discount_value', 'discount_percentage', 'from', 'to')
            ->with(['manageSubservices' => function ($query) {
                $query
                    ->select('manage_subservices.id', 'subservice_id', 'price') // جلب الحقول المطلوبة فقط
                    ->with('subservice:id,name,image');
            }])
            ->get()
            ->map(function ($offer) {
                $offer->subservices = $offer->manageSubservices
                    ->pluck('subservice')
                    ->filter()
                    ->values();

                unset($offer->manageSubservices);

                return $offer;
            });
        return $offers;
    }

    /**
     * Get active subservices for the authenticated center.
     *
     * Returns subservice id/name/image along with managed subservice price.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getActiveSubservices()
    {
        $centerId = Auth::guard('center')->user()->id;

        return ManageSubservice::where('center_id', $centerId)
            ->where('is_active', 1)
            ->with('subservice:id,name,image')
            ->select('id', 'subservice_id', 'price')
            ->get()
            ->map(function ($manageSubservice) {
                $subservice = $manageSubservice->subservice;

                return [
                    'id' => $subservice->id,
                    'name' => $subservice->name,
                    'image' => $subservice->image,
                    'price' => $manageSubservice->price,
                ];
            })
            ->values();
    }

    /**
     * Create a new offer for the authenticated center.
     *
     * @param array $data
     * @return \App\Models\Offer
     */
    public function createOffer(array $data)
    {
        $centerId = Auth::guard('center')->user()->id;

        $subserviceIds = $data['subservice'] ?? [];

        $manageSubservice = ManageSubservice::where('center_id', $centerId)
            ->whereIn('subservice_id', $subserviceIds)
            ->where('is_active', 1)
            ->select('id', 'subservice_id', 'price')
            ->get();

        if (count($manageSubservice) !== count(array_unique($subserviceIds))) {
            $this->throwExceptionJson('بعض الخدمات الفرعية غير موجودة أو غير مرتبطة بالمركز', 422);
        }

        try {
            DB::beginTransaction();

            $offer = Offer::create([
                'center_id' => $centerId,
                'discount_percentage' => $data['discount_percentage'],
                'discount_value' => array_sum($manageSubservice->pluck('price')->toArray()) * ($data['discount_percentage'] / 100),
                'from' => $data['from'],
                'to' => $data['to'],
                'description' => $data['description'] ?? null,
                'image' => FileStorage::storeFile($data['image'], 'Offer', 'img') ?? null,
            ]);

            $offer->manageSubservices()->sync($manageSubservice->pluck('id')->toArray());

            DB::commit();

            return $offer;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating offer', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إنشاء العرض');
        }
    }

    /**
     * Delete an offer owned by the authenticated center.
     *
     * @param  \App\Models\Offer  $offer
     * @return void
     */
    public function deleteOffer(Offer $offer)
    {
        $centerId = Auth::guard('center')->user()->id;

        if ($offer->center_id !== $centerId) {
            $this->throwExceptionJson('غير مسموح لك بحذف هذا العرض', 403);
        }

        try {
            DB::beginTransaction();

            if ($offer->image) {
                FileStorage::deleteFile($offer->image);
            }

            $offer->manageSubservices()->detach();
            $offer->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting offer', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف العرض');
        }
    }
}
