<?php

namespace App\Livewire\OfficeLocations;

use App\Livewire\Traits\Alert;
use App\Models\OfficeLocation;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Delete extends Component
{
    use Alert;

    public OfficeLocation $office;

    public function render(): string
    {
        return <<<'HTML'
        <div>
            <x-button.circle icon="trash" color="red" wire:click="confirm" />
        </div>
        HTML;
    }

    #[Renderless]
    public function confirm(): void
    {
        $this->question()
            ->confirm(method: 'delete')
            ->cancel()
            ->send();
    }

    public function delete(): void
    {
        $this->office->delete();
        $this->dispatch('deleted');
        $this->success();
    }
}
