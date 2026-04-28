<?php

namespace App\Services;

use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\Center\WorkFile;
use App\Models\Center\CenterDocument;
use App\Services\Center\WorkingHourService;
// use App\Models\ManageSubservice;
use App\Models\Subservice;
use App\Models\ManageSubservice;
use App\Models\Section;
use App\Models\Service as ServiceModel;
use App\Models\Offer;
use App\Models\User;
use App\Services\FileStorage;
use App\Traits\DistanceTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use function Symfony\Component\Clock\now;

class CenterService extends Service
{
    use DistanceTrait;
    protected $workingHourService;

    public function __construct(WorkingHourService $workingHourService)
    {
        $this->workingHourService = $workingHourService;
    }

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

    public function getPendingCenters($perPage = 10)
    {
        try {
            $centers = Center::select(
                'id',
                'name',
                'logo',
                'phone',
                'owner_name',
                'owner_number',
            )
                ->where('verification_status', 'pending')
                ->with(['documents' => function ($q) {
                    $q->select('center_id', 'updated_at');
                }])
                ->paginate($perPage);

            // return $centers;
            return $centers->through(function ($center) {
                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'logo' => $center->logo,
                    'phone' => $center->phone,
                    'owner_name' => $center->owner_name,
                    'owner_number' => $center->owner_number,
                    'request_date' => ($center->documents->updated_at)->format('Y-m-d') ?? null,
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching pending centers', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب المراكز المعلقة');
        }
    }

    public function getCenterByID(Center $center)
    {
        try {
            $center->load('section:id,name,image', 'services:id,name');
            return array_merge(
                $center->only([
                    'section_id',
                    'name',
                    'logo',
                    'location_h',
                    'location_v',
                    'phone',
                    'owner_name',
                    'owner_number',
                    'rating',
                    'verification_status',
                ]),
                [
                    'section' => $center->section,
                    'services' => $center->services->map(function ($service) {
                        return $service->only(['id', 'name']);
                    }),
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error fetching center details', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب بيانات المركز');
        }
    }

    public function getCenterDocuments(Center $center)
    {
        try {
            return $center->documents->only('commercial_record', 'id_back', 'id_front', 'center_id');
        } catch (\Exception $e) {
            Log::error('Error fetching center documents', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب وثائق المركز');
        }
    }

    public function acceptCenterDocuments(Center $center)
    {
        return $this->updateCenterVerificationStatus($center, 'accepted');
    }

    public function rejectCenterDocuments(Center $center)
    {
        return $this->updateCenterVerificationStatus($center, 'rejected');
    }

    public function toggleCenterActive(Center $center)
    {
        try {
            $center->update(['is_active' => !$center->is_active]);
            return $center;
        } catch (\Exception $e) {
            Log::error('Error toggling center active status', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل حالة المركز');
        }
    }

    protected function updateCenterVerificationStatus(Center $center, string $status)
    {
        try {
            $center->update(['verification_status' => $status]);
            return $center;
        } catch (\Exception $e) {
            Log::error('Error updating center verification status', ['center_id' => $center->id, 'status' => $status, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل حالة التحقق للمركز');
        }
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

                // also attach all subservices under those services with default inactive status
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

            // creating a center we need default working hours (24/7)
            $this->workingHourService->setDefaultForCenter($center);

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
            DB::beginTransaction();
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

                ManageSubservice::where('center_id', $center->id)->whereNotIn('subservice_id', function ($query) use ($data) {
                    $query->select('id')->from('subservices')->whereIn('service_id', $data['services']);
                })->delete();

                $subServices = Subservice::whereIn('service_id', $data['services'])->pluck('id')->toArray();
                foreach ($subServices as $subServiceId) {
                    ManageSubservice::firstOrCreate([
                        'center_id' => $center->id,
                        'subservice_id' => $subServiceId,
                    ]);
                }
            }
            DB::commit();

            return $center;
        } catch (\Exception $e) {
            DB::rollBack();
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

    /**
     * Retrieve payment information (sham code and image) for a given center.
     *
     * @param \App\Models\Center\Center $center
     * @return array
     */
    public function getPaymentInfCenter()
    {
        $center = Auth::guard('center')->user();
        // simply return the two columns; could be null if not set
        return $center->only(['sham_image', 'sham_code']);
    }


    public function updatePaymentInfCenter(array $data)
    {
        try {
            $center = Auth::guard('center')->user();
            $center->update([
                'sham_code' => $data['sham_code'] ?? $center->sham_code,
                'sham_image' => FileStorage::fileExists($data['sham_image'] ?? null, $center->sham_image, 'Center', 'img') ?? $center->sham_image,
            ]);
            return $center->only(['sham_image', 'sham_code']);
        } catch (\Exception $e) {
            Log::error('Error updating payment info for center', ['center_id' => $center->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل بيانات الدفع للمركز');
        }
    }

    /**
     * Get current center location.
     *
     * @return array
     */
    public function getCenterLocation()
    {
        $center = Auth::guard('center')->user();
        return $center->only(['location_h', 'location_v']);
    }


    public function updateCenterLocation(array $data)
    {
        try {
            $center = Auth::guard('center')->user();
            $center->update([
                'location_h' => $data['location_h'],
                'location_v' => $data['location_v'],
            ]);
            return $center;
        } catch (\Exception $e) {
            Log::error('Error updating center location', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل موقع المركز');
        }
    }

    /**
     * Fetch core profile information for authenticated center.
     *
     * @return array
     */
    public function getCenterProfileInfo()
    {
        $center = Auth::guard('center')->user();
        return $center->only([
            'verification_status',
            'rating',
            'phone',
            'name',
            'logo',
        ]);
    }

    /**
     * Summary of updateCenterLogo
     * @param mixed $data
     * @return \App\Models\User
     */
    public function updateCenterLogo($data)
    {
        try {
            $center = Auth::guard('center')->user();
            $center->update([
                'logo' => FileStorage::fileExists($data['logo'] ?? null, $center->logo, 'Center', 'img') ?? $center->logo,
            ]);
            return $center;
        } catch (\Exception $e) {
            Log::error('Error updating center logo', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل شعار المركز');
        }
    }

    /**
     * Return a simple listing of all centers with just id, name and logo.
     * No pagination or other extraneous columns. This complements
     * getAllCenters which is used by the main `index` action.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listCentersBasic()
    {
        return Center::active()
            ->select('id', 'name', 'logo')
            ->get();
    }

    /**
     * Fetch all managed subservices that have a points value defined.
     * Each record includes the points/from/to fields along with the
     * related center (id,name,logo) and subservice (id,name,image).
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSubservicesHasPoints()
    {
        try {
            return ManageSubservice::with([
                'center:id,name,logo',
                'subservice:id,name,image'
            ])
                ->select('id', 'center_id', 'subservice_id', 'points', 'from', 'to')
                ->where('activating_points', '=', 1)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching subservices with points', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب الخدمات الفرعية التي تحتوي على نقاط');
        }
    }
    public function getCenterSubservicesByService($service_id)
    {
        try {
            $center = Auth::guard('center')->user();
            $subservices = Subservice::with(['manageSubservices' => function ($query) use ($center) {
                $query->where('center_id', $center->id)->select('id', 'is_active', 'center_id', 'subservice_id');
            }])->where('service_id', $service_id)->get();
            // $subservices = Subservice::where('service_id', $service_id)->get();

            $subservices->each(function ($subservice) {
                $subservice->is_active = $subservice->manageSubservices->isNotEmpty()
                    ? $subservice->manageSubservices->first()->is_active
                    : 0;
                unset($subservice->manageSubservices);
            });
            return $subservices;
        } catch (\Exception $e) {
            Log::error('Error fetching subservices for center', ['center_id' => $center->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء جلب الخدمات الخاصة بالمركز');
        }
    }

    public function editSubservices($data)
    {
        $center = Auth::guard('center')->user();
        $manage_subservice = $center->manageSubservices()->where('subservice_id', $data['subservice_id'])->first();

        // if (!$manage_subservice) {
        //     $this->throwExceptionJson('الخدمة الفرعية غير موجودة للمركز');
        // }

        if (!$manage_subservice) {
            ManageSubservice::firstOrCreate([
                'center_id' => $center->id,
                'subservice_id' => $data['subservice_id'],
            ]);
            $manage_subservice = $center->manageSubservices()->where('subservice_id', $data['subservice_id'])->first();
        }

        try {
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

    public function storeCenterDocuments($data)
    {
        try {
            $center = Auth::guard('center')->user();
            DB::beginTransaction();
            $centerDocument = $center->documents()->updateorcreate([
                'center_id' => $center->id
            ], [
                'id_front' => isset($data['id_front']) ? FileStorage::storeFile($data['id_front'], 'CenterDocuments', 'img') : null,
                'id_back' => isset($data['id_back']) ? FileStorage::storeFile($data['id_back'], 'CenterDocuments', 'img') : null,
                'commercial_record' => isset($data['commercial_record']) ? FileStorage::storeFile($data['commercial_record'], 'CenterDocuments', 'img') : null,
            ]);
            $center->update(['verification_status' => 'pending']);
            DB::commit();
            return $centerDocument;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing center documents', ['error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تخزين وثائق المركز');
        }
    }

    public function getCentersByService(ServiceModel $service)
    {
        try {

            return Center::active()
                ->select('id', 'name', 'logo')
                ->whereHas('services', function ($q) use ($service) {
                    $q->where('service_id', $service->id);
                })
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching centers by service', ['service' => $service, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب المراكز الخاصة بالخدمة');
        }
    }

    public function getCentersBySection(Section $section)
    {
        try {

            return Center::active()
                ->select('id', 'name', 'logo')
                ->where('section_id', $section->id)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching centers by section', ['section' => $section, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب المراكز الخاصة بالقسم');
        }
    }

    public function getCentersBySubservice(Subservice $subservice)
    {
        try {
            $user = Auth::guard('api')->user();
            $subservices = ManageSubservice::select('id', 'price', 'subservice_id', 'center_id')
                ->whereHas('center', function ($q) {
                    $q->active();
                })
                ->where('subservice_id', $subservice->id)
                ->where('is_active', 1)
                ->with([
                    'center:id,name,logo,rating,location_h,location_v',
                    'offers' => function ($query) {
                        $query->with('manageSubservices')->select('offers.id as id', 'from', 'to')
                            ->where('from', '<=', Carbon::now())
                            ->where('to', '>=', Carbon::now())
                        ;
                    }


                ])
                ->get();
            // return $subservices;

            return $subservices->map(function (ManageSubservice $manageSubservice) use ($user) {


                //offer
                $offer = $manageSubservice->offers->map(function ($offer) use ($manageSubservice) {
                    $count = $offer->manageSubservices->count();
                    if($count == 1) {
                        return $offer;
                    }
                })->filter()->first();
                $has_offer = false;
                if ($offer) {
                    $has_offer = true;
                }

                $center = $manageSubservice->center;
                $distance = null;
                if ($user && $center) {
                    $distance = $this->calculateDistance($user, $center);
                }

                return [
                    'id' => $manageSubservice->id,
                    'price' => $manageSubservice->price,
                    'subservice_id' => $manageSubservice->subservice_id,
                    'center_id' => $manageSubservice->center_id,
                    'distance_km' => $distance,
                    'has_offer' => $has_offer,
                    'offer_id' => $has_offer ? $offer->id : null,
                    'center' => $center->only(['id', 'name', 'logo', 'rating']),
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error fetching centers by subservice', ['subservice' => $subservice, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء جلب المراكز الخاصة بالخدمة الفرعية');
        }
    }
}
