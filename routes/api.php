<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubserviceController;
use App\Http\Controllers\CenterController;
use App\Http\Controllers\AdController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

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
