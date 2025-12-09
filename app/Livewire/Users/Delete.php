<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\Alert;
use App\Models\User;
use Livewire\Attributes\Renderless;
use Livewire\Component;

class Delete extends Component
{
    use Alert;

    public User $user;

    public function render(): string
    {
        return <<<'HTML'
        <div>
            <x-button.circle icon="trash" color="red" size="sm" wire:click="confirm" title="Hapus" />
        </div>
        HTML;
    }

    #[Renderless]
    public function confirm(): void
    {
        $this->dialog()
            ->question(
                "Hapus {$this->user->name}?",
                "Data karyawan akan dihapus secara permanen dan tidak dapat dikembalikan."
            )
            ->confirm(method: 'delete')
            ->cancel()
            ->send();
    }

    public function delete(): void
    {
        // Safety check: prevent self-delete
        if ($this->user->id === auth()->id()) {
            $this->dialog()
                ->error('Tidak Dapat Dihapus', 'Anda tidak dapat menghapus akun Anda sendiri.')
                ->send();
            return;
        }

        $userName = $this->user->name;

        // Force delete (permanent) - menghapus dari database fisik
        $this->user->forceDelete();

        $this->dispatch('deleted');

        $this->dialog()
            ->success('Berhasil!', "{$userName} telah dihapus secara permanen")
            ->send();
    }
}