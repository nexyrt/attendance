<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Alert;

    // Filter state
    public string $activeTab = 'all';
    public ?int $quantity = 10;
    public ?string $search = null;
    public array $sort = ['column' => 'created_at', 'direction' => 'desc'];

    public array $headers = [
        ['index' => 'type', 'label' => 'Jenis'],
        ['index' => 'dates', 'label' => 'Tanggal', 'sortable' => false],
        ['index' => 'duration', 'label' => 'Durasi', 'sortable' => false],
        ['index' => 'status', 'label' => 'Status', 'sortable' => false],
        ['index' => 'action', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.staff.leave-request.index');
    }

    // Stats Cards Data
    #[Computed]
    public function stats(): array
    {
        $leaveBalance = auth()->user()->currentLeaveBalance();

        $myRequests = LeaveRequest::where('user_id', auth()->id());

        return [
            [
                'label' => 'Sisa Cuti',
                'value' => $leaveBalance?->remaining_balance ?? 0,
                'icon' => 'calendar-days',
                'color' => 'primary',
            ],
            [
                'label' => 'Menunggu',
                'value' => (clone $myRequests)->pending()->count(),
                'icon' => 'clock',
                'color' => 'amber',
            ],
            [
                'label' => 'Disetujui',
                'value' => (clone $myRequests)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
                'icon' => 'check-circle',
                'color' => 'green',
            ],
            [
                'label' => 'Ditolak',
                'value' => (clone $myRequests)->whereIn('status', [
                    LeaveRequest::STATUS_REJECTED_MANAGER,
                    LeaveRequest::STATUS_REJECTED_HR,
                    LeaveRequest::STATUS_REJECTED_DIRECTOR,
                ])->count(),
                'icon' => 'x-circle',
                'color' => 'red',
            ],
        ];
    }

    // Leave Balance Data
    #[Computed]
    public function leaveBalance(): ?object
    {
        return auth()->user()->currentLeaveBalance();
    }

    // Table Data with Filters
    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->where('user_id', auth()->id())
            ->when($this->activeTab === 'pending', fn(Builder $q) => $q->pending())
            ->when($this->activeTab === 'approved', fn(Builder $q) => $q->where('status', LeaveRequest::STATUS_APPROVED))
            ->when($this->activeTab === 'rejected', fn(Builder $q) => $q->whereIn('status', [
                LeaveRequest::STATUS_REJECTED_MANAGER,
                LeaveRequest::STATUS_REJECTED_HR,
                LeaveRequest::STATUS_REJECTED_DIRECTOR,
            ]))
            ->when(
                $this->search,
                fn(Builder $q) =>
                $q->where(function ($query) {
                    $query->where('reason', 'like', '%' . trim($this->search) . '%')
                        ->orWhere('type', 'like', '%' . trim($this->search) . '%');
                })
            )
            ->orderBy($this->sort['column'], $this->sort['direction'])
            ->paginate($this->quantity)
            ->withQueryString();
    }

    // Change active tab
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    // Get tab counts
    #[Computed]
    public function tabCounts(): array
    {
        $myRequests = LeaveRequest::where('user_id', auth()->id());

        return [
            'all' => (clone $myRequests)->count(),
            'pending' => (clone $myRequests)->pending()->count(),
            'approved' => (clone $myRequests)->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $myRequests)->whereIn('status', [
                LeaveRequest::STATUS_REJECTED_MANAGER,
                LeaveRequest::STATUS_REJECTED_HR,
                LeaveRequest::STATUS_REJECTED_DIRECTOR,
            ])->count(),
        ];
    }
}