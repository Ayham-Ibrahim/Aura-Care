<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubserviceController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\BroadcastNotificationController;
use App\Http\Controllers\Center\WorkController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserManagementControllers\CenterController;
use App\Http\Controllers\UserManagementControllers\UserManagementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::post('register', [UserManagementController::class, 'register']);
Route::post('confirm-registration', [UserManagementController::class, 'confirmRegistration']);

// تسجيل الدخول والتأكيد
Route::post('login', [UserManagementController::class, 'login']);
Route::post('confirm-login', [UserManagementController::class, 'confirmLogin']);
Route::post('refresh', [UserManagementController::class, 'refreshToken']);


// نسيان كلمة المرور (منفصل)
Route::post('forgot-password', [UserManagementController::class, 'forgotPassword']);
Route::post('confirm-forgot-password', [UserManagementController::class, 'confirmForgotPassword']);
Route::post('reset-password', [UserManagementController::class, 'resetPassword']);

// إعادة إرسال OTP
Route::post('resend-otp', [UserManagementController::class, 'resendOTP']);

Route::middleware('auth:sanctum')->post('/logout', [UserManagementController::class, 'logout']);
Route::middleware('auth:sanctum')->delete('/account/delete', [UserManagementController::class, 'deleteAccount']);



//################################################################

Route::prefix('admin')->group(function () {
    Route::get('sections/services', [SectionController::class, 'withServices']);
    Route::get('services/by-section/{section}', [ServiceController::class, 'getServicesBySection']);

    /*
    |--------------------------------------------------------------------------
    | Admin Broadcast Notifications Routes - الإشعارات الجماعية
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->group(function () {
        Route::get('/target-types', [BroadcastNotificationController::class, 'getTargetTypes']); // أنواع المستهدفين
        Route::get('/', [BroadcastNotificationController::class, 'index']);                       // قائمة الإشعارات
        Route::post('/', [BroadcastNotificationController::class, 'store']);                      // إنشاء إشعار جديد
        Route::get('/{id}', [BroadcastNotificationController::class, 'show']);                    // تفاصيل إشعار
        Route::delete('/{id}', [BroadcastNotificationController::class, 'destroy']);              // حذف إشعار
    });

    // ---------------------------
    // Admin Users (index, show, delete)
    // ---------------------------
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'listUsers']);      // index
        Route::get('/{id}', [UserManagementController::class, 'userDetails']); // show
        Route::delete('/{id}', [UserManagementController::class, 'deleteUser']); // delete
    });

    Route::apiResource('sections', SectionController::class);
    Route::post('sections/multiple-delete', [SectionController::class, 'multipleDelete']);
    Route::patch('sections/{section}/profit-percentage', [SectionController::class, 'updatePorfitPercentage']);
    // Sections + main services for authenticated users (mobile/web)


    Route::apiResource('services', ServiceController::class)->except(['show']);
    Route::post('services/multiple-delete', [ServiceController::class, 'multipleDelete']);

    Route::apiResource('subservices', SubserviceController::class)->except(['show']);
    Route::post('subservices/multiple-delete', [SubserviceController::class, 'multipleDelete']);
    // Subservices grouped by main service
    Route::get('subservices/by-service', [SubserviceController::class, 'groupedByService']);

    Route::apiResource('centers', CenterController::class);
    Route::get('centers/{center}/works', [CenterController::class, 'getWorks']);
    // Route::post('centers/{id}/restore', [CenterController::class, 'restore']);

    Route::apiResource('ads', AdController::class);

    Route::apiResource('reservations', ReservationController::class);
    Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
});

// ---------------------------
//  User Profile 
// ---------------------------

Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserManagementController::class, 'profile']);
    Route::put('/profile', [UserManagementController::class, 'updateProfile']);
});


// ---------------------------
//  Center App 
// ---------------------------

Route::prefix('center')->middleware('auth:sanctum')->group(function () {
    Route::get('services', [CenterController::class, 'getServices']);
    Route::get('subservices/{service_id}', [CenterController::class, 'showSubservicesByService']);
    Route::patch('subservices', [CenterController::class, 'updateSubservice']);

    Route::get('works/service/{service}', [WorkController::class, 'getWorkByService']);
    Route::post('works/service/{service}', [WorkController::class, 'storeWork']);
    Route::get('works/{work}', [WorkController::class, 'getWorkById']);
    Route::delete('works/{work}', [WorkController::class, 'deleteWork']);
});