<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PrintController;

// Livewire Components
use App\Livewire\User\Profile;
use App\Livewire\Attendance\CheckIn;
use App\Livewire\Attendance\MyAttendance;
use App\Livewire\Attendance\TeamAttendance\Index as TeamAttendanceIndex;
use App\Livewire\Attendance\TeamAttendance\Analytics as TeamAttendanceAnalytics;
use App\Livewire\Attendance\AllAttendance\Index as AllAttendanceIndex;
use App\Livewire\Dashboard\StaffDashboard;
use App\Livewire\Dashboard\ManagerDashboard;
use App\Livewire\Dashboard\AdminDashboard;
use App\Livewire\Dashboard\DirectorDashboard;
use App\Livewire\LeaveRequests\MyLeaves\Index as MyLeavesIndex;
use App\Livewire\LeaveRequests\MyLeaves\Create as MyLeavesCreate;
use App\Livewire\LeaveRequests\Approvals\Index as LeaveApprovalsIndex;
use App\Livewire\Users\Index as UsersIndex;
use App\Livewire\OfficeLocations\Index as OfficeLocationsIndex;
use App\Livewire\Schedule\Index as ScheduleIndex;

Route::get('/', function () {
    if (Auth::check()) {
        $user = auth()->user();

        $route = match (true) {
            $user->hasRole('director') => 'dashboard.director',
            $user->hasRole('admin') => 'dashboard.admin',
            $user->hasRole('manager') => 'dashboard.manager',
            default => 'dashboard.staff'
        };

        return redirect()->route($route);
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // ==================================================
    // DASHBOARD - Role-specific routes
    // ==================================================
    Route::middleware(['can:dashboard.view'])->prefix('dashboard')->group(function () {
        Route::get('/staff', StaffDashboard::class)
            ->middleware('role:staff')
            ->name('dashboard.staff');

        Route::get('/manager', ManagerDashboard::class)
            ->middleware('role:manager')
            ->name('dashboard.manager');

        Route::get('/admin', AdminDashboard::class)
            ->middleware('role:admin')
            ->name('dashboard.admin');

        Route::get('/director', DirectorDashboard::class)
            ->middleware('role:director')
            ->name('dashboard.director');
    });

    // ==================================================
    // GLOBAL ROUTES
    // ==================================================
    Route::get('/user/profile', Profile::class)->name('user.profile');

    Route::middleware(['can:attendance.check-in'])->group(function () {
        Route::get('/attendance/check-in', CheckIn::class)->name('attendance.check-in');
    });

    // ==================================================
    // ATTENDANCE MODULE
    // ==================================================
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::middleware(['can:attendance.view-own'])->group(function () {
            Route::get('/my', MyAttendance::class)->name('my');
        });

        Route::middleware(['can:attendance.view-team'])->group(function () {
            Route::get('/team', TeamAttendanceIndex::class)->name('team');
            Route::get('/team/analytics', TeamAttendanceAnalytics::class)->name('team.analytics');
        });

        Route::middleware(['can:attendance.view-all'])->group(function () {
            Route::get('/all', AllAttendanceIndex::class)->name('all');
        });
    });

    // ==================================================
    // LEAVE REQUESTS MODULE
    // ==================================================
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::middleware(['can:leave-requests.view-own'])->prefix('my')->name('my.')->group(function () {
            Route::get('/', MyLeavesIndex::class)->name('index');
            Route::get('/create', MyLeavesCreate::class)->name('create');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });

        Route::middleware(['can:leave-requests.view-pending'])->prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', LeaveApprovalsIndex::class)->name('index');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // MANAGEMENT MODULES - Manager/Admin/Director
    // ==================================================
    Route::middleware(['can:users.view'])->group(function () {
        Route::get('/users', UsersIndex::class)->name('users.index');
    });

    Route::middleware(['can:schedule.view'])->group(function () {
        Route::get('/schedule', ScheduleIndex::class)->name('schedule.index');
    });

    Route::middleware(['can:office-locations.view'])->group(function () {
        Route::get('/office-locations', OfficeLocationsIndex::class)->name('office-locations.index');
    });
});

require __DIR__ . '/auth.php';