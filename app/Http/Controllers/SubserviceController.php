<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subservice\multipleDeleteSubserviceRequest;
use App\Http\Requests\Subservice\StoreSubserviceRequest;
use App\Http\Requests\Subservice\UpdateSubserviceRequest;
use App\Models\Subservice;
use App\Services\SubserviceService;

class SubserviceController extends Controller
{
    protected $subserviceService;

    public function __construct(SubserviceService $subserviceService)
    {
        $this->subserviceService = $subserviceService;
    }

    public function index()
    {
        $subservices = $this->subserviceService->getAllSubservices();
        return $this->success($subservices, 'تم الحصول على جميع الخدمات الفرعية بنجاح');
    }

    public function store(StoreSubserviceRequest $request)
    {
        $subservice = $this->subserviceService->createSubservice($request->validated());
        return $this->createdResponse($subservice, 'تم انشاء الخدمة الفرعية بنجاح');
    }

    // public function show(Subservice $subservice)
    // {
    //     return $this->success($subservice, 'تم جلب الخدمة الفرعية بنجاح');
    // }

    public function update(UpdateSubserviceRequest $request, Subservice $subservice)
    {
        $data = $this->subserviceService->updateSubservice($subservice, $request->validated());
        return $this->success($data, 'تم تعديل الخدمة الفرعية بنجاح');
    }

    public function destroy(Subservice $subservice)
    {
        $this->subserviceService->deleteSubservice($subservice);
        return $this->success(null, 'تم حذف الخدمة الفرعية بنجاح', 204);
    }

            public function multipleDelete(multipleDeleteSubserviceRequest $request)
    {
        $this->subserviceService->deleteMultipleSubservices($request->validated());
        return $this->success(null,'تم حذف الخدمات الفرعية بنجاح',204);
    }
}
