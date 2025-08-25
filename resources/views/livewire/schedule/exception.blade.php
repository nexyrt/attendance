<div>
    <x-modal title="Kelola Pengecualian Jadwal" wire size="5xl">
        <div class="space-y-6">
            {{-- Add Exception Form --}}
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $editMode ? 'Edit Pengecualian' : 'Tambah Pengecualian Baru' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-input label="Judul *" wire:model="title" placeholder="Contoh: Libur Nasional, Training, dll"
                                required />
                        </div>
                        <div>
                            <x-date label="Tanggal *" wire:model="date" :min-date="now()->format('Y-m-d')" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <x-select.native label="Jenis *" wire:model.live="status" :options="[
                                ['label' => 'Libur', 'value' => 'holiday'],
                                ['label' => 'Event/Training', 'value' => 'event'],
                                ['label' => 'Jadwal Khusus', 'value' => 'regular'],
                            ]" required />
                        </div>
                        @if ($status !== 'holiday')
                            <div>
                                <x-input label="Jam Mulai" type="time" wire:model="start_time" />
                            </div>
                            <div>
                                <x-input label="Jam Selesai" type="time" wire:model="end_time" />
                            </div>
                        @endif
                    </div>

                    @if ($status !== 'holiday')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input label="Toleransi Terlambat (menit)" type="number" wire:model="late_tolerance"
                                    min="0" max="120" />
                            </div>
                            <div>
                                <x-textarea label="Catatan" wire:model="note" rows="2"
                                    placeholder="Keterangan tambahan..." />
                            </div>
                        </div>
                    @endif

                    <div>
                        <x-select.styled label="Berlaku untuk Departemen *" wire:model="selectedDepartments"
                            :options="$this->departments" multiple searchable required />
                    </div>

                    <div class="flex justify-end space-x-2">
                        @if ($editMode)
                            <x-button type="button" color="gray" wire:click="resetForm">
                                Batal
                            </x-button>
                        @endif
                        <x-button type="submit" color="blue" loading="save">
                            {{ $editMode ? 'Update' : 'Tambah' }}
                        </x-button>
                    </div>
                </form>
            </x-card>

            {{-- Exceptions List --}}
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daftar Pengecualian</h3>

                @if ($this->exceptions->count() > 0)
                    <div class="space-y-3">
                        @foreach ($this->exceptions as $exception)
                            <div
                                class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg {{ $exception->date->isPast() ? 'opacity-60' : '' }}">
                                <div class="flex items-center space-x-4">
                                    <div
                                        class="w-12 h-12 {{ match ($exception->status) {
                                            'holiday' => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
                                            'event' => 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
                                            default => 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
                                        } }} rounded-full flex items-center justify-center">
                                        @if ($exception->status === 'holiday')
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zM14 9a1 1 0 100-2 1 1 0 000 2zm-7 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @elseif($exception->status === 'event')
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="font-medium text-gray-900 dark:text-white">
                                                {{ $exception->title }}
                                            </h4>
                                            <x-badge :color="match ($exception->status) {
                                                'holiday' => 'red',
                                                'event' => 'blue',
                                                default => 'green',
                                            }" :text="match ($exception->status) {
                                                'holiday' => 'Libur',
                                                'event' => 'Event',
                                                default => 'Khusus',
                                            }" size="sm" />
                                        </div>

                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <span class="font-medium">{{ $exception->date->format('l, d F Y') }}</span>
                                            @if ($exception->start_time && $exception->end_time)
                                                • {{ $exception->start_time->format('H:i') }} -
                                                {{ $exception->end_time->format('H:i') }}
                                            @endif
                                        </div>

                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Departemen: {{ $exception->departments->pluck('name')->join(', ') }}
                                        </div>

                                        @if ($exception->note)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">
                                                {{ $exception->note }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex space-x-1">
                                    @if (!$exception->date->isPast())
                                        <x-button.circle icon="pencil" color="blue" size="sm"
                                            wire:click="edit({{ $exception->id }})" title="Edit" />
                                    @endif
                                    <x-button.circle icon="trash" color="red" size="sm"
                                        wire:click="delete({{ $exception->id }})"
                                        wire:confirm="Yakin ingin menghapus pengecualian ini?" title="Hapus" />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $this->exceptions->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Pengecualian</h3>
                        <p class="text-gray-500 dark:text-gray-400">Tambahkan libur nasional, event, atau jadwal khusus.
                        </p>
                    </div>
                @endif
            </x-card>
        </div>

        <x-slot:footer>
            <x-button color="gray" wire:click="$set('modal', false)">
                Tutup
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>
