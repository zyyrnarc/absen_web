<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

// root langsung ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::patch('/permits/{id}/approve', [DashboardController::class, 'approvePermit'])
        ->name('permit.approve');
    Route::get('/permits', function () {
        return redirect()->route('dashboard');
    })->name('permits');

    Route::get('/monthly-absence', function () {
        return redirect()->route('dashboard');
    })->name('monthly-absence');

    Route::get('/weekly-activity', function () {
        return redirect()->route('dashboard');
    })->name('weekly-activity');

    Route::get('/manage-student', function () {
        return redirect()->route('dashboard');
    })->name('manage-student');

    Route::get('/manage-major', function () {
        return redirect()->route('dashboard');
    })->name('manage-major');

    Route::get('/manage-campus', function () {
        return redirect()->route('dashboard');
    })->name('manage-campus');

    Route::get('/manage-mentor', function () {
        return redirect()->route('dashboard');
    })->name('manage-mentor');

    Route::get('/setting', function () {
        return redirect()->route('dashboard');
    })->name('setting');
});
