<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\Alert;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Alert;

    public ?int $quantity = 10;
    public ?string $search = null;
    public array $sort = ['column' => 'created_at', 'direction' => 'desc'];
    public array $selected = [];

    public array $headers = [
        ['index' => 'name', 'label' => 'Nama'],
        ['index' => 'email', 'label' => 'Email'],
        ['index' => 'role', 'label' => 'Role', 'sortable' => false],
        ['index' => 'department', 'label' => 'Departemen', 'sortable' => false],
        ['index' => 'created_at', 'label' => 'Bergabung'],
        ['index' => 'action', 'sortable' => false],
    ];

    public function render(): View
    {
        return view('livewire.users.index');
    }

    #[Computed]
    public function rows(): LengthAwarePaginator
    {
        return User::with(['department', 'roles'])
            ->whereNotIn('id', [Auth::id()])
            ->when(
                $this->search,
                fn(Builder $query) =>
                $query->where(function ($q) {
                    $q->whereAny(['name', 'email'], 'like', '%' . trim($this->search) . '%')
                        ->orWhereHas(
                            'department',
                            fn($dept) =>
                            $dept->where('name', 'like', '%' . trim($this->search) . '%')
                        );
                })
            )
            ->when(
                $this->sort['column'] === 'department',
                fn(Builder $query) =>
                $query->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                    ->orderBy('departments.name', $this->sort['direction'])
                    ->select('users.*')
                ,
                fn(Builder $query) =>
                $query->orderBy($this->sort['column'], $this->sort['direction'])
            )
            ->paginate($this->quantity)
            ->withQueryString();
    }

    #[Renderless]
    public function confirmBulkDelete(): void
    {
        if (empty($this->selected)) {
            $this->warning('Tidak ada karyawan yang dipilih');
            return;
        }

        $count = count($this->selected);

        $this->dialog()
            ->question("Hapus {$count} karyawan?", "Data karyawan yang dihapus tidak dapat dikembalikan.")
            ->confirm(method: 'bulkDelete')
            ->cancel()
            ->send();
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected))
            return;

        $count = count($this->selected);
        User::whereIn('id', $this->selected)->delete();

        $this->selected = [];
        $this->resetPage();

        $this->dialog()
            ->success('Berhasil!', "{$count} karyawan berhasil dihapus")
            ->send();
    }

    public function exportSelected(): void
    {
        if (empty($this->selected)) {
            $this->warning('Tidak ada karyawan yang dipilih');
            return;
        }

        $count = count($this->selected);

        // TODO: Implement actual export logic
        $this->toast()
            ->info('Export Diproses', "Export {$count} karyawan sedang diproses")
            ->send();
    }
}