<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubserviceController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\BroadcastNotificationController;
use App\Http\Controllers\Center\WorkController;
use App\Http\Controllers\Center\WorkingHourController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserManagementControllers\CenterController;
use App\Http\Controllers\UserManagementControllers\UserController;
use App\Http\Controllers\UserManagementControllers\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\WalletController;
use App\Http\Middleware\EnsureCenterIsActive;
use Symfony\Component\HttpKernel\Attribute\AsController;

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
Route::post('login', [UserManagementController::class, 'login'])->middleware(EnsureCenterIsActive::class)->name('login');
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

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::get('sections/services', [SectionController::class, 'withServices']);

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
        Route::get('/{user}/user-points', [UserManagementController::class, 'getUserPointsForAdmin']); // admin user points by center
        Route::get('/{id}', [UserManagementController::class, 'userDetails']); // show
        Route::get('/{user}/reservations', [ReservationController::class, 'getUserReservationsForAdmin']); // admin user reservations
        Route::patch('/{reservation}/cancel', [ReservationController::class, 'adminCancelUserReservation']); // admin cancel user reservation
        Route::delete('/{id}', [UserManagementController::class, 'deleteUser']); // delete
    });

    Route::apiResource('sections', SectionController::class);
    Route::post('sections/multiple-delete', [SectionController::class, 'multipleDelete']);
    Route::patch('sections/{section}/profit-percentage', [SectionController::class, 'updatePorfitPercentage']);
    // Sections + main services for authenticated users (mobile/web)


    Route::apiResource('services', ServiceController::class);
    Route::post('services/multiple-delete', [ServiceController::class, 'multipleDelete']);
    Route::get('services/by-section/{section}', [ServiceController::class, 'getServicesBySection']);

    Route::apiResource('subservices', SubserviceController::class);
    Route::post('subservices/multiple-delete', [SubserviceController::class, 'multipleDelete']);
    // Subservices grouped by main service
    Route::get('subservices/by-service', [SubserviceController::class, 'groupedByService']);

    Route::get('centers/pending', [CenterController::class, 'getPendingCenters']);
    Route::apiResource('centers', CenterController::class);
    Route::get('centers/{center}/details', [CenterController::class, 'getCenterByID']);
    Route::get('centers/{center}/documents', [CenterController::class, 'getCenterDocuments']);
    Route::patch('centers/{center}/documents/accept', [CenterController::class, 'acceptCenterDocuments']);
    Route::patch('centers/{center}/documents/reject', [CenterController::class, 'rejectCenterDocuments']);
    Route::patch('centers/{center}/toggle-active', [CenterController::class, 'toggleActive']);
    Route::get('centers/{center}/works', [CenterController::class, 'getWorks']);
    Route::delete('work/{work}/delete', [CenterController::class, 'deleteWork']);
    Route::get('wallets', [WalletController::class, 'getWalletForAdmin']);
    Route::get('wallets/{center}', [WalletController::class, 'getCenterWalletDetails']);
    Route::patch('wallets/{wallet}/paid', [WalletController::class, 'markWalletAsPaid']);
    Route::patch('centers/{center}/wallets/paid', [WalletController::class, 'markCenterWalletsAsPaid']);
    // Route::post('centers/{id}/restore', [CenterController::class, 'restore']);

    Route::apiResource('ads', AdController::class);

    Route::apiResource('reservations', ReservationController::class);
    Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);

    Route::get('home', [DashboardController::class, 'adminHome']);
});



Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    // ---------------------------
    //  User Profile 
    // ---------------------------

    Route::get('/profile', [UserManagementController::class, 'profile']);
    Route::put('/profile', [UserManagementController::class, 'updateProfile']);
    // location endpoints
    Route::get('/location', [UserManagementController::class, 'getUserLocation']);
    Route::patch('/location', [UserManagementController::class, 'updateUserLocation']);
    // payment info
    Route::get('/payment-info', [UserManagementController::class, 'getUserPaymentInfo']);
    Route::patch('/payment-info', [UserManagementController::class, 'updateUserPaymentInfo']);
    // user points log
    Route::get('/points', [UserManagementController::class, 'getUserPoints']);
    // ---------------------------
    //  User favorite 
    // ---------------------------

    // favorite centers (list, remove)
    Route::get('/favorite-centers', [UserController::class, 'favoriteCenters']);
    Route::delete('/favorite-centers/{center}', [UserController::class, 'removeFavoriteCenter']);
    Route::post('/favorite-centers/{center}/toggle', [UserController::class, 'DACenters']);
    // detailed center info for user


    Route::post('centers/{center}/subservices/availability', [ReservationController::class, 'getSubserviceWithTime']);
    Route::post('reservations', [ReservationController::class, 'store']);
    Route::get('reservations/payment_info/{reservation}', [ReservationController::class, 'getCenterPaymentInfo']);
    Route::post('reservations/confirm/{reservation}', [ReservationController::class, 'confirmedReservation']);
    Route::get('reservations', [ReservationController::class, 'getUserReservation']);
    Route::get('reservations/{reservation}', [ReservationController::class, 'getUserReservationById']);
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'cancelReservationForUser']);
    Route::post('reservations/{reservation}/center/{center}/rate', [ReservationController::class, 'ratingCenter']);






    // User device (multi-device support)
    Route::post('/device/register', [DeviceController::class, 'registerUserDevice']);
    Route::post('/device/unregister', [DeviceController::class, 'removeUserDevice']);
});


