<?php

namespace App\Livewire\Users;

use App\Livewire\Traits\Alert;
use App\Models\User;
use App\Models\Department;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Create extends Component
{
    use Alert;

    // Modal Control
    public bool $modal = false;

    // Form Fields - Individual Properties
    public ?string $name = null;
    public ?string $email = null;
    public ?string $phone_number = null;
    public ?string $role = null;
    public ?int $department_id = null;
    public ?string $birthdate = null;
    public ?float $salary = null;
    public ?string $address = null;
    public ?string $password = null;
    public ?string $password_confirmation = null;

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

    #[Computed]
    public function roles(): array
    {
        return Role::orderBy('name')->get()
            ->map(fn($role) => ['label' => ucfirst($role->name), 'value' => $role->name])
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', 'string', 'exists:roles,name'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'department_id' => $validated['department_id'],
            'birthdate' => $validated['birthdate'],
            'salary' => $validated['salary'],
            'address' => $validated['address'],
            'password' => bcrypt($validated['password']),
            'email_verified_at' => now(),
        ]);

        // Assign role using Spatie
        $user->assignRole($validated['role']);

        $this->dispatch('created');
        $this->reset();

        $this->toast()
            ->success('Berhasil!', 'Karyawan berhasil ditambahkan')
            ->send();
    }
}