<?php

namespace App\Livewire\Manager\TeamAttendance;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $quantity = 10;
    public ?string $search = null;
    public ?string $status = null;
    public ?string $member = null;
    public ?string $date = null;
    public ?string $month = null;

    public array $sort = [
        'column' => 'date',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'user', 'label' => 'Staff Member', 'sortable' => false],
        ['index' => 'date', 'label' => 'Date'],
        ['index' => 'check_in', 'label' => 'Check In'],
        ['index' => 'check_out', 'label' => 'Check Out'],
        ['index' => 'working_hours', 'label' => 'Hours'],
        ['index' => 'late_hours', 'label' => 'Late Hours'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'office', 'label' => 'Office', 'sortable' => false],
    ];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render(): View
    {
        return view('livewire.manager.team-attendance.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return Attendance::query()
            ->whereHas(
                'user',
                fn(Builder $query) =>
                $query->where('department_id', Auth::user()->department_id)
            )
            ->with(['user', 'checkInOffice', 'checkOutOffice'])
            ->when(
                $this->search,
                fn(Builder $query) =>
                $query->whereHas(
                    'user',
                    fn(Builder $subQuery) =>
                    $subQuery->where('name', 'like', "%{$this->search}%")
                )
            )
            ->when(
                $this->member,
                fn(Builder $query) =>
                $query->where('user_id', $this->member)
            )
            ->when(
                $this->date,
                fn(Builder $query) =>
                $query->whereDate('date', $this->date)
            )
            ->when(
                !$this->date && $this->month,
                fn(Builder $query) =>
                $query->whereYear('date', substr($this->month, 0, 4))
                    ->whereMonth('date', substr($this->month, 5, 2))
            )
            ->when(
                $this->status,
                fn(Builder $query) =>
                $query->where('status', $this->status)
            )
            ->orderBy(...array_values($this->sort))
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed]
    public function teamMembers(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('department_id', Auth::user()->department_id)
            ->where('id', '!=', Auth::id())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function monthlyStats(): array
    {
        $departmentId = Auth::user()->department_id;
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        $attendances = Attendance::whereHas(
            'user',
            fn(Builder $query) =>
            $query->where('department_id', $departmentId)
        )
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->get();

        $totalMembers = $this->teamMembers->count();
        $workingDaysInMonth = $this->getWorkingDaysInMonth($year, $monthNum);

        return [
            'total_attendances' => $attendances->count(),
            'present_count' => $attendances->where('status', 'present')->count(),
            'late_count' => $attendances->where('status', 'late')->count(),
            'early_leave_count' => $attendances->where('status', 'early_leave')->count(),
            'total_working_hours' => round($attendances->sum('working_hours'), 2),
            'total_late_hours' => round($attendances->sum('late_hours'), 2),
            'attendance_rate' => $totalMembers > 0 && $workingDaysInMonth > 0
                ? round(($attendances->count() / ($totalMembers * $workingDaysInMonth)) * 100, 1)
                : 0,
            'punctuality_rate' => $attendances->count() > 0
                ? round(($attendances->where('status', 'present')->count() / $attendances->count()) * 100, 1)
                : 0,
        ];
    }

    #[Computed]
    public function teamPerformance(): array
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
                'attendances as late_days' => fn(Builder $query) =>
                    $query->whereYear('date', $year)->whereMonth('date', $monthNum)
                        ->where('status', 'late'),
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
                    'total_days' => $user->total_days,
                    'present_days' => $user->present_days,
                    'late_days' => $user->late_days,
                    'total_hours' => round($user->total_hours ?? 0, 2),
                    'attendance_rate' => $attendanceRate,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->values()
            ->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'All Status',
            'present' => 'Present',
            'late' => 'Late',
            'early_leave' => 'Early Leave',
            'holiday' => 'Holiday',
            'pending present' => 'Pending Present',
        ];
    }

    public function getMemberOptions(): array
    {
        return $this->teamMembers->pluck('name', 'id')->prepend('All Members', '')->toArray();
    }

    private function getWorkingDaysInMonth(string $year, string $month): int
    {
        $startDate = \Carbon\Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $workingDays = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($date->isWeekday()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}