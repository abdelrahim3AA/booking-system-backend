<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);

    Route::apiResource('appointments', AppointmentController::class);

    // Additional appointment-specific routes
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);

});
