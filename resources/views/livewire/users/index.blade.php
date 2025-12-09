<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Manajemen Karyawan</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Kelola data karyawan dan informasi departemen</p>
        </div>
        <livewire:users.create @created="$refresh" />
    </div>

    {{-- Users Table --}}
    <x-card>
        <x-table :$headers :$sort :rows="$this->rows" selectable wire:model="selected" paginate filter loading>
            {{-- Custom Name Column with Avatar --}}
            @interact('column_name', $row)
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-semibold text-sm">
                            {{ $row->getInitials() }}
                        </span>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $row->name }}</div>
                        @if ($row->phone_number)
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row->phone_number }}</div>
                        @endif
                    </div>
                </div>
            @endinteract

            {{-- Role Badge - Direct Spatie --}}
            @interact('column_role', $row)
                @php
                    $role = $row->roles->first();
                @endphp
                @if ($role)
                    <x-badge :color="match ($role->name) {
                        'director' => 'purple',
                        'admin' => 'red',
                        'manager' => 'blue',
                        'staff' => 'green',
                        default => 'gray',
                    }" :text="match ($role->name) {
                        'director' => 'Director',
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'staff' => 'Staff',
                        default => ucfirst($role->name),
                    }" />
                @else
                    <x-badge color="gray" text="No Role" />
                @endif
            @endinteract

            {{-- Department Info --}}
            @interact('column_department', $row)
                @if ($row->department)
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-3 h-3 rounded-full {{ match ($row->department->name) {
                                'Digital Marketing' => 'bg-blue-500',
                                'Sistem Digital' => 'bg-green-500',
                                'Administrasi Pajak' => 'bg-yellow-500',
                                'HR' => 'bg-purple-500',
                                default => 'bg-gray-500',
                            } }}">
                        </div>
                        <span class="text-gray-900 dark:text-white">{{ $row->department->name }}</span>
                    </div>
                @else
                    <span class="text-gray-400 italic">Tidak ada</span>
                @endif
            @endinteract

            {{-- Date with relative time --}}
            @interact('column_created_at', $row)
                <div class="text-sm">
                    <div class="text-gray-900 dark:text-white">{{ $row->created_at?->format('d M Y') ?? '-' }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row->created_at?->diffForHumans() ?? '-' }}
                    </div>
                </div>
            @endinteract

            {{-- Action buttons --}}
            @interact('column_action', $row)
                <div class="flex items-center gap-1">
                    <x-button.circle icon="pencil" color="blue" size="sm"
                        wire:click="$dispatch('load::user', { user: '{{ $row->id }}' })" title="Edit" />
                    <livewire:users.delete :user="$row" :key="uniqid()" @deleted="$refresh" />
                </div>
            @endinteract
        </x-table>
    </x-card>

    {{-- Enhanced Bulk Actions Bar --}}
    <div x-data="{ show: @entangle('selected').live }" x-show="show.length > 0" x-transition
        class="fixed bottom-4 sm:bottom-6 left-4 right-4 sm:left-1/2 sm:right-auto sm:transform sm:-translate-x-1/2 z-50">

        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-600 px-4 sm:px-6 py-4 sm:min-w-96">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
                {{-- Selection Info --}}
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-gray-50"
                            x-text="`${show.length} karyawan dipilih`"></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Pilih aksi untuk karyawan yang dipilih
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 justify-end">
                    {{-- Export Selected --}}
                    <x-button wire:click="exportSelected" size="sm" color="green" icon="document-arrow-down"
                        loading="exportSelected" class="whitespace-nowrap">
                        Export
                    </x-button>

                    {{-- Delete Selected --}}
                    <x-button wire:click="confirmBulkDelete" size="sm" color="red" icon="trash"
                        loading="confirmBulkDelete" class="whitespace-nowrap">
                        Hapus
                    </x-button>

                    {{-- Cancel Selection --}}
                    <x-button wire:click="$set('selected', [])" size="sm" color="gray" icon="x-mark"
                        class="whitespace-nowrap">
                        Batal
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    {{-- Update Modal --}}
    <livewire:users.update @updated="$refresh" />
</div>
