<?php

namespace App\Livewire\Staff\Attendance;

use App\Models\Attendance;
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
    public ?string $month = null;

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
    ];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function render(): View
    {
        return view('livewire.staff.attendance.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return Attendance::query()
            ->where('user_id', Auth::id())
            ->with(['checkInOffice', 'checkOutOffice'])
            ->when($this->search, fn(Builder $query) => 
                $query->whereDate('date', $this->search)
            )
            ->when(!$this->search && $this->month, fn(Builder $query) => 
                $query->whereYear('date', substr($this->month, 0, 4))
                      ->whereMonth('date', substr($this->month, 5, 2))
            )
            ->when($this->status, fn(Builder $query) => 
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
        $year = substr($this->month, 0, 4);
        $monthNum = substr($this->month, 5, 2);

        $attendances = Attendance::where('user_id', $userId)
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->get();

        return [
            'total_days' => $attendances->count(),
            'present_days' => $attendances->where('status', 'present')->count(),
            'late_days' => $attendances->where('status', 'late')->count(),
            'total_hours' => $attendances->sum('working_hours'),
            'avg_hours' => $attendances->avg('working_hours') ?? 0,
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'All Status',
            'present' => 'Present',
            'late' => 'Late',
            'early_leave' => 'Early Leave',
            'holiday' => 'Holiday',
        ];
    }
}