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

    // STAFF LEVEL - All roles can access
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', App\Livewire\Staff\Attendance\Index::class)->name('index');
        Route::get('/check-in', App\Livewire\Staff\Attendance\CheckIn::class)->name('check-in');
    });

    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', App\Livewire\Staff\LeaveRequest\Index::class)->name('index');
        Route::get('/create', App\Livewire\Staff\LeaveRequest\Create::class)->name('create');
        Route::get('/{leaveRequest}/print', [PrintController::class, 'leaveRequest'])->name('print');
    });

    // HR LEVEL - Users management for HR+
    Route::middleware(['role:hr,director,admin'])->group(function () {
        Route::get('/users', Index::class)->name('users.index');
    });
});

require __DIR__ . '/auth.php';