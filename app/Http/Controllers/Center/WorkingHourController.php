<?php

namespace App\Http\Controllers\Center;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\UpdateWorkingHoursRequest;
use App\Services\Center\WorkingHourService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkingHourController extends Controller
{
    protected $hourService;

    public function __construct(WorkingHourService $hourService)
    {
        $this->hourService = $hourService;
    }

    /**
     * Retrieve current working hours for authenticated center
     */
    public function index()
    {
        $data = $this->hourService->getWorkingHours();
        return $this->success($data, 'تم الحصول على أوقات العمل بنجاح');
    }

    /**
     * Update working hours for authenticated center
     */
    public function update(UpdateWorkingHoursRequest $request)
    {
        $result = $this->hourService->updateWorkingHours($request->validated());
        return $this->success($result, 'تم تعديل أوقات العمل بنجاح');
    }

    public function resetToDefault()
    {
        $result = $this->hourService->resetWorkingHours();
        return $this->success($result, 'تم إعادة أوقات العمل إلى الافتراضية بنجاح');
    }
}
