<div>
    <x-modal title="Edit Jadwal Kerja" wire size="5xl" persistent>
        <div class="space-y-6">
            {{-- Warning Alert --}}
            <x-alert color="amber" icon="exclamation-triangle">
                <strong>Perhatian:</strong> Perubahan jadwal akan diterapkan untuk semua karyawan.
                Anda juga dapat menghapus jadwal untuk hari tertentu.
            </x-alert>

            {{-- Edit Form --}}
            <form id="schedule-edit" wire:submit="save" class="space-y-4">
                @foreach ($schedules as $index => $schedule)
                    <x-card>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ match ($schedule['day_of_week']) {
                                    'monday' => 'Senin',
                                    'tuesday' => 'Selasa',
                                    'wednesday' => 'Rabu',
                                    'thursday' => 'Kamis',
                                    'friday' => 'Jumat',
                                    'saturday' => 'Sabtu',
                                    'sunday' => 'Minggu',
                                } }}
                            </h3>

                            <div class="flex items-center space-x-2">
                                <x-badge color="blue" text="{{ ucfirst($schedule['day_of_week']) }}" />
                                <x-button.circle icon="trash" color="red" size="sm"
                                    wire:click="markForDeletion('{{ $schedule['day_of_week'] }}')"
                                    title="Hapus jadwal {{ $schedule['day_of_week'] }}" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <x-input label="Jam Masuk *" type="time"
                                    wire:model="schedules.{{ $index }}.start_time" required />
                            </div>
                            <div>
                                <x-input label="Jam Pulang *" type="time"
                                    wire:model="schedules.{{ $index }}.end_time" required />
                            </div>
                            <div>
                                <x-input label="Toleransi Terlambat (menit) *" type="number"
                                    wire:model="schedules.{{ $index }}.late_tolerance" min="0"
                                    max="120" required />
                            </div>
                        </div>

                        {{-- Working Hours Calculation --}}
                        <div
                            class="mt-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300">
                                    <strong>Jam kerja:</strong>
                                    {{ $this->calculateWorkingHours($schedule['start_time'], $schedule['end_time']) }}
                                    jam/hari
                                </span>
                                <span class="text-blue-600 dark:text-blue-400 font-medium">
                                    {{ $schedule['start_time'] }} - {{ $schedule['end_time'] }}
                                </span>
                            </div>
                        </div>
                    </x-card>
                @endforeach

                {{-- Quick Templates --}}
                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Template Cepat</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <x-button type="button" color="purple" size="sm"
                            wire:click="$dispatch('apply-template', {start: '08:00', end: '17:00', tolerance: 30})">
                            Standard (08:00-17:00)
                        </x-button>
                        <x-button type="button" color="blue" size="sm"
                            wire:click="$dispatch('apply-template', {start: '09:00', end: '18:00', tolerance: 30})">
                            Fleksibel (09:00-18:00)
                        </x-button>
                        <x-button type="button" color="green" size="sm"
                            wire:click="$dispatch('apply-template', {start: '07:30', end: '16:30', tolerance: 15})">
                            Pagi (07:30-16:30)
                        </x-button>
                        <x-button type="button" color="orange" size="sm"
                            wire:click="$dispatch('apply-template', {start: '08:30', end: '17:30', tolerance: 45})">
                            Santai (08:30-17:30)
                        </x-button>
                    </div>
                </x-card>

                {{-- Summary --}}
                <x-card>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Ringkasan Perubahan</h3>
                    <div
                        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-4 rounded-lg">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7 gap-2 text-center">
                            @foreach ($schedules as $schedule)
                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                    <div class="font-semibold text-blue-900 dark:text-blue-100 text-xs mb-1">
                                        {{ substr(
                                            match ($schedule['day_of_week']) {
                                                'monday' => 'Senin',
                                                'tuesday' => 'Selasa',
                                                'wednesday' => 'Rabu',
                                                'thursday' => 'Kamis',
                                                'friday' => 'Jumat',
                                                'saturday' => 'Sabtu',
                                                'sunday' => 'Minggu',
                                            },
                                            0,
                                            3,
                                        ) }}
                                    </div>
                                    <div class="text-xs text-blue-700 dark:text-blue-300">
                                        {{ $schedule['start_time'] }} - {{ $schedule['end_time'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $this->calculateWorkingHours($schedule['start_time'], $schedule['end_time']) }}h
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </form>
        </div>

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button color="gray" wire:click="$set('modal', false)">
                    Batal
                </x-button>
                <x-button type="submit" form="schedule-edit" color="green" loading="save" icon="check">
                    Update Jadwal ({{ count($schedules) }} hari)
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Apply template for all days (edit)
        Livewire.on('apply-template', (event) => {
            @this.schedules.forEach((schedule, index) => {
                @this.set(`schedules.${index}.start_time`, event.start);
                @this.set(`schedules.${index}.end_time`, event.end);
                @this.set(`schedules.${index}.late_tolerance`, event.tolerance);
            });
        });
    });
</script>
