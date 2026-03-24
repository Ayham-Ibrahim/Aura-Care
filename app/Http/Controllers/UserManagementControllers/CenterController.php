<?php

namespace App\Http\Controllers\UserManagementControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\StoreWorkRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Requests\Center\UpdateCenterSubsevrice;
use App\Http\Requests\Center\UpdateCenterPaymentInfoRequest;
use App\Http\Requests\Center\StoreCenterDocumentsRequest;
use App\Http\Requests\Center\UpdateCenterLocation;
use App\Http\Requests\Center\UpdateCenterLogoRequest;
use App\Models\Center\Center;
use App\Models\Section;
use App\Models\Service;
use App\Services\CenterService;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    protected $centerService;

    public function __construct(CenterService $centerService)
    {
        $this->centerService = $centerService;
    }

    public function index(Request $request)
    {
        $centers = $this->centerService->getAllCenters($request->perPage);
        return $this->paginate($centers, 'تم الحصول على جميع المراكز بنجاح');
    }

    public function store(StoreCenterRequest $request)
    {
        $center = $this->centerService->createCenter($request->validated());
        return $this->createdResponse($center, 'تم انشاء المركز بنجاح');
    }

    public function show(Center  $center)
    {
        $center->load('section:id,name','services:id,name');
        return $this->success($center, 'تم جلب المركز بنجاح');
    }

    public function update(UpdateCenterRequest $request, Center $center)
    {
        $data = $this->centerService->updateCenter($center, $request->validated());
        return $this->success($data, 'تم تعديل المركز بنجاح');
    }

    public function destroy(Center $center)
    {
        $this->centerService->deleteCenter($center);
        return $this->success(null, 'تم حذف المركز بنجاح', 204);
    }
    
    public function getWorks(Center $center)
    {
        $works = $this->centerService->getCenterWorks($center);
        return $this->success($works, 'تم الحصول على أعمال المركز بنجاح');
    }
    // public function restore($id)
    // {
    //     $center = $this->centerService->restoreCenter($id);
    //     return $this->success($center, 'تم استعادة المركز بنجاح');
    // }

    public function getServices()
    {
        $services = $this->centerService->getCenterServices();
        return $this->success($services, 'تم الحصول على خدمات المركز بنجاح');
    }

    public function showSubservicesByService($sevice_id)
    {
        $subservices = $this->centerService->getCenterSubservicesByService($sevice_id);
        return $this->success($subservices, 'تم الحصول على خدمات المركز الفرعية بنجاح');
    }

    public function updateSubservice(UpdateCenterSubsevrice $request)
    {
        $subservices = $this->centerService->editSubservices($request->validated());
        return $this->success($subservices, 'تم تعديل الخدمة بنجاح');
    }

    public function getSubservicesById($subservice_id)
    {
        $subservices = $this->centerService->subservicesById($subservice_id);
        if(!$subservices){
            return $this->notFoundResponse('الخدمة الفرعية غير موجودة');
        }
        return $this->success($subservices, 'تم الحصول على الخدمات الفرعية بنجاح');
    }

    /**
     * Return payment information (sham code/image) for a center.
     */
    public function getPaymentInfCenter()
    {
        $data = $this->centerService->getPaymentInfCenter();
        return $this->success($data, 'تم الحصول على بيانات الدفع بنجاح');
    }

    /**
     * Update sham code/image for a center.
     */
    public function updatePaymentInfCenter(UpdateCenterPaymentInfoRequest $request)
    {
        $data = $this->centerService->updatePaymentInfCenter($request->validated());
        return $this->success($data, 'تم تحديث بيانات الدفع بنجاح');
    }

    /**
     * Store or update the three required images and mark verification pending.
     */
    public function uploadDocuments(StoreCenterDocumentsRequest $request)
    {
        $docs = $this->centerService->storeCenterDocuments($request->validated());
        return $this->success($docs, 'تم رفع الوثائق بنجاح');
    }

    /**
     * Get the current authenticated center's geographic location.
     */
    public function getCenterLocation()
    {
        $loc = $this->centerService->getCenterLocation();
        return $this->success($loc, 'تم الحصول على موقع المركز بنجاح');
    }

    /**
     * Return a small set of profile fields for the authenticated center.
     */
    public function centerProfileInfo()
    {
        $data = $this->centerService->getCenterProfileInfo();
        return $this->success($data, 'تم الحصول على بيانات الملف الشخصي للمركز بنجاح');
    }

    /**
     * Update just the center logo.
     */
    public function updateCenterLogo(UpdateCenterLogoRequest $request)
    {
        $center = $this->centerService->updateCenterLogo($request->validated());
        return $this->success($center->only(['logo']), 'تم تحديث شعار المركز بنجاح');
    }

    /**
     * Update the authenticated center's coordinates.
     */
    public function updateCenterLocation(UpdateCenterLocation $request)
    {
        $center = $this->centerService->updateCenterLocation($request->validated());
        return $this->success($center->only(['location_h','location_v']), 'تم تحديث موقع المركز بنجاح');
    }

    /**
     * Administrator helper – simple center listing.
     */
    public function listBasicCenters()
    {
        $centers = $this->centerService->listCentersBasic();
        return $this->success($centers, 'تم جلب قائمة المراكز الأساسية بنجاح');
    }

    /**
     * Administrator helper – list centers pending verification.
     */
    public function getPendingCenters(Request $request)
    {
        $centers = $this->centerService->getPendingCenters($request->per_Page);
        return $this->paginate($centers, 'تم جلب المراكز المعلقة بنجاح');
    }

    /**
     * Administrator helper – get center details with section and services.
     */
    public function getCenterByID(Center $center)
    {
        $data = $this->centerService->getCenterByID($center);
        return $this->success($data, 'تم جلب بيانات المركز بنجاح');
    }

    /**
     * Administrator helper – get center documents.
     */
    public function getCenterDocuments(Center $center)
    {
        $documents = $this->centerService->getCenterDocuments($center);
        return $this->success($documents, 'تم جلب وثائق المركز بنجاح');
    }

    /**
     * Administrator helper – accept center documents.
     */
    public function acceptCenterDocuments(Center $center)
    {
        $center = $this->centerService->acceptCenterDocuments($center);
        return $this->success($center->only(['id', 'verification_status']), 'تم قبول وثائق المركز');
    }

    /**
     * Administrator helper – reject center documents.
     */
    public function rejectCenterDocuments(Center $center)
    {
        $center = $this->centerService->rejectCenterDocuments($center);
        return $this->success($center->only(['id', 'verification_status']), 'تم رفض وثائق المركز');
    }

    /**
     * Administrator helper – all managed subservices that have points.
     */
    public function getSubservicesHasPoints()
    {
        $data = $this->centerService->getSubservicesHasPoints();
        return $this->success($data, 'تم جلب الخدمات الفرعية المحتوية على نقاط بنجاح');
    }

    

    /**
     * Return centers that offer the specified service. Used by
     * authenticated users during service exploration.
     */
    public function getCentersByService(Service $service)
    {
        $centers = $this->centerService->getCentersByService($service);
        return $this->success($centers, 'تم جلب المراكز التي تقدم الخدمة بنجاح');
    }

    public function getCentersBySection(Section $section)
    {
        $centers = $this->centerService->getCentersBySection($section);
        return $this->success($centers, 'تم جلب المراكز التي تنتمي للقسم بنجاح');
    }
}
