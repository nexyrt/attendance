<div>
    <x-modal wire="modal" size="2xl" center persistent>
        {{-- Custom Title --}}
        <x-slot:title>
            <div class="flex items-center gap-4 my-3">
                <div class="h-12 w-12 bg-green-50 dark:bg-green-900/20 rounded-xl flex items-center justify-center">
                    <x-icon name="pencil" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                        Edit Karyawan
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->userName ? 'Update data: ' . $this->userName : 'Perbarui informasi karyawan' }}
                    </p>
                </div>
            </div>
        </x-slot:title>

        {{-- Form --}}
        <form id="user-update" wire:submit="save" class="space-y-6">
            {{-- Section: Informasi Dasar --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-600 pb-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Informasi Dasar</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Data pribadi karyawan</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-input wire:model="name" label="Nama Lengkap *" placeholder="Masukkan nama lengkap" />

                    <x-input wire:model="email" type="email" label="Email *" placeholder="user@example.com" />

                    <x-input wire:model="phone_number" label="No. Telepon" placeholder="+62..." />

                    <x-date wire:model="birthdate" label="Tanggal Lahir" placeholder="Pilih tanggal" />

                    <x-input wire:model="salary" type="number" label="Gaji" prefix="Rp" placeholder="0" />

                    <div class="lg:col-span-2">
                        <x-textarea wire:model="address" label="Alamat" rows="2"
                            placeholder="Masukkan alamat lengkap" />
                    </div>
                </div>
            </div>

            {{-- Section: Akun & Role --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-600 pb-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Akun & Role</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pengaturan akses sistem</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-select.styled wire:model="role" :options="$this->roles" label="Role *" placeholder="Pilih role..."
                        searchable />

                    <x-select.styled wire:model="department_id" :options="$this->departments" label="Departemen"
                        placeholder="Pilih departemen..." searchable />
                </div>
            </div>

            {{-- Section: Change Password (Optional) --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-600 pb-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Ubah Password</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <x-password wire:model="password">
                        <x-slot:label>
                            <div class="flex items-center gap-2">
                                <span>Password Baru</span>
                                <x-tooltip color="secondary" text="Kosongkan jika tidak ingin mengubah"
                                    position="top" />
                            </div>
                        </x-slot:label>
                    </x-password>

                    <x-password wire:model="password_confirmation" label="Konfirmasi Password Baru" />
                </div>
            </div>
        </form>

        {{-- Footer --}}
        <x-slot:footer>
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <x-button wire:click="$set('modal', false)" color="secondary" outline
                    class="w-full sm:w-auto order-2 sm:order-1">
                    Batal
                </x-button>
                <x-button type="submit" form="user-update" color="green" icon="check" loading="save"
                    class="w-full sm:w-auto order-1 sm:order-2">
                    Update Karyawan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
