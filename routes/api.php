<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubserviceController;
use App\Http\Controllers\CenterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('sections', SectionController::class);
Route::apiResource('services', ServiceController::class)->except(['show']);
Route::apiResource('subservices', SubserviceController::class)->except(['show']);
Route::apiResource('centers', CenterController::class);
Route::get('centers/{center}/works', [CenterController::class, 'getWorks']);
// Route::post('centers/{id}/restore', [CenterController::class, 'restore']);
