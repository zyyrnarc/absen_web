<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\ManageMajorController;
use App\Http\Controllers\ManageStudentController;
use App\Http\Controllers\MonthlyAbsenceController;
use App\Http\Controllers\WeeklyActivityController;
use Illuminate\Support\Facades\Route;

// root langsung ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Backward-compatible aliases for old URLs without /admin prefix.
Route::redirect('/login', '/admin/login');
Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/logout', '/admin/logout');
Route::redirect('/monthly-absence', '/admin/monthly-absence');
Route::redirect('/weekly-activity', '/admin/weekly-activity');
Route::redirect('/manage-student', '/admin/manage-student');
Route::redirect('/manage-major', '/admin/manage-major');
Route::redirect('/manage-campus', '/admin/manage-campus');
Route::redirect('/manage-mentor', '/admin/manage-mentor');
Route::redirect('/profile', '/admin/profile');
Route::redirect('/setting', '/admin/setting');

Route::prefix('admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');

    Route::middleware(['auth:admin', 'admin.idle'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [AuthController::class, 'showLogout'])->name('logout.page');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        Route::patch('/permits/{id}/approve', [DashboardController::class, 'approvePermit'])
            ->name('permit.approve');
        Route::get('/permits', function () {
            return redirect()->route('dashboard');
        })->name('permits');

        Route::get('/monthly-absence', [MonthlyAbsenceController::class, 'index'])
            ->name('monthly-absence');
        Route::patch('/monthly-absence/permits/{id}/approve', [MonthlyAbsenceController::class, 'approvePermit'])
            ->name('monthly-absence.permit.approve');

        Route::get('/weekly-activity', [WeeklyActivityController::class, 'index'])
            ->name('weekly-activity');

        Route::get('/manage-student', [ManageStudentController::class, 'index'])
            ->name('manage-student');
        Route::get('/manage-student/create', [ManageStudentController::class, 'create'])
            ->name('manage-student.create');
        Route::post('/manage-student', [ManageStudentController::class, 'store'])
            ->name('manage-student.store');
        Route::get('/manage-student/{student}/edit', [ManageStudentController::class, 'edit'])
            ->name('manage-student.edit');
        Route::put('/manage-student/{student}', [ManageStudentController::class, 'update'])
            ->name('manage-student.update');
        Route::delete('/manage-student/{student}', [ManageStudentController::class, 'destroy'])
            ->name('manage-student.destroy');

        Route::get('/manage-major', [ManageMajorController::class, 'index'])
            ->name('manage-major');
        Route::post('/manage-major', [ManageMajorController::class, 'store'])
            ->name('manage-major.store');
        Route::put('/manage-major/{major}', [ManageMajorController::class, 'update'])
            ->name('manage-major.update');
        Route::delete('/manage-major/{major}', [ManageMajorController::class, 'destroy'])
            ->name('manage-major.destroy');

        Route::get('/manage-campus', function () {
            return redirect()->route('dashboard');
        })->name('manage-campus');

        Route::get('/manage-mentor', function () {
            return redirect()->route('dashboard');
        })->name('manage-mentor');

        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile');
        Route::get('/setting', [ProfileController::class, 'showSetting'])->name('setting');
        Route::put('/setting/company', [ProfileController::class, 'updateSetting'])->name('setting.update');
        Route::put('/setting/password', [ProfileController::class, 'updatePassword'])->name('setting.password.update');
    });
});