Route::prefix('user')->group(function () {
    Route::get('/center/{center}', [UserController::class, 'centerDetails']);
    // filter subservices or works by service for a specific center
    Route::get('centers/{center}/subservices/service/{service}', [UserController::class, 'getSubservicesForUser']);
    Route::get('centers/{center}/works/service/{service}', [UserController::class, 'getWorksByServiceForUser']);
    Route::get('works/{work}', [WorkController::class, 'getWorkById']);

    Route::get('services/by-section/{section}', [ServiceController::class, 'getServicesBySection']);
    Route::get('services/list', [ServiceController::class, 'getServicesList']);
    Route::get('ads', [AdController::class, 'index']);

    Route::get('sections/basic', [SectionController::class, 'listBasic']);
    Route::get('centers/basic', [CenterController::class, 'listBasicCenters']);
    Route::get('subservices/has-points', [CenterController::class, 'getSubservicesHasPoints']);

    Route::get('centers/service/{service}', [CenterController::class, 'getCentersByService']);
    Route::get('centers/section/{section}', [CenterController::class, 'getCentersBySection']);

    Route::get('subservice/service/{service}', [SubserviceController::class, 'getSubservicesByServiceForUser']);
    Route::get('center/subservice/{subservice}', [CenterController::class, 'getCentersBySubservice']);


    // Dashboard home page with cached data
    Route::get('home', [DashboardController::class, 'index']);
});

// ---------------------------
//  Center App 
// ---------------------------

Route::prefix('center')->middleware(['auth:sanctum'])->group(function () {
    // Center Services
    Route::get('services', [CenterController::class, 'getServices']);
    Route::get('subservices/{service_id}', [CenterController::class, 'showSubservicesByService']);
    Route::patch('subservices', [CenterController::class, 'updateSubservice']);
    Route::get('subservices/id/{subservice}', [CenterController::class, 'getSubservicesById']);

    // Center Works
    Route::get('works/service/{service}', [WorkController::class, 'getWorkByService']);
    Route::post('works/service/{service}', [WorkController::class, 'storeWork']);
    Route::get('works/{work}', [WorkController::class, 'getWorkById']);
    Route::delete('works/{work}', [WorkController::class, 'deleteWork']);

    // Center Reservations
    Route::get('reservations', [ReservationController::class, 'getCenterReservation']);
    Route::get('reservations/{reservation}', [ReservationController::class, 'getReservationById']);
    Route::get('reservations/{reservation}/payment-images', [ReservationController::class, 'getReservationpaymentImages']);
    Route::delete('reservations/{image}/payment-images', [ReservationController::class, 'deletePaymentImage']);
    Route::patch('reservations/{reservation}/accept', [ReservationController::class, 'acceptReservation']);
    Route::patch('reservations/{reservation}/complete', [ReservationController::class, 'reservationCompleted']);
    Route::patch('reservations/{reservation}/incomplete', [ReservationController::class, 'ReservationIncomplete']);
    Route::patch('reservations/{reservation}/cancel', [ReservationController::class, 'rejectReservation']);

    Route::get('reservations/{reservation}/user', [ReservationController::class, 'getReservationUserInfo']);
    Route::patch('reservations/{reservation}/confirm-deposit-refund', [ReservationController::class, 'confirmDepositRefund']);


    Route::get('wallet', [WalletController::class, 'getWalletForCenter']);

    // Center working hours (24/7 default, editable by center)
    Route::get('working-hours', [WorkingHourController::class, 'index']);
    Route::put('working-hours', [WorkingHourController::class, 'update']);
    Route::post('working-hours/reset', [WorkingHourController::class, 'resetToDefault']);

    // Center document uploads for verification
    Route::post('documents', [CenterController::class, 'uploadDocuments']);

    // Center profile and location
    Route::get('profile-info', [CenterController::class, 'centerProfileInfo']);
    Route::post('logo', [CenterController::class, 'updateCenterLogo']);
    Route::get('location', [CenterController::class, 'getCenterLocation']);
    Route::patch('location', [CenterController::class, 'updateCenterLocation']);

    // additional endpoints for sham payment info
    Route::get('payment-info', [CenterController::class, 'getPaymentInfCenter']);
    Route::patch('payment-info', [CenterController::class, 'updatePaymentInfCenter']);

    // public or authenticated listing of offers by center
    Route::get('offers', [OfferController::class, 'index']);
    Route::post('offers', [OfferController::class, 'storeOffer']);
    Route::delete('offers/{offer}', [OfferController::class, 'destroyOffer']);
    Route::get('offers/active-subservices', [OfferController::class, 'getActiveSubservice']);


    // Center device (single-device support)
    Route::post('/device/register', [DeviceController::class, 'registerCenterDevice']);
    Route::post('/device/unregister', [DeviceController::class, 'removeCenterDevice']);
});
