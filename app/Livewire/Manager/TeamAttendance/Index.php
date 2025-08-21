<?php

namespace App\Livewire\Manager\TeamAttendance;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class Index extends Component
{
    use WithPagination;

    public ?int $quantity = 10;
    public $dateRange = [];
    public ?string $search = null;
    public ?string $statusFilter = null;

    public array $sort = [
        'column' => 'name',
        'direction' => 'asc',
    ];

    public array $headers = [
        ['index' => 'name', 'label' => 'Staff Name'],
        ['index' => 'email', 'label' => 'Email'],
        ['index' => 'latest_date', 'label' => 'Latest Date'],
        ['index' => 'check_in', 'label' => 'Check In'],
        ['index' => 'check_out', 'label' => 'Check Out'],
        ['index' => 'avg_hours', 'label' => 'Avg Hours'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'notes', 'label' => 'Notes', 'sortable' => false],
    ];

    public function mount()
    {
        $this->dateRange = [
            now()->startOfWeek()->format('Y-m-d'),
            now()->endOfWeek()->format('Y-m-d')
        ];
    }

    public function render()
    {
        return view('livewire.manager.team-attendance.index', [
            'attendanceStats' => $this->getAttendanceStats(),
        ]);
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $managerDepartment = auth()->user()->department_id;
        $startDate = $this->dateRange[0] ?? now()->startOfWeek()->format('Y-m-d');
        $endDate = $this->dateRange[1] ?? now()->endOfWeek()->format('Y-m-d');

        $query = User::with([
            'attendances' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date', 'desc');
            }
        ])
            ->where('department_id', $managerDepartment)
            ->where('role', 'staff')
            ->when($this->search, function (Builder $query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function (Builder $query) use ($startDate, $endDate) {
                $query->whereHas('attendances', function ($attendanceQuery) use ($startDate, $endDate) {
                    $attendanceQuery->whereBetween('date', [$startDate, $endDate])
                        ->where('status', $this->statusFilter);
                });
            });

        // Handle custom sorting for attendance-related columns
        if ($this->sort['column'] === 'latest_date') {
            $query->leftJoin('attendances', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
                ->select('users.*')
                ->groupBy('users.id')
                ->orderBy(\DB::raw('MAX(attendances.date)'), $this->sort['direction']);
        } elseif ($this->sort['column'] === 'status') {
            $query->leftJoin('attendances', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
                ->select('users.*')
                ->groupBy('users.id')
                ->orderBy(\DB::raw('MAX(attendances.status)'), $this->sort['direction']);
        } elseif ($this->sort['column'] === 'check_in') {
            $query->leftJoin('attendances', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
                ->select('users.*')
                ->groupBy('users.id')
                ->orderBy(\DB::raw('MAX(attendances.check_in)'), $this->sort['direction']);
        } elseif ($this->sort['column'] === 'check_out') {
            $query->leftJoin('attendances', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
                ->select('users.*')
                ->groupBy('users.id')
                ->orderBy(\DB::raw('MAX(attendances.check_out)'), $this->sort['direction']);
        } elseif ($this->sort['column'] === 'avg_hours') {
            $query->leftJoin('attendances', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendances.user_id')
                    ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
                ->select('users.*')
                ->groupBy('users.id')
                ->orderBy(\DB::raw('AVG(attendances.working_hours)'), $this->sort['direction']);
        } else {
            $query->orderBy(...array_values($this->sort));
        }

        return $query->paginate($this->quantity)
            ->withQueryString();
    }

    private function getAttendanceStats()
    {
        $managerDepartment = auth()->user()->department_id;
        $startDate = $this->dateRange[0] ?? now()->startOfWeek()->format('Y-m-d');
        $endDate = $this->dateRange[1] ?? now()->endOfWeek()->format('Y-m-d');

        $totalStaff = User::where('department_id', $managerDepartment)
            ->where('role', 'staff')
            ->count();

        $attendanceData = Attendance::whereBetween('date', [$startDate, $endDate])
            ->whereHas('user', function ($query) use ($managerDepartment) {
                $query->where('department_id', $managerDepartment)
                    ->where('role', 'staff');
            })
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $totalAttendanceRecords = array_sum($attendanceData);
        $workingDays = $this->getWorkingDaysBetween($startDate, $endDate);
        $expectedRecords = $totalStaff * $workingDays;

        return [
            'total_staff' => $totalStaff,
            'present' => $attendanceData['present'] ?? 0,
            'late' => $attendanceData['late'] ?? 0,
            'absent' => max(0, $expectedRecords - $totalAttendanceRecords),
            'early_leave' => $attendanceData['early_leave'] ?? 0,
        ];
    }

    private function getWorkingDaysBetween($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workingDays = 0;

        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workingDays++;
            }
            $start->addDay();
        }

        return $workingDays;
    }

    public function getStatusBadgeColor($status)
    {
        return match ($status) {
            'present' => 'green',
            'late' => 'yellow',
            'early_leave' => 'orange',
            'absent' => 'red',
            default => 'gray'
        };
    }
}