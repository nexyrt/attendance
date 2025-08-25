<?php

namespace App\Livewire\Director\LeaveRequest;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\Department;
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
    public ?string $status = LeaveRequest::STATUS_PENDING_DIRECTOR;
    public ?string $type = null;
    public ?int $employee_id = null;
    public ?int $department_id = null;

    public array $sort = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'employee', 'label' => 'Karyawan'],
        ['index' => 'department', 'label' => 'Departemen'],
        ['index' => 'type', 'label' => 'Jenis Cuti'],
        ['index' => 'dates', 'label' => 'Tanggal Cuti', 'sortable' => false],
        ['index' => 'duration', 'label' => 'Durasi', 'sortable' => false],
        ['index' => 'approvals', 'label' => 'Persetujuan', 'sortable' => false],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'created_at', 'label' => 'Diajukan'],
        ['index' => 'action', 'label' => 'Aksi', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.director.leave-request.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->with(['user', 'user.department', 'manager', 'hr', 'director'])
            ->when(
                $this->search,
                fn(Builder $query) =>
                $query->where('reason', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            )
            ->when(
                $this->status,
                fn(Builder $query) =>
                $query->where('status', $this->status)
            )
            ->when(
                $this->type,
                fn(Builder $query) =>
                $query->where('type', $this->type)
            )
            ->when(
                $this->employee_id,
                fn(Builder $query) =>
                $query->where('user_id', $this->employee_id)
            )
            ->when(
                $this->department_id,
                fn(Builder $query) =>
                $query->whereHas('user', fn($q) => $q->where('department_id', $this->department_id))
            )
            ->orderBy(...array_values($this->sort))
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed]
    public function statusStats(): array
    {
        $requests = LeaveRequest::all();

        return [
            'total' => $requests->count(),
            'pending_director' => $requests->where('status', LeaveRequest::STATUS_PENDING_DIRECTOR)->count(),
            'approved_by_director' => $requests->where('director_id', Auth::id())->whereNotNull('director_approved_at')->count(),
            'final_approved' => $requests->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => $requests->where('status', 'like', 'rejected_%')->count(),
        ];
    }

    #[Computed]
    public function employees(): array
    {
        return User::where('role', '!=', 'director')
            ->orderBy('name')
            ->get()
            ->map(fn($user) => ['label' => $user->name, 'value' => $user->id])
            ->prepend(['label' => 'Semua Karyawan', 'value' => ''])
            ->toArray();
    }

    #[Computed]
    public function departments(): array
    {
        return Department::orderBy('name')
            ->get()
            ->map(fn($dept) => ['label' => $dept->name, 'value' => $dept->id])
            ->prepend(['label' => 'Semua Departemen', 'value' => ''])
            ->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'Semua Status',
            LeaveRequest::STATUS_PENDING_MANAGER => 'Menunggu Manager',
            LeaveRequest::STATUS_PENDING_HR => 'Menunggu HR',
            LeaveRequest::STATUS_PENDING_DIRECTOR => 'Menunggu Direktur',
            LeaveRequest::STATUS_APPROVED => 'Disetujui',
            LeaveRequest::STATUS_REJECTED_MANAGER => 'Ditolak Manager',
            LeaveRequest::STATUS_REJECTED_HR => 'Ditolak HR',
            LeaveRequest::STATUS_REJECTED_DIRECTOR => 'Ditolak Direktur',
            LeaveRequest::STATUS_CANCEL => 'Dibatalkan',
        ];
    }

    public function getTypeOptions(): array
    {
        return [
            '' => 'Semua Jenis',
            LeaveRequest::TYPE_SICK => 'Cuti Sakit',
            LeaveRequest::TYPE_ANNUAL => 'Cuti Tahunan',
            LeaveRequest::TYPE_IMPORTANT => 'Cuti Penting',
            LeaveRequest::TYPE_OTHER => 'Lainnya',
        ];
    }
}