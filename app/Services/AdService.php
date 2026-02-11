<?php

namespace App\Services;

use App\Models\Ad;
use Illuminate\Support\Facades\Log;

class AdService extends Service
{
    public function getAllAds()
    {
        return Ad::select('id', 'image')->get();
    }

    public function createAd(array $data)
    {
        try {
            $ad = Ad::create([
                'image' => FileStorage::storeFile($data['image'], 'Ad', 'img'),
            ]);

            return $ad;
        } catch (\Exception $e) {
            Log::error('Error creating ad', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء انشاء الاعلان');
        }
    }

    public function updateAd(Ad $ad, array $data)
    {
        try {
            $ad->update([
                'image' => FileStorage::fileExists(
                    $data['image'] ?? null,
                    $ad->image,
                    'Ad',
                    'img'
                ) ?? $ad->image,
            ]);

            return $ad;
        } catch (\Exception $e) {
            Log::error('Error updating ad', ['ad_id' => $ad->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل الاعلان');
        }
    }

    public function deleteAd(Ad $ad): void
    {
        try {
            FileStorage::deleteFile($ad->image);
            $ad->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting ad', ['ad_id' => $ad->id ?? null, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف الاعلان');
        }
    }
}
