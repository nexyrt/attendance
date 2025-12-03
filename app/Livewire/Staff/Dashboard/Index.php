<?php

namespace App\Livewire\Staff\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\ScheduleException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Carbon\Carbon;

class Index extends Component
{
    public bool $showEventModal = false;

    public function render(): View
    {
        return view('livewire.staff.dashboard.index');
    }

    // Today's Attendance
    #[Computed]
    public function todayAttendance(): ?Attendance
    {
        return Attendance::where('user_id', Auth::id())
            ->whereDate('date', today())
            ->first();
    }

    // Current Month Stats
    #[Computed]
    public function workingDays(): int
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $days = 0;

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if (!$date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }

    #[Computed]
    public function attendanceSummary(): array
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->get();

        return [
            'present' => $attendances->where('status', 'present')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'leave' => LeaveRequest::where('user_id', Auth::id())
                ->where('status', LeaveRequest::STATUS_APPROVED)
                ->whereMonth('start_date', '<=', now()->month)
                ->whereMonth('end_date', '>=', now()->month)
                ->count(),
            'absent' => $this->workingDays - $attendances->whereIn('status', ['present', 'late'])->count(),
            'workingDays' => $this->workingDays,
        ];
    }

    // Leave Balance
    #[Computed]
    public function leaveBalance(): ?LeaveBalance
    {
        return LeaveBalance::where('user_id', Auth::id())
            ->where('year', now()->year)
            ->first();
    }

    // Holidays (current month + next 3 months)
    #[Computed]
    public function holidays(): \Illuminate\Support\Collection
    {
        $user = Auth::user();

        return ScheduleException::where('status', 'holiday')
            ->where('date', '>=', now()->startOfMonth())
            ->where('date', '<=', now()->addMonths(3)->endOfMonth())
            ->whereHas('departments', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->orderBy('date')
            ->get();
    }

    // Team Attendance Today (same department)
    #[Computed]
    public function teamAttendanceToday(): array
    {
        $user = Auth::user();

        $totalTeam = $user->department->users()->count();
        $presentToday = Attendance::whereHas('user', function ($query) use ($user) {
            $query->where('department_id', $user->department_id);
        })
            ->whereDate('date', today())
            ->whereNotNull('check_in')
            ->count();

        return [
            'present' => $presentToday,
            'total' => $totalTeam,
        ];
    }

    // Attendance Rate
    #[Computed]
    public function attendanceRate(): int
    {
        $summary = $this->attendanceSummary;
        $totalPresent = $summary['present'] + $summary['late'];

        return $summary['workingDays'] > 0
            ? round(($totalPresent / $summary['workingDays']) * 100)
            : 0;
    }

    // Upcoming Holidays (next 3)
    #[Computed]
    public function upcomingHolidays(): \Illuminate\Support\Collection
    {
        return $this->holidays
            ->where('date', '>=', now()->startOfDay())
            ->take(3);
    }
}