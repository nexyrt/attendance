<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PrintController;

// Livewire Components - Global
use App\Livewire\User\Profile;
use App\Livewire\Attendance\CheckIn;
use App\Livewire\Staff\Dashboard\Index as StaffDashboard;

// Livewire Components - Staff
use App\Livewire\Staff\Attendance\Index as StaffAttendance;
use App\Livewire\Staff\LeaveRequest\Index as StaffLeaveIndex;
use App\Livewire\Staff\LeaveRequest\Create as StaffLeaveCreate;

// Livewire Components - Manager
use App\Livewire\Manager\TeamAttendance\Index as ManagerTeamAttendance;

// Livewire Components - Admin & Director
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\OfficeManagement\Index as OfficeManagementIndex;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // ==================================================
    // GLOBAL ROUTES - All authenticated users
    // ==================================================
    Route::get('/dashboard', StaffDashboard::class)->name('dashboard');
    Route::get('/user/profile', Profile::class)->name('user.profile');
    Route::get('/attendance/check-in', CheckIn::class)->name('attendance.check-in');

    // ==================================================
    // STAFF LEVEL - Staff role only
    // ==================================================
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('/attendance', StaffAttendance::class)->name('attendance');

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', StaffLeaveIndex::class)->name('index');
            Route::get('/create', StaffLeaveCreate::class)->name('create');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // MANAGER LEVEL - Manager role only
    // ==================================================
    Route::middleware(['role:manager'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/team-attendance', ManagerTeamAttendance::class)->name('team-attendance');
        Route::get('/team-attendance/analytics', \App\Livewire\Manager\TeamAttendance\Analytics::class)->name('team-attendance.analytics');
        Route::get('/attendance', \App\Livewire\Director\Attendance\Index::class)->name('attendance');

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', \App\Livewire\Manager\LeaveRequest\Index::class)->name('index');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // ADMIN LEVEL - Admin role only
    // ==================================================
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/attendance', \App\Livewire\Director\Attendance\Index::class)->name('attendance');

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', \App\Livewire\Admin\LeaveRequest\Index::class)->name('index');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // DIRECTOR LEVEL - Director role only
    // ==================================================
    Route::middleware(['role:director'])->prefix('director')->name('director.')->group(function () {
        Route::get('/attendance', \App\Livewire\Director\Attendance\Index::class)->name('attendance');

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', \App\Livewire\Director\LeaveRequest\Index::class)->name('index');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // SHARED MANAGEMENT - Admin, Director & Manager
    // ==================================================
    Route::middleware(['role:admin,director,manager'])->group(function () {
        Route::get('/users', UsersIndex::class)->name('users.index');
        Route::get('/schedule', \App\Livewire\Schedule\Index::class)->name('schedule.index');

        Route::prefix('office-management')->name('office-management.')->group(function () {
            Route::get('/', OfficeManagementIndex::class)->name('index');
        });
    });
});

require __DIR__ . '/auth.php';