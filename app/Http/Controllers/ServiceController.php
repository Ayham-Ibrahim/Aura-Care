<?php

namespace App\Http\Controllers;

use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use App\Services\ServiceService;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function index()
    {
        $services = $this->serviceService->getAllServices();
        return $this->success($services, 'تم الحصول على جميع الخدمات بنجاح');
    }

    public function store(StoreServiceRequest $request)
    {
        $service = $this->serviceService->createService($request->validated());
        return $this->createdResponse($service, 'تم انشاء الخدمة بنجاح');
    }

    // public function show(Service $service)
    // {
    //     return $this->success($service->load('section'), 'تم جلب الخدمة بنجاح');
    // }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $data = $this->serviceService->updateService($service, $request->validated());
        return $this->success($data, 'تم تعديل الخدمة بنجاح');
    }

    public function destroy(Service $service)
    {
        $this->serviceService->deleteService($service);
        return $this->success(null, 'تم حذف الخدمة بنجاح', 204);
    }
}
