<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PrintController;

// Livewire Components - Global
use App\Livewire\User\Profile;
use App\Livewire\Attendance\CheckIn;
use App\Livewire\Staff\Dashboard\Index as StaffDashboard;

// Livewire Components - Role Specific
use App\Livewire\Staff\Attendance\Index as StaffAttendance;
use App\Livewire\Staff\LeaveRequest\Index as StaffLeaveIndex;
use App\Livewire\Staff\LeaveRequest\Create as StaffLeaveCreate;
use App\Livewire\Manager\TeamAttendance\Index as ManagerTeamAttendance;
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
    Route::middleware(['role:staff'])->group(function () {
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', StaffAttendance::class)->name('index');
        });

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', StaffLeaveIndex::class)->name('index');
            Route::get('/create', StaffLeaveCreate::class)->name('create');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // MANAGER LEVEL - Manager role only
    // ==================================================
    Route::middleware(['role:manager'])->group(function () {
        Route::prefix('manager')->name('manager.')->group(function () {
            Route::get('/team-attendance', ManagerTeamAttendance::class)->name('team-attendance');
            Route::get('/team-attendance/analytics', \App\Livewire\Manager\TeamAttendance\Analytics::class)->name('team-attendance.analytics');
        });
    });

    // ==================================================
    // ADMIN LEVEL - Admin, Director roles
    // ==================================================
    Route::middleware(['role:admin,director,manager'])->group(function () {
        // User Management
        Route::get('/users', UsersIndex::class)->name('users.index');

        // Office Management
        Route::prefix('office-management')->name('office-management.')->group(function () {
            Route::get('/', OfficeManagementIndex::class)->name('index');
        });
    });

    // ==================================================
    // FUTURE EXPANSIONS (Commented for reference)
    // ==================================================

    // HR Module (Admin+ roles)
    // Route::middleware(['role:admin,director'])->group(function () {
    //     Route::prefix('hr')->name('hr.')->group(function () {
    //         Route::get('/schedules', App\Livewire\HR\Schedules\Index::class)->name('schedules.index');
    //         Route::get('/leave-balances', App\Livewire\HR\LeaveBalances\Index::class)->name('leave-balances.index');
    //         Route::get('/reports', App\Livewire\HR\Reports\Index::class)->name('reports.index');
    //     });
    // });

    // Director Module (Director only)
    // Route::middleware(['role:director'])->group(function () {
    //     Route::prefix('director')->name('director.')->group(function () {
    //         Route::get('/departments', App\Livewire\Director\Departments\Index::class)->name('departments.index');
    //         Route::get('/strategic-reports', App\Livewire\Director\Reports\Index::class)->name('strategic-reports.index');
    //     });
    // });
});

require __DIR__ . '/auth.php';