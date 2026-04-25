<?php

namespace App\Http\Controllers;

use App\Http\Requests\Device\RegisterDeviceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Device Controller
 * 
 * Handles FCM token registration for push notifications.
 * Supports multiple owner types: User, Driver, Provider, Store
 */
class DeviceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | User Device Management (Multi-device support)
    |--------------------------------------------------------------------------
    */

    /**
     * Register FCM token for authenticated user.
     * Users can have multiple devices.
     *
     * @param RegisterDeviceRequest $request
     * @return JsonResponse
     */
    public function registerUserDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        $device = $user->registerDevice($request->fcm_token);

        return $this->success([
            'device_id' => $device->id,
        ], 'تم تسجيل الجهاز بنجاح');
    }

    /**
     * Remove FCM token for authenticated user (logout from device).
     *
     * @param RegisterDeviceRequest $request
     * @return JsonResponse
     */
    public function removeUserDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $user = Auth::guard('api')->user();

        \App\Models\Device::removeByToken($user, $request->fcm_token);

        return $this->success(null, 'تم إلغاء تسجيل الجهاز');
    }

    /*
    |--------------------------------------------------------------------------
    | Center Device Management (Single-device only)
    |--------------------------------------------------------------------------
    */

    /**
     * Register FCM token for authenticated center.
     * Centers only support single device - previous device will be replaced.
     *
     * @param RegisterDeviceRequest $request
     * @return JsonResponse
     */
    public function registerCenterDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $center = Auth::guard('center')->user();

        $device = $center->registerDevice($request->fcm_token);

        return $this->success([
            'device_id' => $device->id,
        ], 'تم تسجيل الجهاز بنجاح');
    }

    /**
     * Remove FCM token for authenticated center.
     *
     * @return JsonResponse
     */
    public function removeCenterDevice(): JsonResponse
    {
        $center = Auth::guard('center')->user();

        \App\Models\Device::removeAllDevices($center);

        return $this->success(null, 'تم إلغاء تسجيل الجهاز');
    }

}
