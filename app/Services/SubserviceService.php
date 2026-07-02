<?php

namespace App\Services;

use App\Models\ManageSubservice;
use App\Models\Subservice;
use App\Models\Service;
use Illuminate\Support\Facades\Log;

class SubserviceService extends Service
{
    public function getAllSubservices()
    {
        return Subservice::select('id', 'name', 'image', 'service_id')->with('service:id,name')->get();
    }

    /**
     * Return subservices grouped by their parent main service
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSubservicesGroupedByService()
    {
        return Service::select('id', 'name', 'image')
            ->with(['subservices' => function ($q) {
                $q->select('id', 'name', 'image', 'service_id')->orderBy('name');
            }])
            ->orderBy('name')
            ->get();
    }

    public function createSubservice(array $data)
    {
        try {
            $subservice = Subservice::create([
                'name' => $data['name'],
                'service_id' => $data['service_id'],
                'image' => FileStorage::storeFile($data['image'], 'Subservice', 'img'),
            ]);

            return $subservice;
        } catch (\Exception $e) {
            Log::error('Error creating subservice', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء انشاء الخدمة الفرعية');
        }
    }

    public function updateSubservice(Subservice $subservice, array $data)
    {
        try {
            $subservice->update([
                'name' => $data['name'] ?? $subservice->name,
                'service_id' => $data['service_id'] ?? $subservice->service_id,
                'image' => FileStorage::fileExists(
                    $data['image'] ?? null,
                    $subservice->image,
                    'Subservice',
                    'img'
                ) ?? $subservice->image,
            ]);

            return $subservice;
        } catch (\Exception $e) {
            Log::error('Error updating subservice', ['subservice_id' => $subservice->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل الخدمة الفرعية');
        }
    }

    public function deleteSubservice(Subservice $subservice): void
    {
        try {
            FileStorage::deleteFile($subservice->image);
            $subservice->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting subservice', ['subservice_id' => $subservice->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف الخدمة الفرعية');
        }
    }

    public function deleteMultipleSubservices($data): void
    {
        try {
            $subservices = Subservice::whereIn('id', $data['ids'])->get();

            foreach ($subservices as $subservice) {
                $this->deleteSubservice($subservice);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting multiple subservices', ['subservice_ids' => $data['ids'], 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف الخدمات الفرعية');
        }
    }

    public function getSubservicesByServiceForUser($serviceId)
    {
        return Subservice::where('service_id', $serviceId)
            ->select('id', 'name', 'image')
            ->get();
    }

    public function getSubservicesByIdForUser(ManageSubservice $manageSubservice)
    {
        $manageSubservice->load(['subservice:id,name,image', 'images', 'offers' => function ($query) {
            $query->with('manageSubservices')->where('from', '<=', now())
                ->where('to', '>=', now());
        }]);
        $offer = $manageSubservice->offers->map(function ($offer) use ($manageSubservice) {
            $count = $offer->manageSubservices->count();
            if ($count == 1) {
                return $offer;
            }
        })->filter()->first();
        $has_offer = false;
        if ($offer) {
            $has_offer = true;
        }

        $has_points = $manageSubservice->activating_points && $manageSubservice->points > 0 && $manageSubservice->from <= now() && $manageSubservice->to >= now();
        return [
            'id' => $manageSubservice->id,
            'name' => $manageSubservice->subservice->name,
            'price' => $manageSubservice->price,
            'is_active' => $manageSubservice->is_active,
            'has_points' => $has_points,
            'activating_points' => $manageSubservice->activating_points,
            'points' => $manageSubservice->points,
            'from' => $manageSubservice->from,
            'to' => $manageSubservice->to,
            'image' => $manageSubservice->image ?? $manageSubservice->subservice->image,
            'description' => $manageSubservice->description,
            'completion_time' => $manageSubservice->completion_time,
            'equipment' => $manageSubservice->equipment,
            'has_offer' => $has_offer,
            'offer' => $has_offer ? [
                'id' => $offer->id,
                'discount_value' => $offer->discount_value,
                'from' => $offer->from,
                'to' => $offer->to,
                'image' => $offer->image,
                'description' => $offer->description,
            ] : null,
            'images' => $manageSubservice->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'image' => $image->image,
                ];
            }),
        ];
    }
}
