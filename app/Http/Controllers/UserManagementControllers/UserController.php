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
    protected $centerService;

    public function __construct(UserService $userService)
    {
        $this->centerService = $userService;
    }

    /**
     * Return favorite centers for authenticated user.
     * Only rating, name and logo columns are selected.
     */
    public function favoriteCenters()
    {
        $user = Auth::user();
        $centers = $user->favoriteCenters()
                        ->select('centers.id','rating','name','logo')
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

    /**
     * Get detailed information about a specific center (for user view).
     * Includes location, working hours, services, and user's own applied services.
     */
    public function centerDetails(Center $center)
    {
        // reuse service to build basic data
        $data = $this->centerService->getCenterDetailForUser($center);
        return $this->success($data, 'تم جلب بيانات المركز بنجاح');
    }
}
