<?php

namespace App\Livewire\LeaveRequests\Approvals;

use App\Models\LeaveRequest;
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
    public ?string $type = null;
    public ?int $employee_id = null;

    public array $sort = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'employee', 'label' => 'Karyawan'],
        ['index' => 'type', 'label' => 'Jenis Cuti'],
        ['index' => 'dates', 'label' => 'Tanggal Cuti', 'sortable' => false],
        ['index' => 'duration', 'label' => 'Durasi', 'sortable' => false],
        ['index' => 'reason', 'label' => 'Alasan', 'sortable' => false],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'created_at', 'label' => 'Diajukan'],
        ['index' => 'action', 'label' => 'Aksi', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.leave-requests.approvals.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->whereHas('user', function (Builder $query) {
                // Hanya leave request dari user dengan role staff dalam department yang sama
                $managerDepartment = Auth::user()->department_id;
                $query->where('department_id', $managerDepartment)
                      ->where('role', 'staff');
            })
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
            ->orderBy(...array_values($this->sort))
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed]
    public function statusStats(): array
    {
        $requests = LeaveRequest::whereHas('user', function (Builder $query) {
            $managerDepartment = Auth::user()->department_id;
            $query->where('department_id', $managerDepartment)
                  ->where('role', 'staff');
        })->get();

        return [
            'total' => $requests->count(),
            'pending_manager' => $requests->where('status', LeaveRequest::STATUS_PENDING_MANAGER)->count(),
            'approved_by_me' => $requests->where('manager_id', Auth::id())->whereNotNull('manager_approved_at')->count(),
            'pending_others' => $requests->whereIn('status', [
                LeaveRequest::STATUS_PENDING_HR,
                LeaveRequest::STATUS_PENDING_DIRECTOR
            ])->count(),
        ];
    }

    #[Computed]
    public function employees(): array
    {
        return User::where('department_id', Auth::user()->department_id)
                   ->where('role', 'staff')
                   ->orderBy('name')
                   ->get()
                   ->map(fn($user) => ['label' => $user->name, 'value' => $user->id])
                   ->prepend(['label' => 'Semua Karyawan', 'value' => ''])
                   ->toArray();
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'Semua Status',
            LeaveRequest::STATUS_PENDING_MANAGER => 'Menunggu Persetujuan Manager',
            LeaveRequest::STATUS_PENDING_HR => 'Menunggu Persetujuan HR',
            LeaveRequest::STATUS_PENDING_DIRECTOR => 'Menunggu Persetujuan Direktur',
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
