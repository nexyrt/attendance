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
    // Dashboard - All roles
    Route::get('/dashboard', StaffDashboard::class)->name('dashboard');

    // Profile - All roles
    Route::get('/user/profile', Profile::class)->name('user.profile');

    // Check-in only - All authenticated users (untuk mobile/kiosk access)
    Route::get('/attendance/check-in', App\Livewire\Staff\Attendance\CheckIn::class)->name('attendance.check-in');

    // Staff Level - Staff role only
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

    // Manager Level and above
    Route::middleware(['role:manager,hr,director,admin'])->prefix('manager')->name('manager.')->group(function () {
        Route::get('/team-attendance', App\Livewire\Manager\TeamAttendance\Index::class)->name('team-attendance');
        // Route::get('/approve-leaves', App\Livewire\Manager\LeaveApproval\Index::class)->name('approve-leaves');
    });

    // HR Level and above
    Route::middleware(['role:hr,director,admin'])->group(function () {
        Route::get('/users', Index::class)->name('users.index');

        // Future HR routes
        // Route::prefix('hr')->name('hr.')->group(function () {
        //     Route::get('/schedules', App\Livewire\HR\Schedules\Index::class)->name('schedules.index');
        //     Route::get('/leave-balances', App\Livewire\HR\LeaveBalances\Index::class)->name('leave-balances.index');
        //     Route::get('/reports', App\Livewire\HR\Reports\Index::class)->name('reports.index');
        // });
    });

    // Director Level and above (for future use)
    // Route::middleware(['role:director,admin'])->prefix('director')->name('director.')->group(function () {
    //     Route::get('/departments', App\Livewire\Director\Departments\Index::class)->name('departments.index');
    //     Route::get('/office-locations', App\Livewire\Director\OfficeLocations\Index::class)->name('office-locations.index');
    // });
});

require __DIR__ . '/auth.php';