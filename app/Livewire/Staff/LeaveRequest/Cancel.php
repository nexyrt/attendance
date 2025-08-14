<?php

namespace App\Livewire\Staff\LeaveRequest;

use App\Livewire\Traits\Alert;
use App\Models\LeaveRequest;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Cancel extends Component
{
    use Alert;

    public LeaveRequest $leaveRequest;

    public function render(): string
    {
        return <<<'HTML'
        <div>
            <x-button.circle icon="x-mark" color="red" wire:click="confirm" />
        </div>
        HTML;
    }

    #[Renderless]
    public function confirm(): void
    {
        $this->question('Cancel Leave Request?', 'This action cannot be undone.')
            ->confirm(method: 'cancel')
            ->cancel()
            ->send();
    }

    public function cancel(): void
    {
        if (!$this->leaveRequest->canBeCancelled()) {
            $this->error('This request cannot be cancelled');
            return;
        }

        $this->leaveRequest->cancel();

        // Restore leave balance if annual leave
        if ($this->leaveRequest->type === 'annual') {
            $leaveBalance = $this->leaveRequest->user->currentLeaveBalance();
            if ($leaveBalance) {
                $usedDays = $this->leaveRequest->getDurationInDays();
                $leaveBalance->updateBalance($leaveBalance->used_balance - $usedDays);
            }
        }

        $this->dispatch('cancelled');
        $this->success('Leave request cancelled successfully');
    }
}