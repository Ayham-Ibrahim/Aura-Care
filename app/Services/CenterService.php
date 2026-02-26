<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\Center\WorkFile;
// use App\Models\ManageSubservice;
use App\Models\Subservice;
use App\Models\ManageSubservice;
use App\Models\Service as ServiceModel;
use App\Services\FileStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CenterService extends Service
{
    public function getAllCenters($perPage = 10)
    {
        return Center::select(
            'id',
            'name',
            'logo',
            'phone',
            'owner_name',
            'owner_number',
            'rating',
            // 'section_id',
            // 'location_h',
            // 'location_v',
            // 'reliable',
            // 'sham_image',
            // 'sham_code'
        )
            // ->with('section:id,name', 'services:id,name')
            ->paginate($perPage);
    }

    public function createCenter(array $data)
    {
        try {
            DB::beginTransaction();
            $center = Center::create([
                'section_id' => $data['section_id'],
                'name' => $data['name'],
                'logo' => isset($data['logo']) ? FileStorage::storeFile($data['logo'], 'Center', 'img') : null,
                'location_h' => $data['location_h'],
                'location_v' => $data['location_v'],
                'phone' => $data['phone'],
                'password' => Hash::make($data['password']),
                'owner_name' => $data['owner_name'],
                'owner_number' => $data['owner_number'],
            ]);

            // attach services if provided (array of ids)
            if (!empty($data['services']) && is_array($data['services'])) {
                $center->services()->sync($data['services']);

                $subServices = Subservice::whereIn('service_id', $data['services'])->pluck('id')->toArray();
                ManageSubservice::insert(
                    array_map(function ($subServiceId) use ($center) {
                        return [
                            'center_id' => $center->id,
                            'subservice_id' => $subServiceId,
                        ];
                    }, $subServices)
                );
            }
            DB::commit();

            return $center;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating center', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء انشاء المركز');
        }
    }

    public function updateCenter(Center $center, array $data)
    {
        try {
            $center->update([
                'section_id' => $data['section_id'] ?? $center->section_id,
                'name' => $data['name'] ?? $center->name,
                'logo' => FileStorage::fileExists($data['logo'] ?? null, $center->logo, 'Center', 'img') ?? $center->logo,
                'location_h' => $data['location_h'] ?? $center->location_h,
                'location_v' => $data['location_v'] ?? $center->location_v,
                'phone' => $data['phone'] ?? $center->phone,
                'password' => isset($data['password']) ? Hash::make($data['password']) : $center->password,
                'owner_name' => $data['owner_name'] ?? $center->owner_name,
                'owner_number' => $data['owner_number'] ?? $center->owner_number,
            ]);

            if (isset($data['services']) && is_array($data['services'])) {
                $center->services()->sync($data['services']);
            }

            return $center;
        } catch (\Exception $e) {
            Log::error('Error updating center', ['center_id' => $center->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('1حدث خطأ ما أثناء تعديل المركز');
        }
    }

    public function deleteCenter(Center $center): void
    {
        try {
            // delete attached services pivot first (cascade db will handle but detach just in case)
            $center->services()->detach();

            FileStorage::deleteFile($center->logo);
            FileStorage::deleteFile($center->sham_image);

            $center->delete(); // soft delete
        } catch (\Exception $e) {
            Log::error('Error deleting center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف المركز');
        }
    }

    public function getCenterWorks(Center $center)
    {
        try {
            $works = $center->works()->with(['service:id,name', 'files'])->get();
            return $works;
        } catch (\Exception $e) {
            Log::error('Error fetching works for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الأعمال الخاصة بالمركز');
        }
    }

    // public function restoreCenter($id)
    // {
    //     try {
    //         $center = Center::withTrashed()->findOrFail($id);
    //         $center->restore();
    //         return $center;
    //     } catch (\Exception $e) {
    //         Log::error('Error restoring center', ['center_id' => $id, 'error' => $e->getMessage()]);
    //         $this->throwExceptionJson('حدث خطأ ما أثناء استعادة المركز');
    //     }
    // }

    public function getCenterServices()
    {
        try {
            $center = Auth::guard('center')->user();
            $services = ServiceModel::whereHas('centers', function ($query) use ($center) {
                $query->where('center_id', $center->id);
            })->select('id', 'name', 'image')
                ->get();
            return $services;
        } catch (\Exception $e) {
            Log::error('Error fetching services for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء جلب الخدمات الخاصة بالمركز');
        }
    }

    public function getCenterSubservicesByService($service_id)
    {
        try {
            $center = Auth::guard('center')->user();
            $subservices = Subservice::where('service_id', $service_id)->get(); 
            return $subservices;
        } catch (\Exception $e) {
            Log::error('Error fetching subservices for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء جلب الخدمات الخاصة بالمركز');
        }
    }

    public function editSubservices($data)
    {
        try {
            $center = Auth::guard('center')->user();
            $manage_subservice = $center->manageSubservices()->where('subservice_id', $data['subservice_id'])->firstOrFail();
            $manage_subservice->update([
                'price' => $data['price'] ?? $manage_subservice->price,
                'is_active' => $data['is_active'] ?? $manage_subservice->is_active,
                'activating_points' => $data['activating_points'] ?? $manage_subservice->activating_points,
                'points' => $data['points'] ?? $manage_subservice->points,
                'from' => $data['from'] ?? $manage_subservice->from,
                'to' => $data['to'] ?? $manage_subservice->to,
            ]);
            return $manage_subservice->load('subservice:id,name,image');
        } catch (\Exception $e) {
            Log::error('Error editing subservices for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء تعديل الخدمات الخاصة بالمركز');
        }
    }

    public function subservicesById($subservice_id)
    {
        try {
            $center = Auth::guard('center')->user();
            $manage_subservice = $center->manageSubservices()->where('subservice_id', $subservice_id)->first();
            return $manage_subservice->load('subservice:id,name,image');
        } catch (\Exception $e) {
            Log::error('Error fetching subservice by id for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء جلب الخدمة الخاصة بالمركز');
        }
    }
}
