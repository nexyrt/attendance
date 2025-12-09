<?php

namespace App\Livewire\LeaveRequests\Approvals;

use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Detail extends Component
{
    public ?LeaveRequest $leaveRequest = null;
    public bool $modal = false;

    public function render(): View
    {
        return view('livewire.leave-requests.approvals.detail');
    }

    #[On('load::leave-request-detail')]
    public function load(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequest = $leaveRequest->load(['user', 'user.department', 'manager', 'hr', 'director']);
        $this->modal = true;
    }

    public function print(): void
    {
        $this->dispatch('print-leave-request', ['id' => $this->leaveRequest->id]);
    }
}
