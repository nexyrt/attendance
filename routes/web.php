<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Livewire\User\Profile;
use App\Livewire\Users\Index;
use App\Livewire\Staff\Dashboard\Index as StaffDashboard;
use App\Http\Controllers\PrintController;

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
    Route::get('/attendance/check-in', App\Livewire\Staff\Attendance\CheckIn::class)->name('attendance.check-in');

    // ==================================================
    // STAFF LEVEL - Staff role only
    // ==================================================
    Route::middleware(['role:staff'])->group(function () {
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', App\Livewire\Staff\Attendance\Index::class)->name('index');
        });

        Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
            Route::get('/', App\Livewire\Staff\LeaveRequest\Index::class)->name('index');
            Route::get('/create', App\Livewire\Staff\LeaveRequest\Create::class)->name('create');
            Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
        });
    });

    // ==================================================
    // MANAGER LEVEL - Manager, Admin, Director
    // ==================================================
    Route::middleware(['role:manager,admin,director'])->group(function () {
        Route::prefix('manager')->name('manager.')->group(function () {
            Route::get('/team-attendance', App\Livewire\Manager\TeamAttendance\Index::class)->name('team-attendance');
            // Route::get('/approve-leaves', App\Livewire\Manager\LeaveApproval\Index::class)->name('approve-leaves');
        });
    });

    // ==================================================
    // ADMIN LEVEL (HR) - Admin, Director
    // ==================================================
    Route::middleware(['role:admin,director,manager'])->group(function () {
        // User Management
        Route::get('/users', Index::class)->name('users.index');

        // Office Management
        Route::prefix('office-management')->name('office-management.')->group(function () {
            Route::get('/', App\Livewire\OfficeManagement\Index::class)->name('index');
            // Route::get('/create', App\Livewire\OfficeManagement\Create::class)->name('create');
        });

        // Future HR routes
        // Route::prefix('hr')->name('hr.')->group(function () {
        //     Route::get('/schedules', App\Livewire\HR\Schedules\Index::class)->name('schedules.index');
        //     Route::get('/leave-balances', App\Livewire\HR\LeaveBalances\Index::class)->name('leave-balances.index');
        //     Route::get('/reports', App\Livewire\HR\Reports\Index::class)->name('reports.index');
        // });
    });

    // ==================================================
    // DIRECTOR LEVEL - Director only (for future use)
    // ==================================================
    // Route::middleware(['role:director'])->group(function () {
    //     Route::prefix('director')->name('director.')->group(function () {
    //         Route::get('/departments', App\Livewire\Director\Departments\Index::class)->name('departments.index');
    //         Route::get('/strategic-reports', App\Livewire\Director\Reports\Index::class)->name('strategic-reports.index');
    //     });
    // });
});

require __DIR__ . '/auth.php';