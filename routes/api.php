<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubserviceController;
use App\Http\Controllers\AdController;
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

Route::apiResource('sections', SectionController::class);
Route::post('sections/multiple-delete', [SectionController::class, 'multipleDelete']);
Route::patch('sections/{section}/profit-percentage', [SectionController::class, 'updatePorfitPercentage']);

Route::apiResource('services', ServiceController::class)->except(['show']);
Route::post('services/multiple-delete', [ServiceController::class, 'multipleDelete']);

Route::apiResource('subservices', SubserviceController::class)->except(['show']);
Route::post('subservices/multiple-delete', [SubserviceController::class, 'multipleDelete']);

Route::apiResource('centers', CenterController::class);
Route::get('centers/{center}/works', [CenterController::class, 'getWorks']);
// Route::post('centers/{id}/restore', [CenterController::class, 'restore']);

Route::apiResource('ads', AdController::class);

Route::apiResource('reservations', ReservationController::class);
Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
