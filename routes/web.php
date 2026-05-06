<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManageCampusController;
use App\Http\Controllers\Admin\ManageMajorController;
use App\Http\Controllers\Admin\ManageMentorController;
use App\Http\Controllers\Admin\ManageStudentController;
use App\Http\Controllers\Admin\MonthlyAbsenceController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\WeeklyActivityController;
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
            return redirect()->route('monthly-absence');
        })->name('permits');

        Route::get('/monthly-absence', [MonthlyAbsenceController::class, 'index'])
            ->name('monthly-absence');
        Route::patch('/monthly-absence/permits/{id}/approve', [MonthlyAbsenceController::class, 'approvePermit'])
            ->name('monthly-absence.permit.approve');

        Route::get('/weekly-activity', [WeeklyActivityController::class, 'index'])
            ->name('weekly-activity');
        Route::get('/weekly-activity/export', [WeeklyActivityController::class, 'export'])
            ->name('weekly-activity.export');

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

        Route::get('/manage-campus', [ManageCampusController::class, 'index'])
            ->name('manage-campus');
        Route::post('/manage-campus', [ManageCampusController::class, 'store'])
            ->name('manage-campus.store');
        Route::put('/manage-campus/{campus}', [ManageCampusController::class, 'update'])
            ->name('manage-campus.update');
        Route::delete('/manage-campus/{campus}', [ManageCampusController::class, 'destroy'])
            ->name('manage-campus.destroy');

        Route::get('/manage-mentor', [ManageMentorController::class, 'index'])
            ->name('manage-mentor');
        Route::post('/manage-mentor', [ManageMentorController::class, 'store'])
            ->name('manage-mentor.store');
        Route::put('/manage-mentor/{mentor}', [ManageMentorController::class, 'update'])
            ->name('manage-mentor.update');
        Route::delete('/manage-mentor/{mentor}', [ManageMentorController::class, 'destroy'])
            ->name('manage-mentor.destroy');

        Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::get('/setting', [ProfileController::class, 'showSetting'])->name('setting');
        Route::put('/setting/company', [ProfileController::class, 'updateSetting'])->name('setting.update');
        Route::put('/setting/password', [ProfileController::class, 'updatePassword'])->name('setting.password.update');
        Route::get('/monthly-absence/export', [MonthlyAbsenceController::class, 'export'])
            ->name('monthly-absence.export');
    });
});
