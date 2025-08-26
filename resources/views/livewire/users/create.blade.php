<div>
    <x-button wire:click="$toggle('modal')" color="blue" icon="plus" class="w-full sm:w-auto">
        Tambah Karyawan
    </x-button>

    <x-modal title="Tambah Karyawan Baru" wire size="2xl">
        <form id="user-create" wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <x-input label="Nama Lengkap *" wire:model="user.name" required />
                </div>
                <div>
                    <x-input label="Email *" type="email" wire:model="user.email" required />
                </div>
                <div>
                    <x-select.native label="Departemen" wire:model="user.department_id" 
                                     :options="$this->departments" placeholder="Pilih departemen" />
                </div>
                <div>
                    <x-select.native label="Role *" wire:model="user.role" :options="[
                        ['label' => 'Staff', 'value' => 'staff'],
                        ['label' => 'Manager', 'value' => 'manager'], 
                        ['label' => 'Admin', 'value' => 'admin'],
                        ['label' => 'Director', 'value' => 'director']
                    ]" required />
                </div>
                <div>
                    <x-input label="No. Telepon" wire:model="user.phone_number" />
                </div>
                <div>
                    <x-date label="Tanggal Lahir" wire:model="user.birthdate" />
                </div>
                <div>
                    <x-input label="Gaji" type="number" wire:model="user.salary" prefix="Rp" />
                </div>
                <div class="sm:col-span-2">
                    <x-textarea label="Alamat" wire:model="user.address" rows="2" />
                </div>
                <div>
                    <x-password label="Password *" wire:model="password" required />
                </div>
                <div>
                    <x-password label="Konfirmasi Password *" wire:model="password_confirmation" required />
                </div>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button color="gray" wire:click="$set('modal', false)">
                    Batal
                </x-button>
                <x-button type="submit" form="user-create" color="blue" loading="save" icon="check">
                    Simpan Karyawan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>