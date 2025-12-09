<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MyAttendance extends Component
{
    use WithPagination;

    public ?int $quantity = 10;
    public ?string $status = null;
    public ?string $search = null;
    public array $dateRange = [];
    public bool $modal = false;

    public array $sort = [
        'column' => 'date',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'date', 'label' => 'Date'],
        ['index' => 'check_in', 'label' => 'Check In'],
        ['index' => 'check_out', 'label' => 'Check Out'],
        ['index' => 'working_hours', 'label' => 'Hours'],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'office', 'label' => 'Office', 'sortable' => false],
        ['index' => 'action', 'label' => '', 'sortable' => false],
    ];

    public ?int $selectedAttendanceId = null;

    public function mount(): void
    {
        // Set default date range to current month
        $this->dateRange = [
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        ];
    }

    public function render(): View
    {
        return view('livewire.attendance.my-attendance');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return Attendance::query()
            ->where('user_id', Auth::id())
            ->with(['checkInOffice', 'checkOutOffice'])
            ->when(
                count($this->dateRange) === 2,
                fn(Builder $query) =>
                $query->whereBetween('date', $this->dateRange)
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
    public function monthlyStats(): array
    {
        $userId = Auth::id();

        $attendances = Attendance::where('user_id', $userId)
            ->when(
                count($this->dateRange) === 2,
                fn(Builder $query) =>
                $query->whereBetween('date', $this->dateRange)
            )
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'early_leave_days' => $attendances->where('status', 'early_leave')->count(),
            'total_hours' => $attendances->sum('working_hours'),
            'avg_hours' => $attendances->avg('working_hours') ?? 0,
            'total_late_hours' => $attendances->sum('late_hours'),
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            ['label' => 'All Status', 'value' => ''],
            ['label' => 'Present', 'value' => 'present'],
            ['label' => 'Late', 'value' => 'late'],
            ['label' => 'Early Leave', 'value' => 'early_leave'],
            ['label' => 'Holiday', 'value' => 'holiday'],
        ];
    }

    public function resetFilters(): void
    {
        $this->reset(['status']);
        $this->dateRange = [
            now()->startOfMonth()->format('Y-m-d'),
            now()->endOfMonth()->format('Y-m-d')
        ];
        $this->resetPage();
    }

    public function viewNotes(int $attendanceId): void
    {
        $this->modal = true;
        $this->selectedAttendanceId = $attendanceId;
    }

    #[Computed]
    public function selectedAttendance(): ?Attendance
    {
        if (!$this->selectedAttendanceId) {
            return null;
        }

        return Attendance::with(['checkInOffice', 'checkOutOffice'])
            ->find($this->selectedAttendanceId);
    }
}
