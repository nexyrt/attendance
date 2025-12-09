<?php

namespace App\Livewire\Attendance\AllAttendance;

use App\Models\Attendance;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $quantity = 10;
    public ?string $search = null;
    public ?string $department_filter = null;
    public ?string $status_filter = null;
    public array $date_range = [];
    public array $sort = ['column' => 'check_in', 'direction' => 'desc'];

    // Modal properties
    public bool $modal = false;
    public ?Attendance $selectedAttendance = null;

    public function mount(): void
    {
        $this->date_range = [
            now()->startOfYear()->format('Y-m-d'),
            now()->format('Y-m-d')
        ];
    }

    public array $headers = [
        ['index' => 'user', 'label' => 'Karyawan'],
        ['index' => 'department', 'label' => 'Departemen', 'sortable' => false],
        ['index' => 'date', 'label' => 'Tanggal'],
        ['index' => 'check_in', 'label' => 'Check In'],
        ['index' => 'check_out', 'label' => 'Check Out'],
        ['index' => 'working_hours', 'label' => 'Jam Kerja'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'office', 'label' => 'Lokasi', 'sortable' => false],
        ['index' => 'notes', 'label' => 'Catatan', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.attendance.all-attendance.index');
    }

    public function showNotes(int $attendanceId)
    {
        $this->selectedAttendance = Attendance::with('user.department', 'checkInOffice', 'checkOutOffice')->find($attendanceId);
        $this->modal = true;
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

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        $startDate = $this->date_range[0] ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $this->date_range[1] ?? now()->format('Y-m-d');

        return Attendance::with(['user.department', 'checkInOffice'])
            ->has('user') // Only get attendance with existing user
            ->whereBetween('date', [$startDate, $endDate])
            ->when(
                $this->search,
                fn(Builder $query) =>
                $query->whereHas(
                    'user',
                    fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when(
                $this->department_filter,
                fn(Builder $query) =>
                $query->whereHas(
                    'user',
                    fn($q) =>
                    $q->where('department_id', $this->department_filter)
                )
            )
            ->when(
                $this->status_filter,
                fn(Builder $query) =>
                $query->where('status', $this->status_filter)
            )
            ->when(
                $this->sort['column'] === 'user',
                fn(Builder $query) =>
                $query->join('users', 'attendances.user_id', '=', 'users.id')
                    ->orderBy('users.name', $this->sort['direction'])
                    ->select('attendances.*')
                ,
                fn(Builder $query) =>
                $query->orderBy($this->sort['column'], $this->sort['direction'])
            )
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get();
    }

    #[Computed]
    public function stats(): array
    {
        $startDate = $this->date_range[0] ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $this->date_range[1] ?? now()->format('Y-m-d');
        $query = Attendance::whereBetween('date', [$startDate, $endDate]);

        return [
            'total' => $query->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'early_leave' => (clone $query)->where('status', 'early_leave')->count(),
        ];
    }
}
