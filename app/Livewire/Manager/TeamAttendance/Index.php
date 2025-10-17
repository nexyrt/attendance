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
    public ?string $search = null;
    public ?string $statusFilter = null;
    public array $dateRange = [];

    // Modal properties
    public bool $modal = false;
    public ?Attendance $selectedAttendance = null;

    public array $sort = [
        'column' => 'date',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'user', 'label' => 'Staff Name'],
        ['index' => 'date', 'label' => 'Date'],
        ['index' => 'check_in', 'label' => 'Check In'],
        ['index' => 'check_out', 'label' => 'Check Out'],
        ['index' => 'working_hours', 'label' => 'Working Hours'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'office', 'label' => 'Location', 'sortable' => false],
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

    public function showNotes(int $attendanceId)
    {
        $this->selectedAttendance = Attendance::with('user', 'checkInOffice', 'checkOutOffice')->find($attendanceId);
        $this->modal = true;
    }

    public function updatedDateRange()
    {
        $this->resetPage();
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $managerDepartment = auth()->user()->department_id;
        $startDate = $this->dateRange[0] ?? now()->startOfWeek()->format('Y-m-d');
        $endDate = $this->dateRange[1] ?? now()->endOfWeek()->format('Y-m-d');

        $query = Attendance::with(['user', 'checkInOffice'])
            ->whereHas('user', function ($q) use ($managerDepartment) {
                $q->where('department_id', $managerDepartment)
                    ->where('role', 'staff');
            })
            ->whereBetween('date', [$startDate, $endDate])
            ->when($this->search, function (Builder $query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            });

        // Handle sorting
        if ($this->sort['column'] === 'user') {
            $query->join('users', 'attendances.user_id', '=', 'users.id')
                ->orderBy('users.name', $this->sort['direction'])
                ->select('attendances.*');
        } else {
            $query->orderBy($this->sort['column'], $this->sort['direction']);
        }

        return $query->paginate($this->quantity)->withQueryString();
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
            'total_records' => $totalAttendanceRecords,
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
            'holiday' => 'blue',
            'pending present' => 'gray',
            default => 'gray'
        };
    }
}