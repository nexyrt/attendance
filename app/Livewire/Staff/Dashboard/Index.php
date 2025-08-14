<?php

namespace App\Livewire\Staff\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.staff.dashboard.index');
    }

    #[Computed]
    public function todayAttendance(): ?Attendance
    {
        return Attendance::where('user_id', Auth::id())
            ->where('date', today())
            ->first();
    }

    #[Computed]
    public function currentMonthStats(): array
    {
        $userId = Auth::id();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $attendances = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        return [
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'total_working_hours' => $attendances->sum('working_hours'),
            'total_late_hours' => $attendances->sum('late_hours'),
        ];
    }

    #[Computed]
    public function leaveBalance(): ?LeaveBalance
    {
        return LeaveBalance::where('user_id', Auth::id())
            ->where('year', now()->year)
            ->first();
    }

    #[Computed]
    public function recentLeaveRequests(): \Illuminate\Database\Eloquent\Collection
    {
        return LeaveRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function pendingLeaveRequests(): int
    {
        return LeaveRequest::where('user_id', Auth::id())
            ->whereIn('status', [
                LeaveRequest::STATUS_PENDING_MANAGER,
                LeaveRequest::STATUS_PENDING_HR,
                LeaveRequest::STATUS_PENDING_DIRECTOR
            ])
            ->count();
    }
}