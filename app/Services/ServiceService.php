<?php

namespace App\Services;

use App\Models\Service;
use App\Services\Service as ServicesService;
use Illuminate\Support\Facades\Log;

class ServiceService extends ServicesService
{
    public function getAllServices()
    {
        return Service::select('id', 'name', 'image', 'section_id')->with('section:id,name')->get();
    }

    public function createService(array $data)
    {
        try {
            $service = Service::create([
                'name' => $data['name'],
                'section_id' => $data['section_id'],
                'image' => FileStorage::storeFile($data['image'], 'Service', 'img'),
            ]);

            return $service;
        } catch (\Exception $e) {
            Log::error('Error creating service', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء انشاء الخدمة');
        }
    }

    public function updateService(Service $service, array $data)
    {
        try {
            $service->update([
                'name' => $data['name'] ?? $service->name,
                'section_id' => $data['section_id'] ?? $service->section_id,
                'image' => FileStorage::fileExists(
                    $data['image'] ?? null,
                    $service->image,
                    'Service',
                    'img'
                ) ?? $service->image,
            ]);

            return $service;
        } catch (\Exception $e) {
            Log::error('Error updating service', ['service_id' => $service->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل الخدمة');
        }
    }

    public function deleteService(Service $service): void
    {
        try {
            // delete files for related subservices first
            foreach ($service->subservices as $subservice) {
                FileStorage::deleteFile($subservice->image);
                $subservice->delete();
            }

            FileStorage::deleteFile($service->image);
            $service->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting service', ['service_id' => $service->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف الخدمة');
        }
    }

      public function deleteMultipleServices($data): void
    {
        try {
            $services = Service::whereIn('id', $data['ids'])->get();

            foreach ($services as $service) {
                $this->deleteService($service);
            }
        } catch (\Exception $e) {
            Log::error('Error deleting multiple services', ['service_ids' => $data['ids'], 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف الخدمات');
        }
    }

    
    /**
     * Summary of getServicesBySection
     * @param mixed $section
     */
    public function getServicesBySection($section)
    {
        try {
            // return $section_id;
            $services = Service::where('section_id', $section->id)->get();
            return $services;
        } catch (\Exception $e) {
            Log::error('Error fetching services by section', ['section_id' => $section->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الخدمات الخاصة بالقسم');
        }
    }
}
     

