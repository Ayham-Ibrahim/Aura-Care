<?php

namespace App\Http\Controllers\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreWorkRequest;
use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\Service;
use App\Services\Center\WorkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkController extends Controller
{
    protected $workService;

    public function __construct(WorkService $workService)
    {
        $this->workService = $workService;
    }


    public function getWorkByService(Service $Service)
    {
        $work = $this->workService->WorksByService($Service);
        return $this->success($work, 'تم الحصول على عمل المركز بنجاح');
    }

    public function storeWork(StoreWorkRequest $request, Service $service)
    {
        $work = $this->workService->createWork($service, $request->validated());
        return $this->createdResponse($work, 'تم إنشاء عمل المركز بنجاح');
    }

    public function getWorkById(Work $work)
    {
        return $this->success($work->load('files'), 'تم الحصول على عمل المركز بنجاح');
    }

    public function deleteWork(Work $work)
    {
        if ($work->center_id !== Auth::guard('center')->user()->id) {
            return $this->forbiddenResponse('لا يمكنك حذف عمل مركز آخر');
        }
        $work->delete();
        return $this->success(null, 'تم حذف عمل المركز بنجاح', 204);
    }
}
