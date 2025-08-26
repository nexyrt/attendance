<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\Alert;
use App\Models\User;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use Alert;

    public User $user;
    public ?string $password = null;
    public ?string $password_confirmation = null;
    public bool $modal = false;

    public function mount(): void
    {
        $this->user = new User();
    }

    public function render(): View
    {
        return view('livewire.users.create');
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
            'user.email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'user.role' => ['required', 'in:staff,manager,admin,director'],
            'user.department_id' => ['nullable', 'exists:departments,id'],
            'user.phone_number' => ['nullable', 'string', 'max:20'],
            'user.birthdate' => ['nullable', 'date'],
            'user.salary' => ['nullable', 'numeric', 'min:0'],
            'user.address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->user->password = bcrypt($this->password);
        $this->user->email_verified_at = now();
        $this->user->save();

        $this->dispatch('created');
        $this->reset();
        $this->user = new User();
        $this->success('Karyawan berhasil ditambahkan');
    }
}