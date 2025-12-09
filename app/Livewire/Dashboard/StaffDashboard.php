<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Contracts\View\View;
use Carbon\Carbon;

class StaffDashboard extends Component
{
    // Real-time clock
    public string $currentTime;

    // Check-in state
    public ?string $checkInTime = null;
    public ?string $checkOutTime = null;
    public string $status = 'not_checked_in'; // not_checked_in, checked_in, checked_out

    // Calendar
    public $selectedDate;

    public function mount(): void
    {
        $this->currentTime = now()->format('H:i:s');
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function placeholder(): View
    {
        return view('livewire.dashboard.staff-dashboard-placeholder');
    }

    public function render(): View
    {
        return view('livewire.dashboard.staff-dashboard', [
            'currentUser' => $this->getCurrentUser(),
            'leaveBalances' => $this->getLeaveBalances(),
            'scheduleExceptions' => $this->getScheduleExceptions(),
            'weekData' => $this->getWeekData(),
            'activities' => $this->getActivities(),
            'stats' => $this->getStats(),
            'upcomingExceptions' => $this->getUpcomingExceptions(),
            'selectedDateException' => $this->getSelectedDateException(),
        ]);
    }

    // ============================================
    // ACTIONS
    // ============================================

    public function updateTime(): void
    {
        $this->currentTime = now()->format('H:i:s');
    }

    public function checkIn(): void
    {
        $this->checkInTime = now()->format('H:i:s');
        $this->checkOutTime = null;
        $this->status = 'checked_in';
    }

    public function checkOut(): void
    {
        $this->checkOutTime = now()->format('H:i:s');
        $this->status = 'checked_out';
    }

    // ============================================
    // MOCK DATA METHODS
    // ============================================

    private function getCurrentUser(): array
    {
        return [
            'name' => 'Ahmad Rizki',
            'role' => 'Staff',
            'department' => 'Digital Marketing',
        ];
    }

    private function getLeaveBalances(): array
    {
        return [
            ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => 12, 'used' => 5, 'color' => 'text-blue-600'],
            ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => 14, 'used' => 2, 'color' => 'text-green-600'],
            ['type' => 'Important', 'icon' => 'star', 'total' => 3, 'used' => 0, 'color' => 'text-yellow-600'],
            ['type' => 'Other', 'icon' => 'briefcase', 'total' => 5, 'used' => 1, 'color' => 'text-gray-600'],
        ];
    }

    private function getScheduleExceptions(): array
    {
        return [
            ['id' => 1, 'title' => 'Christmas Day', 'date' => '2025-12-25', 'status' => 'holiday', 'note' => 'National Holiday'],
            ['id' => 2, 'title' => 'New Year\'s Day', 'date' => '2026-01-01', 'status' => 'holiday', 'note' => 'National Holiday'],
            ['id' => 3, 'title' => 'Company Anniversary', 'date' => '2025-12-15', 'status' => 'event', 'note' => 'Annual celebration'],
            ['id' => 4, 'title' => 'Team Building Day', 'date' => '2025-12-20', 'status' => 'event', 'note' => 'Outdoor activities'],
            ['id' => 5, 'title' => 'Hari Raya', 'date' => '2025-12-31', 'status' => 'holiday', 'note' => 'Religious Holiday'],
        ];
    }

    private function getWeekData(): array
    {
        return [
            ['day' => 'Mon', 'date' => '02', 'status' => 'present', 'hours' => 8.5],
            ['day' => 'Tue', 'date' => '03', 'status' => 'late', 'hours' => 8.0],
            ['day' => 'Wed', 'date' => '04', 'status' => 'present', 'hours' => 8.5],
            ['day' => 'Thu', 'date' => '05', 'status' => 'present', 'hours' => 8.5],
            ['day' => 'Fri', 'date' => '06', 'status' => 'present', 'hours' => 8.0],
            ['day' => 'Sat', 'date' => '07', 'status' => 'holiday', 'hours' => 0],
            ['day' => 'Sun', 'date' => '08', 'status' => 'holiday', 'hours' => 0],
        ];
    }

    private function getActivities(): array
    {
        return [
            ['id' => 1, 'type' => 'check_in', 'desc' => 'Checked in at Main Office', 'time' => '2025-12-08 08:55:00'],
            ['id' => 2, 'type' => 'check_out', 'desc' => 'Checked out from Main Office', 'time' => '2025-12-06 17:30:00'],
            ['id' => 3, 'type' => 'leave_approved', 'desc' => 'Annual leave approved', 'time' => '2025-12-05 14:22:00', 'status' => 'approved'],
            ['id' => 4, 'type' => 'late', 'desc' => 'Late arrival - 25 minutes', 'time' => '2025-12-03 09:25:00'],
        ];
    }

    private function getStats(): array
    {
        return [
            ['label' => 'Attendance Rate', 'value' => '96.5%', 'change' => '+2.3%', 'trend' => 'up', 'icon' => 'check-circle', 'color' => 'text-green-600'],
            ['label' => 'Late Arrivals', 'value' => '2 days', 'change' => '-1', 'trend' => 'down', 'icon' => 'exclamation-circle', 'color' => 'text-yellow-600'],
            ['label' => 'Avg. Hours', 'value' => '8.2h/day', 'change' => '+0.3h', 'trend' => 'up', 'icon' => 'clock', 'color' => 'text-blue-600'],
            ['label' => 'Days Worked', 'value' => '21 days', 'icon' => 'calendar', 'color' => 'text-gray-600'],
        ];
    }

    private function getUpcomingExceptions(): array
    {
        return collect($this->getScheduleExceptions())
            ->filter(fn($ex) => Carbon::parse($ex['date'])->isFuture())
            ->sortBy('date')
            ->take(4)
            ->values()
            ->toArray();
    }

    private function getSelectedDateException(): ?array
    {
        $selected = Carbon::parse($this->selectedDate);

        return collect($this->getScheduleExceptions())
            ->first(fn($ex) => Carbon::parse($ex['date'])->isSameDay($selected));
    }

    public function formatWorkingHours(): string
    {
        $h = floor($this->workingSeconds / 3600);
        $m = floor(($this->workingSeconds % 3600) / 60);
        $s = $this->workingSeconds % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}