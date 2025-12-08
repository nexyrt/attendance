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
use Spatie\Permission\Models\Role;

class Update extends Component
{
    use Alert;

    // Modal Control
    public bool $modal = false;

    // User ID untuk tracking
    public ?int $userId = null;

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
        return view('livewire.users.update');
    }

    // Event Listener - Dipanggil dari parent
    #[On('load::user')]
    public function load(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone_number = $user->phone_number;
        $this->department_id = $user->department_id;
        $this->birthdate = $user->birthdate?->format('Y-m-d');
        $this->salary = $user->salary;
        $this->address = $user->address;

        // Get role from Spatie
        $this->role = $user->getRoleName();

        $this->modal = true;
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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->userId)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date'],
            'salary' => ['nullable', 'numeric', 'min:0'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $user = User::findOrFail($this->userId);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'department_id' => $validated['department_id'],
            'birthdate' => $validated['birthdate'],
            'salary' => $validated['salary'],
            'address' => $validated['address'],
        ]);

        // Update password jika diisi
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
            $user->save();
        }

        // Sync role using Spatie
        $user->syncRoles([$validated['role']]);

        $this->dispatch('updated');
        $this->reset();

        $this->toast()
            ->success('Berhasil!', 'Data karyawan berhasil diperbarui')
            ->send();
    }

    // Helper untuk get user name (untuk title modal)
    #[Computed]
    public function userName(): ?string
    {
        if (!$this->userId)
            return null;

        return User::find($this->userId)?->name;
    }
}