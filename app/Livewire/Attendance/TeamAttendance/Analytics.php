<?php

namespace App\Livewire\Attendance\TeamAttendance;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Collection;

class Analytics extends Component
{
    public $dateRange = 'week';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->setDateRange();
    }

    public array $performanceHeaders = [
        ['index' => 'name', 'label' => 'Staff'],
        ['index' => 'total_days', 'label' => 'Total Days'],
        ['index' => 'present_days', 'label' => 'Present'],
        ['index' => 'late_days', 'label' => 'Late'],
        ['index' => 'early_leave_days', 'label' => 'Early Leave'],
        ['index' => 'avg_working_hours', 'label' => 'Avg Hours'],
        ['index' => 'attendance_rate', 'label' => 'Rate'],
    ];

    public function render()
    {
        return view('livewire.manager.team-attendance.analytics', [
            'attendanceTrends' => $this->getAttendanceTrends(),
            'individualStats' => $this->getIndividualStats(),
            'weeklyComparison' => $this->getWeeklyComparison(),
            'punctualityStats' => $this->getPunctualityStats(),
        ]);
    }

    public function updatedDateRange()
    {
        $this->setDateRange();
    }

    private function setDateRange()
    {
        match ($this->dateRange ?? 'week') {
            'week' => [
                $this->startDate = now()->startOfWeek()->format('Y-m-d'),
                $this->endDate = now()->endOfWeek()->format('Y-m-d')
            ],
            'month' => [
                $this->startDate = now()->startOfMonth()->format('Y-m-d'),
                $this->endDate = now()->endOfMonth()->format('Y-m-d')
            ],
            'quarter' => [
                $this->startDate = now()->startOfQuarter()->format('Y-m-d'),
                $this->endDate = now()->endOfQuarter()->format('Y-m-d')
            ],
            default => [
                $this->startDate = now()->startOfWeek()->format('Y-m-d'),
                $this->endDate = now()->endOfWeek()->format('Y-m-d')
            ]
        };
    }

    private function getAttendanceTrends()
    {
        $managerDepartment = auth()->user()->department_id;

        return Attendance::whereBetween('date', [$this->startDate, $this->endDate])
            ->whereHas('user', function ($query) use ($managerDepartment) {
                $query->where('department_id', $managerDepartment)
                    ->where('role', 'staff');
            })
            ->selectRaw('DATE(date) as date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->map(function ($dayData) {
                return $dayData->pluck('count', 'status')->toArray();
            });
    }

    private function getIndividualStats()
    {
        $managerDepartment = auth()->user()->department_id;

        return User::with([
            'attendances' => function ($query) {
                $query->whereBetween('date', [$this->startDate, $this->endDate]);
            }
        ])
            ->where('department_id', $managerDepartment)
            ->where('role', 'staff')
            ->get()
            ->map(function ($user) {
                $attendances = $user->attendances;
                $totalDays = $attendances->count();

                return [
                    'name' => $user->name,
                    'total_days' => $totalDays,
                    'present_days' => $attendances->where('status', 'present')->count(),
                    'late_days' => $attendances->where('status', 'late')->count(),
                    'early_leave_days' => $attendances->where('status', 'early_leave')->count(),
                    'avg_working_hours' => $attendances->avg('working_hours') ?? 0,
                    'attendance_rate' => $totalDays > 0 ? round(($attendances->whereIn('status', ['present', 'late'])->count() / $totalDays) * 100, 1) : 0,
                ];
            });
    }

    private function getWeeklyComparison()
    {
        $managerDepartment = auth()->user()->department_id;
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();

        $getCurrentWeek = function () use ($managerDepartment, $currentWeek) {
            return Attendance::whereBetween('date', [$currentWeek, $currentWeek->copy()->endOfWeek()])
                ->whereHas('user', function ($query) use ($managerDepartment) {
                    $query->where('department_id', $managerDepartment)->where('role', 'staff');
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        };

        $getPreviousWeek = function () use ($managerDepartment, $previousWeek) {
            return Attendance::whereBetween('date', [$previousWeek, $previousWeek->copy()->endOfWeek()])
                ->whereHas('user', function ($query) use ($managerDepartment) {
                    $query->where('department_id', $managerDepartment)->where('role', 'staff');
                })
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        };

        return [
            'current' => $getCurrentWeek(),
            'previous' => $getPreviousWeek(),
        ];
    }

    private function getPunctualityStats()
    {
        $managerDepartment = auth()->user()->department_id;

        $lateArrivals = Attendance::whereBetween('date', [$this->startDate, $this->endDate])
            ->whereHas('user', function ($query) use ($managerDepartment) {
                $query->where('department_id', $managerDepartment)->where('role', 'staff');
            })
            ->where('status', 'late')
            ->with('user:id,name')
            ->get()
            ->groupBy('user.name')
            ->map->count()
            ->sortDesc()
            ->take(5);

        $earlyLeavers = Attendance::whereBetween('date', [$this->startDate, $this->endDate])
            ->whereHas('user', function ($query) use ($managerDepartment) {
                $query->where('department_id', $managerDepartment)->where('role', 'staff');
            })
            ->where('status', 'early_leave')
            ->with('user:id,name')
            ->get()
            ->groupBy('user.name')
            ->map->count()
            ->sortDesc()
            ->take(5);

        return [
            'late_arrivals' => $lateArrivals,
            'early_leavers' => $earlyLeavers,
        ];
    }

    public function getChangePercentage($current, $previous)
    {
        if ($previous == 0)
            return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
