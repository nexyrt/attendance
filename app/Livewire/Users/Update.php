<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\Alert;
use App\Models\User;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Update extends Component
{
    use Alert;

    public ?User $user = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public bool $modal = false;

    public function render(): View
    {
        return view('livewire.users.update');
    }

    #[On('load::user')]
    public function load(User $user): void
    {
        $this->user = $user;
        $this->modal = true;
    }

    #[Computed]
    public function departments(): array
    {
        return Department::orderBy('name')->get()
            ->map(fn($dept) => ['label' => $dept->name, 'value' => $dept->id])
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'user.role' => ['required', 'in:staff,manager,admin,director'],
            'user.department_id' => ['nullable', 'exists:departments,id'],
            'user.phone_number' => ['nullable', 'string', 'max:20'],
            'user.birthdate' => ['nullable', 'date'],
            'user.salary' => ['nullable', 'numeric', 'min:0'],
            'user.address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed']
        ];
    }

    public function save(): void
    {
        $this->validate();

        if ($this->password) {
            $this->user->password = bcrypt($this->password);
        }
        
        $this->user->save();

        $this->dispatch('updated');
        $this->resetExcept('user');
        $this->success('Data karyawan berhasil diperbarui');
    }
}