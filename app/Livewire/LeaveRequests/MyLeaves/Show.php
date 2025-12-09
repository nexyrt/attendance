<?php

namespace App\Livewire\LeaveRequests\MyLeaves;

use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public bool $modal = false;
    public ?int $leaveRequestId = null;

    public function render(): View
    {
        return view('livewire.leave-requests.my-leaves.show');
    }

    #[On('show::leave-request')]
    public function load(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequestId = $leaveRequest->id;
        $this->modal = true;
    }

    public function getLeaveRequestProperty(): ?LeaveRequest
    {
        if (!$this->leaveRequestId)
            return null;

        return LeaveRequest::with(['user', 'manager', 'hr', 'director'])
            ->find($this->leaveRequestId);
    }
}
