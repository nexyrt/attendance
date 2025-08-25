<?php

namespace App\Livewire\Schedule;

use App\Livewire\Traits\Alert;
use App\Models\ScheduleException;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Exception extends Component
{
    use Alert, WithPagination;

    public bool $modal = false;
    public bool $editMode = false;
    public ?ScheduleException $exception = null;

    public ?string $title = null;
    public ?string $date = null;
    public string $status = 'holiday';
    public ?string $start_time = null;
    public ?string $end_time = null;
    public ?int $late_tolerance = 30;
    public ?string $note = null;
    public array $selectedDepartments = [];

    public function render(): View
    {
        return view('livewire.schedule.exception');
    }

    #[On('load::manage-exceptions')]
    public function load(): void
    {
        $this->resetForm();
        $this->modal = true;
    }

    #[Computed]
    public function exceptions()
    {
        return ScheduleException::with('departments')
            ->when(Auth::user()->role === 'manager', function ($query) {
                $query->whereHas('departments', function ($q) {
                    $q->where('departments.id', Auth::user()->department_id);
                });
            })
            ->orderBy('date', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get()
            ->map(fn($dept) => ['label' => $dept->name, 'value' => $dept->id])
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'status' => ['required', 'in:holiday,event,regular'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'late_tolerance' => ['nullable', 'integer', 'min:0', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
            'selectedDepartments' => ['required', 'array', 'min:1'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $exception = $this->editMode ? $this->exception : new ScheduleException();

        $exception->fill([
            'title' => $this->title,
            'date' => $this->date,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'late_tolerance' => $this->late_tolerance,
            'note' => $this->note,
        ]);

        $exception->save();
        $exception->departments()->sync($this->selectedDepartments);

        $this->dispatch('updated');
        $this->resetForm();
        $this->success($this->editMode ? 'Pengecualian berhasil diperbarui' : 'Pengecualian berhasil dibuat');
    }

    public function edit(ScheduleException $exception): void
    {
        $this->editMode = true;
        $this->exception = $exception;

        $this->title = $exception->title;
        $this->date = $exception->date->format('Y-m-d');
        $this->status = $exception->status;
        $this->start_time = $exception->start_time?->format('H:i');
        $this->end_time = $exception->end_time?->format('H:i');
        $this->late_tolerance = $exception->late_tolerance;
        $this->note = $exception->note;
        $this->selectedDepartments = $exception->departments->pluck('id')->toArray();
    }

    public function delete(ScheduleException $exception): void
    {
        $exception->delete();
        $this->success('Pengecualian berhasil dihapus');
    }

    private function resetForm(): void
    {
        $this->editMode = false;
        $this->exception = null;
        $this->title = null;
        $this->date = null;
        $this->status = 'holiday';
        $this->start_time = null;
        $this->end_time = null;
        $this->late_tolerance = 30;
        $this->note = null;
        $this->selectedDepartments = [];
    }
}