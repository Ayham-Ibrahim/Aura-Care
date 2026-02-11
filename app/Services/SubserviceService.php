<?php

namespace App\Services;

use App\Models\Subservice;
use Illuminate\Support\Facades\Log;

class SubserviceService extends Service
{
    public function getAllSubservices()
    {
        return Subservice::select('id', 'name', 'image', 'service_id')->with('service:id,name')->get();
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
}
