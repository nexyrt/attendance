<?php

namespace App\Livewire\Manager\TeamAttendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Analytics extends Component
{
    public bool $modal = false;
    public ?string $period = 'month';
    public ?string $month = null;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render(): View
    {
        return view('livewire.manager.team-attendance.analytics');
    }

    #[Computed]
    public function chartData(): array
    {
        return match ($this->period) {
            'week' => $this->getWeeklyChart(),
            'month' => $this->getMonthlyChart(),
            'quarter' => $this->getQuarterlyChart(),
            default => $this->getMonthlyChart(),
        };
    }

    #[Computed]
    public function attendanceTrends(): array
    {
        $departmentId = Auth::user()->department_id;
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        // Get daily attendance for the month
        $dailyStats = Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->selectRaw('DATE(date) as day, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late,
                        AVG(working_hours) as avg_hours')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return $dailyStats->map(function ($stat) {
            return [
                'date' => $stat->day,
                'total' => $stat->total,
                'present' => $stat->present,
                'late' => $stat->late,
                'attendance_rate' => $stat->total > 0 ? round(($stat->present / $stat->total) * 100, 1) : 0,
                'avg_hours' => round($stat->avg_hours ?? 0, 2),
            ];
        })->toArray();
    }

    #[Computed]
    public function topPerformers(): array
    {
        $departmentId = Auth::user()->department_id;
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        return User::where('department_id', $departmentId)
            ->where('id', '!=', Auth::id())
            ->withCount([
                'attendances as total_days' => fn(Builder $query) =>
                    $query->whereYear('date', $year)->whereMonth('date', $monthNum),
                'attendances as present_days' => fn(Builder $query) =>
                    $query->whereYear('date', $year)->whereMonth('date', $monthNum)
                        ->where('status', 'present'),
            ])
            ->withSum([
                'attendances as total_hours' => fn(Builder $query) =>
                    $query->whereYear('date', $year)->whereMonth('date', $monthNum)
            ], 'working_hours')
            ->get()
            ->map(function ($user) {
                $attendanceRate = $user->total_days > 0
                    ? round(($user->present_days / $user->total_days) * 100, 1)
                    : 0;

                return [
                    'user' => $user,
                    'attendance_rate' => $attendanceRate,
                    'total_hours' => round($user->total_hours ?? 0, 2),
                    'avg_daily_hours' => $user->total_days > 0
                        ? round(($user->total_hours ?? 0) / $user->total_days, 2)
                        : 0,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->take(5)
            ->values()
            ->toArray();
    }

    #[Computed]
    public function lateArrivals(): array
    {
        $departmentId = Auth::user()->department_id;
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        return Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->with('user')
            ->where('status', 'late')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('late_hours', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($attendance) {
                return [
                    'user' => $attendance->user,
                    'date' => $attendance->date,
                    'check_in' => $attendance->check_in,
                    'late_hours' => $attendance->late_hours,
                ];
            })
            ->toArray();
    }

    private function getWeeklyChart(): array
    {
        $departmentId = Auth::user()->department_id;
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $data = Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->selectRaw('DAYNAME(date) as day_name, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->groupBy('day_name')
            ->orderByRaw('FIELD(day_name, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday")')
            ->get();

        return [
            'labels' => $data->pluck('day_name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $data->pluck('present')->toArray(),
                    'backgroundColor' => '#10B981',
                ],
                [
                    'label' => 'Late',
                    'data' => $data->pluck('late')->toArray(),
                    'backgroundColor' => '#F59E0B',
                ],
            ],
        ];
    }

    private function getMonthlyChart(): array
    {
        $departmentId = Auth::user()->department_id;
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        $data = Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->selectRaw('DAY(date) as day, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return [
            'labels' => $data->pluck('day')->toArray(),
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $data->pluck('present')->toArray(),
                    'backgroundColor' => '#10B981',
                ],
                [
                    'label' => 'Late',
                    'data' => $data->pluck('late')->toArray(),
                    'backgroundColor' => '#F59E0B',
                ],
            ],
        ];
    }

    private function getQuarterlyChart(): array
    {
        $departmentId = Auth::user()->department_id;
        $currentQuarter = now()->quarter;
        $year = now()->year;

        $months = match ($currentQuarter) {
            1 => [1, 2, 3],
            2 => [4, 5, 6],
            3 => [7, 8, 9],
            4 => [10, 11, 12],
        };

        $data = Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->whereYear('date', $year)
            ->whereIn(\DB::raw('MONTH(date)'), $months)
            ->selectRaw('MONTHNAME(date) as month_name, 
                        COUNT(*) as total,
                        SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late')
            ->groupBy('month_name')
            ->orderByRaw('MONTH(date)')
            ->get();

        return [
            'labels' => $data->pluck('month_name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $data->pluck('present')->toArray(),
                    'backgroundColor' => '#10B981',
                ],
                [
                    'label' => 'Late',
                    'data' => $data->pluck('late')->toArray(),
                    'backgroundColor' => '#F59E0B',
                ],
            ],
        ];
    }

    public function getPeriodOptions(): array
    {
        return [
            'week' => 'This Week',
            'month' => 'This Month',
            'quarter' => 'This Quarter',
        ];
    }
}