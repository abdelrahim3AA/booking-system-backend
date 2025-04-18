<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login',    [AuthController::class, 'login']);

// Route::middleware('auth:sanctum')->group(function () {
//     Route::post('/logout', [AuthController::class, 'logout']);
// });

Route::get('/user', function (Request $request) {
    return $request->user();
});
// ->middleware('auth:sanctum');


// Include modular route files
require __DIR__ . '/api/appointments.php';
require __DIR__ . '/api/services.php';
require __DIR__ . '/api/categories.php';

