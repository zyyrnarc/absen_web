<?php

use App\Http\Controllers\Api\MobileActivityController;
use App\Http\Controllers\Api\MobileAttendanceController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDashboardController;
use App\Http\Controllers\Api\MobilePermitController;
use App\Http\Controllers\Api\MobileProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);
    Route::post('/google-login', [MobileAuthController::class, 'googleLogin']);

    Route::get('/me', [MobileAuthController::class, 'me']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);
    Route::get('/dashboard', MobileDashboardController::class);
    Route::get('/profile', [MobileProfileController::class, 'show']);
    Route::put('/profile', [MobileProfileController::class, 'update']);
    Route::put('/profile/password', [MobileProfileController::class, 'updatePassword']);

    Route::get('/attendances', [MobileAttendanceController::class, 'index']);
    Route::get('/attendances/monthly', [MobileAttendanceController::class, 'monthly']);
    Route::get('/attendances/monthly/export', [MobileAttendanceController::class, 'monthlyExport']);
    Route::post('/attendances/check-in', [MobileAttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [MobileAttendanceController::class, 'checkOut']);

    Route::get('/activities', [MobileActivityController::class, 'index']);
    Route::get('/activities/weekly', [MobileActivityController::class, 'weekly']);
    Route::get('/activities/weekly/export', [MobileActivityController::class, 'weeklyExport']);
    Route::post('/activities', [MobileActivityController::class, 'store']);
    Route::put('/activities/{activity}', [MobileActivityController::class, 'update']);
    Route::delete('/activities/{activity}', [MobileActivityController::class, 'destroy']);

    Route::get('/permits/meta', [MobilePermitController::class, 'meta']);
    Route::get('/permits', [MobilePermitController::class, 'index']);
    Route::post('/permits', [MobilePermitController::class, 'store']);
});
