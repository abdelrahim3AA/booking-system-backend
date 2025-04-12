<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ServiceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('services', ServiceController::class);

    // Get available time slots for a service
    Route::get('services/{service}/availability', [ServiceController::class, 'checkAvailability']);
});