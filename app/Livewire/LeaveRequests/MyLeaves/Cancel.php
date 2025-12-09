<?php

namespace App\Livewire\LeaveRequests\MyLeaves;

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
            <x-button.circle icon="x-circle" color="red" size="sm" 
                wire:click="confirm" title="Batalkan" />
        </div>
        HTML;
    }

    #[Renderless]
    public function confirm(): void
    {
        $this->dialog()
            ->question('Batalkan pengajuan cuti?', 'Pengajuan yang dibatalkan tidak dapat dikembalikan.')
            ->confirm(method: 'cancel')
            ->cancel()
            ->send();
    }

    public function cancel(): void
    {
        if (!$this->leaveRequest->canBeCancelled()) {
            $this->toast()
                ->error('Gagal!', 'Pengajuan ini tidak dapat dibatalkan')
                ->send();
            return;
        }

        $this->leaveRequest->cancel();

        $this->dispatch('cancelled');

        $this->toast()
            ->success('Berhasil!', 'Pengajuan cuti berhasil dibatalkan')
            ->send();
    }
}
