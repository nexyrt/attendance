<?php

namespace App\Livewire\OfficeManagement;

use App\Livewire\Traits\Alert;
use App\Models\OfficeLocation;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use Alert, WithPagination;

    public ?string $search = null;
    public ?int $quantity = 10;
    public array $sort = ['column' => 'name', 'direction' => 'asc'];

    protected $listeners = ['created', 'edited', 'deleted'];

    public function render(): View
    {
        return view('livewire.office-management.index');
    }

    public function getHeaders(): array
    {
        return [
            ['index' => 'name', 'label' => 'Office Name'],
            ['index' => 'address', 'label' => 'Address'],
            ['index' => 'radius', 'label' => 'Radius (m)'],
            ['index' => 'coordinates', 'label' => 'Coordinates', 'sortable' => false],
            ['index' => 'action', 'label' => 'Actions', 'sortable' => false],
        ];
    }

    public function getRows()
    {
        return OfficeLocation::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('address', 'like', "%{$this->search}%"))
            ->orderBy(...array_values($this->sort))
            ->paginate(10)
            ->withQueryString();
    }

    public function created(): void
    {
        $this->success('Office location created successfully!');
    }

    public function edited(): void
    {
        $this->success('Office location updated successfully!');
    }

    public function deleted(): void
    {
        $this->success('Office location deleted successfully!');
    }
}