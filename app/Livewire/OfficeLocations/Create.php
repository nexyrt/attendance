<?php

namespace App\Livewire\OfficeLocations;

use App\Livewire\Traits\Alert;
use App\Models\OfficeLocation;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Create extends Component
{
    use Alert;

    public OfficeLocation $office;
    public bool $modal = false;

    public function mount(): void
    {
        $this->office = new OfficeLocation();
    }

    public function rules(): array
    {
        return [
            'office.name' => ['required', 'string', 'max:255'],
            'office.address' => ['nullable', 'string'],
            'office.latitude' => ['required', 'numeric', 'between:-90,90'],
            'office.longitude' => ['required', 'numeric', 'between:-180,180'],
            'office.radius' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->office->save();

        $this->dispatch('created');
        $this->reset();
        $this->office = new OfficeLocation();
        $this->success('Office location created successfully!');
    }
}
