<?php

namespace App\Http\Controllers\UserManagementControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\StoreWorkRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Requests\Center\UpdateCenterSubsevrice;
use App\Models\Center\Center;
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
        return $this->success($subservices, 'تم الحصول على خدمات المركز بنجاح');
    }

    public function updateSubservice(UpdateCenterSubsevrice $request)
    {
        $subservices = $this->centerService->editSubservices($request->validated());
        return $this->success($subservices, 'تم الحصول على خدمات المركز بنجاح');
    }
}
