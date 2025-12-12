<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\LeaveBalance;
use App\Models\ScheduleException;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class StaffDashboard extends Component
{
    public $selectedDate;
    public ?int $selectedDayIndex = null;
    public bool $eventModal = false;

    public function mount()
    {
        $this->selectedDate = now();
    }

    public function render(): View
    {
        return view('livewire.dashboard.staff-dashboard');
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

    // #[Computed]
    // public function leaveBalances(): array
    // {
    //     $balance = Auth::user()
    //         ->leaveBalances()
    //         ->where('year', now()->year)
    //         ->first();

    //     if (!$balance) {
    //         return [
    //             ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => 12, 'used' => 0, 'color' => 'text-blue-600'],
    //             ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => 12, 'used' => 0, 'color' => 'text-red-600'],
    //             ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => 12, 'used' => 0, 'color' => 'text-yellow-600'],
    //             ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => 12, 'used' => 0, 'color' => 'text-gray-600'],
    //         ];
    //     }

    //     return [
    //         ['type' => 'Annual Leave', 'icon' => 'sun', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-blue-600'],
    //         ['type' => 'Sick Leave', 'icon' => 'heart', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-red-600'],
    //         ['type' => 'Important Leave', 'icon' => 'shield-exclamation', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-yellow-600'],
    //         ['type' => 'Other', 'icon' => 'ellipsis-horizontal', 'total' => $balance->total_balance, 'used' => $balance->used_balance, 'color' => 'text-gray-600'],
    //     ];
    // }

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

    // ============================================================
    // CALENDAR METHODS
    // ============================================================

    #[Computed]
    public function calendarData(): array
    {
        $year = $this->selectedDate->year;
        $month = $this->selectedDate->month;

        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();

        // Get schedule exceptions for current month
        $scheduleExceptions = ScheduleException::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->whereHas('departments', fn($q) => $q->where('departments.id', auth()->user()->department_id))
            ->get()
            ->keyBy(fn($se) => $se->date->format('Y-m-d'));

        $daysInMonth = [];
        for ($day = 1; $day <= $lastDay->day; $day++) {
            $date = Carbon::create($year, $month, $day);
            $dateKey = $date->format('Y-m-d');

            $exception = $scheduleExceptions->get($dateKey);

            $daysInMonth[] = [
                'date' => $date,
                'day' => $day,
                'exception' => $exception ? [
                    'id' => $exception->id,
                    'title' => $exception->title,
                    'status' => $exception->status,
                    'note' => $exception->note,
                    'start_time' => $exception->start_time?->format('H:i'),
                    'end_time' => $exception->end_time?->format('H:i'),
                ] : null,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
            ];
        }

        return [
            'days' => $daysInMonth,
            'startDayOfWeek' => $firstDay->dayOfWeek,
            'monthName' => $firstDay->format('F Y'),
        ];
    }

    #[Computed]
    public function selectedEvent(): ?array
    {
        if ($this->selectedDayIndex === null) {
            return null;
        }

        $day = $this->calendarData['days'][$this->selectedDayIndex] ?? null;

        if (!$day || !$day['exception']) {
            return null;
        }

        return [
            ...$day['exception'],
            'date' => $day['date']->format('l, F j, Y')
        ];
    }

    public function previousMonth(): void
    {
        $this->selectedDate = $this->selectedDate->subMonth();
        unset($this->calendarData);
    }

    public function nextMonth(): void
    {
        $this->selectedDate = $this->selectedDate->addMonth();
        unset($this->calendarData);
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
        unset($this->calendarData);
    }
}