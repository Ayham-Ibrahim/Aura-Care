<?php

namespace App\Http\Controllers\UserManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\Center\Center;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CenterService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Return favorite centers for authenticated user.
     * Only rating, name and logo columns are selected.
     */
    public function favoriteCenters()
    {
        $user = Auth::user();
        $centers = $user->favoriteCenters()
            ->where('centers.is_active', true)
            ->select('centers.id', 'rating', 'name', 'logo')
            ->get()
            ->makeHidden('pivot');
        return $this->success($centers, 'تم جلب المراكز المفضلة بنجاح');
    }

    /**
     * Remove a center from the user's favorites.
     */
    public function removeFavoriteCenter(Center $center)
    {
        $user = Auth::user();
        $user->favoriteCenters()->detach($center->id);
        return $this->success(null, 'تمت إزالة المركز من المفضلة');
    }

    public function DACenters(Center $center)
    {
        $user = Auth::user();
        $user->favoriteCenters()->toggle($center->id);
        $isFavorited = $user->favoriteCenters()->where('center_id', $center->id)->exists();
        $message = $isFavorited ? 'تم إضافة المركز إلى المفضلة' : 'تم إزالة المركز من المفضلة';

        return $this->success(null, $message);
    }

    /**
     * Get detailed information about a specific center (for user view).
     * Includes location, working hours, services, and user's own applied services.
     */
    public function centerDetails(Center $center)
    {
        if (!$center->is_active) {
            return $this->notFoundResponse('المركز غير متاح حالياً');
        }

        // reuse service to build basic data
        $data = $this->userService->getCenterDetailForUser($center);
        return $this->success($data, 'تم جلب بيانات المركز بنجاح');
    }



    /**
     * Administrator endpoint – return only active managed subservices
     * for a given center and service.
     */
    public function getSubservicesForUser(Center $center, $service)
    {
        if (!$center->is_active) {
            return $this->notFoundResponse('المركز غير متاح حالياً');
        }

        $subs = $this->userService->getSubservices($center->id, $service);
        return $this->success($subs, 'تم الحصول على الخدمات الفرعية النشطة للمركز بنجاح');
    }

    /**
     * Administrator endpoint – get all work entries for a center filtered
     * by service, including related files.
     */
    public function getWorksByServiceForUser($center, $service)
    {
        $centerModel = Center::find($center);
        if (!$centerModel || !$centerModel->is_active) {
            return $this->notFoundResponse('المركز غير متاح حالياً');
        }

        $works = $this->userService->getCenterWorks($center, $service);
        return $this->success($works, 'تم الحصول على أعمال المركز للخدمة المحددة بنجاح');
    }
}
