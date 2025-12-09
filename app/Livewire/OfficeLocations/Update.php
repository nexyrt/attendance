<?php

namespace App\Livewire\OfficeLocations;

use App\Livewire\Traits\Alert;
use App\Models\OfficeLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class Update extends Component
{
    use Alert;

    public ?OfficeLocation $office;
    public bool $modal = false;

    #[On('load::office')]
    public function load(OfficeLocation $office): void
    {
        $this->office = $office;
        $this->modal = true;

        // Dispatch coordinates to JavaScript
        $this->dispatch('initUpdateMap', [
            'lat' => $office->latitude,
            'lng' => $office->longitude
        ]);
    }

    public function rules(): array
    {
        return [
            'office.name' => ['required', 'string', 'max:255', Rule::unique('office_locations', 'name')->ignore($this->office->id)],
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

        $this->dispatch('edited');
        $this->resetExcept('office');
        $this->success('Office location updated successfully!');
    }
}
