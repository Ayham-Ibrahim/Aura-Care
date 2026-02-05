<?php

use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::apiResource('sections', SectionController::class);
Route::apiResource('services', ServiceController::class)->except(['show']);
