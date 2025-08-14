<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Models\LeaveRequest;
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

    public array $sort = [
        'column' => 'created_at',
        'direction' => 'desc',
    ];

    public array $headers = [
        ['index' => 'type', 'label' => 'Type'],
        ['index' => 'dates', 'label' => 'Leave Dates', 'sortable' => false],
        ['index' => 'duration', 'label' => 'Duration', 'sortable' => false],
        ['index' => 'reason', 'label' => 'Reason', 'sortable' => false],
        ['index' => 'status', 'label' => 'Status'],
        ['index' => 'created_at', 'label' => 'Submitted'],
        ['index' => 'action', 'label' => 'Action', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.staff.leave-request.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return LeaveRequest::query()
            ->where('user_id', Auth::id())
            ->with(['manager', 'hr', 'director'])
            ->when(
                $this->search,
                fn(Builder $query) =>
                $query->where('reason', 'like', "%{$this->search}%")
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
            ->orderBy(...array_values($this->sort))
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Computed]
    public function statusStats(): array
    {
        $requests = LeaveRequest::where('user_id', Auth::id())->get();

        return [
            'total' => $requests->count(),
            'pending' => $requests->whereIn('status', [
                LeaveRequest::STATUS_PENDING_MANAGER,
                LeaveRequest::STATUS_PENDING_HR,
                LeaveRequest::STATUS_PENDING_DIRECTOR
            ])->count(),
            'approved' => $requests->where('status', LeaveRequest::STATUS_APPROVED)->count(),
            'rejected' => $requests->whereIn('status', [
                LeaveRequest::STATUS_REJECTED_MANAGER,
                LeaveRequest::STATUS_REJECTED_HR,
                LeaveRequest::STATUS_REJECTED_DIRECTOR
            ])->count(),
        ];
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'All Status',
            LeaveRequest::STATUS_PENDING_MANAGER => 'Pending Manager',
            LeaveRequest::STATUS_PENDING_HR => 'Pending HR',
            LeaveRequest::STATUS_PENDING_DIRECTOR => 'Pending Director',
            LeaveRequest::STATUS_APPROVED => 'Approved',
            LeaveRequest::STATUS_REJECTED_MANAGER => 'Rejected by Manager',
            LeaveRequest::STATUS_REJECTED_HR => 'Rejected by HR',
            LeaveRequest::STATUS_REJECTED_DIRECTOR => 'Rejected by Director',
            LeaveRequest::STATUS_CANCEL => 'Cancelled',
        ];
    }

    public function getTypeOptions(): array
    {
        return [
            '' => 'All Types',
            LeaveRequest::TYPE_SICK => 'Sick Leave',
            LeaveRequest::TYPE_ANNUAL => 'Annual Leave',
            LeaveRequest::TYPE_IMPORTANT => 'Important Leave',
            LeaveRequest::TYPE_OTHER => 'Other',
        ];
    }
}