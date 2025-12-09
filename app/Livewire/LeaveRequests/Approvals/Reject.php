<?php

namespace App\Livewire\LeaveRequests\Approvals;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Reject extends Component
{
    use Alert;

    public ?LeaveRequest $leaveRequest = null;
    public bool $modal = false;
    public ?string $rejection_reason = null;

    public function render(): View
    {
        return view('livewire.manager.leave-request.reject');
    }

    #[On('load::reject-leave-request')]
    public function load(LeaveRequest $leaveRequest): void
    {
        $this->leaveRequest = $leaveRequest;
        $this->modal = true;
        $this->rejection_reason = null;
    }

    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    public function reject(): void
    {
        $this->validate();

        if (!$this->leaveRequest || $this->leaveRequest->status !== LeaveRequest::STATUS_PENDING_MANAGER) {
            $this->error('Pengajuan cuti tidak dapat ditolak');
            return;
        }

        // Restore leave balance if annual leave
        if ($this->leaveRequest->type === 'annual') {
            $leaveBalance = $this->leaveRequest->user->currentLeaveBalance();
            if ($leaveBalance) {
                $usedDays = $this->leaveRequest->getDurationInDays();
                $leaveBalance->updateBalance($leaveBalance->used_balance - $usedDays);
            }
        }

        $this->leaveRequest->update([
            'status' => LeaveRequest::STATUS_REJECTED_MANAGER,
            'manager_id' => Auth::id(),
            'rejection_reason' => $this->rejection_reason,
        ]);

        $this->dispatch('rejected');
        $this->modal = false;
        $this->resetExcept('leaveRequest');
        $this->success('Pengajuan cuti berhasil ditolak');
    }
}
