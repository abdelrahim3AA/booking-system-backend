<?php

use App\Models\Appointment;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppointmentController;
use App\Mail\AppointmentConfirmation;

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);

    Route::apiResource('appointments', AppointmentController::class);

    // Additional appointment-specific routes
    Route::post('appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::post('appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule']);
    Route::post('appointments/{appointment}/confirm', [AppointmentController::class, 'confirm']);
});
