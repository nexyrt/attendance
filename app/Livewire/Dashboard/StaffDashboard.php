<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\ScheduleException;
use FontLib\TrueType\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StaffDashboard extends Component
{
    public function render(): View
    {
        return view('livewire.dashboard.staff-dashboard');
    }

    public $selectedDate;

    public function mount()
    {
        $this->selectedDate = now();
    }

    // ============================================================
    // COMPUTED PROPERTIES - STATS & DATA
    // ============================================================

    #[Computed]
    public function quickStats(): array
    {
        $userId = Auth::id();
        $thirtyDaysAgo = now()->subDays(30);

        $attendances = Attendance::where('user_id', $userId)
            ->where('date', '>=', $thirtyDaysAgo)
            ->get();

        $totalDays = $attendances->count();
        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $totalHours = $attendances->sum('working_hours');
        $avgHours = $totalDays > 0 ? round($totalHours / $totalDays, 1) : 0;

        return [
            'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 1) : 0,
            'late_count' => $lateDays,
            'avg_hours' => $avgHours,
            'days_worked' => $presentDays
        ];
    }

    #[Computed]
    public function leaveBalances(): array
    {
        $balance = Auth::user()
            ->leaveBalances()
            ->where('year', now()->year)
            ->first();

        if (!$balance) {
            return [
                ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => 12, 'used' => 0, 'color' => 'text-blue-600'],
                ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => 12, 'used' => 0, 'color' => 'text-red-600'],
                ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => 12, 'used' => 0, 'color' => 'text-yellow-600'],
                ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => 12, 'used' => 0, 'color' => 'text-gray-600'],
            ];
        }

        return [
            ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-blue-600'],
            ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-red-600'],
            ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-yellow-600'],
            ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-gray-600'],
        ];
    }

    #[Computed]
    public function scheduleExceptions(): array
    {
        $user = Auth::user();

        return ScheduleException::whereYear('date', now()->year)
            ->whereHas('departments', function ($query) use ($user) {
                $query->where('department_id', $user->department_id);
            })
            ->orderBy('date')
            ->get()
            ->map(fn($exc) => [
                'id' => $exc->id,
                'title' => $exc->title,
                'date' => $exc->date->format('Y-m-d'),
                'status' => $exc->status,
                'note' => $exc->note
            ])
            ->toArray();
    }

    #[Computed]
    public function weekData(): array
    {
        $startOfWeek = now()->startOfWeek();
        $weekData = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            $attendance = Attendance::where('user_id', Auth::id())
                ->where('date', $date)
                ->first();

            $weekData[] = [
                'day' => $date->format('D'),
                'date' => $date->format('d'),
                'full_date' => $date->format('Y-m-d'),
                'status' => $attendance ? $attendance->status : null,
                'hours' => $attendance ? $attendance->working_hours : null
            ];
        }

        return $weekData;
    }

    #[Computed]
    public function activities(): array
    {
        return Attendance::where('user_id', Auth::id())
            ->whereNotNull('check_in')
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc')
            ->take(10)
            ->get()
            ->flatMap(function ($att) {
                $activities = [];

                if ($att->check_in) {
                    $activities[] = [
                        'type' => 'check_in',
                        'desc' => 'Checked in' . ($att->status === 'late' ? ' (Late)' : ''),
                        'time' => $att->check_in->format('Y-m-d H:i:s'),
                        'date' => $att->date->format('Y-m-d')
                    ];
                }

                if ($att->check_out) {
                    $activities[] = [
                        'type' => 'check_out',
                        'desc' => 'Checked out' . ($att->status === 'early_leave' ? ' (Early)' : ''),
                        'time' => $att->check_out->format('Y-m-d H:i:s'),
                        'date' => $att->date->format('Y-m-d')
                    ];
                }

                return $activities;
            })
            ->sortByDesc('time')
            ->take(5)
            ->values()
            ->toArray();
    }

    #[Computed]
    public function attendanceData(): Collection
    {
        return Attendance::where('user_id', auth()->id())
            ->whereYear('date', $this->selectedDate->year)
            ->whereMonth('date', $this->selectedDate->month)
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));
    }

    public function getAttendance($date): ?object
    {
        if (!$date)
            return null;

        $key = $date->format('Y-m-d');
        $attendance = $this->attendanceData->get($key);

        if (!$attendance)
            return null;

        return (object) [
            'status' => $attendance->status,
            'hours' => $attendance->working_hours
        ];
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================

    #[On('refresh-dashboard')]
    public function refreshDashboard(): void
    {
        // Clear all computed caches
        unset($this->quickStats);
        unset($this->leaveBalances);
        unset($this->weekData);
        unset($this->activities);
    }
}