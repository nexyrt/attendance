<div>
    <x-modal title="Tambah Jadwal Kerja" wire size="4xl" persistent>
        <div class="space-y-6">
            {{-- Info Alert --}}
            <x-alert color="blue" icon="information-circle">
                <strong>Tambah Jadwal:</strong> Anda dapat menambahkan jadwal untuk hari yang belum memiliki jadwal.
                Centang "Hari Kerja" untuk mengaktifkan jam kerja pada hari tersebut.
            </x-alert>

            @if (count($this->availableDays) === 0)
                <x-alert color="amber" icon="exclamation-triangle">
                    <strong>Semua jadwal sudah ada!</strong> Tidak ada hari yang dapat ditambahkan.
                    Gunakan fitur "Edit" untuk mengubah jadwal yang ada.
                </x-alert>
            @endif

            {{-- Schedule Form --}}
            <form id="schedule-create" wire:submit="save" class="space-y-4">
                @foreach ($schedules as $index => $schedule)
                    <x-card>
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" wire:model.live="schedules.{{ $index }}.is_workday"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
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
                            </div>
                            <x-badge :color="$schedule['is_workday'] ? 'green' : 'gray'" :text="$schedule['is_workday'] ? 'Hari Kerja' : 'Libur'" />
                        </div>

                        @if ($schedule['is_workday'])
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
                        @else
                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <p class="text-gray-600 dark:text-gray-300 text-sm">
                                    Hari ini ditandai sebagai hari libur. Tidak ada jam kerja yang perlu diatur.
                                </p>
                            </div>
                        @endif
                    </x-card>
                @endforeach

                @if (count($this->availableDays) > 0)
                    {{-- Quick Templates --}}
                    <x-card>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Template Cepat</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <x-button type="button" color="blue" size="sm"
                                wire:click="$dispatch('apply-workday-template', {start: '08:00', end: '17:00', tolerance: 30})">
                                Standar (08:00-17:00)
                            </x-button>
                            <x-button type="button" color="green" size="sm"
                                wire:click="$dispatch('apply-workday-template', {start: '09:00', end: '18:00', tolerance: 30})">
                                Fleksibel (09:00-18:00)
                            </x-button>
                            <x-button type="button" color="purple" size="sm"
                                wire:click="$dispatch('apply-workday-template', {start: '07:30', end: '16:30', tolerance: 15})">
                                Pagi (07:30-16:30)
                            </x-button>
                            <x-button type="button" color="orange" size="sm"
                                wire:click="$dispatch('apply-weekend-off')">
                                Weekend Libur
                            </x-button>
                        </div>
                    </x-card>
                @endif
            </form>
        </div>

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button color="gray" wire:click="$set('modal', false)">
                    Batal
                </x-button>
                @if (count($this->availableDays) > 0)
                    <x-button type="submit" form="schedule-create" color="blue" loading="save" icon="plus">
                        Tambah Jadwal ({{ count($this->availableDays) }} hari)
                    </x-button>
                @endif
            </div>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        // Apply template for workdays only
        Livewire.on('apply-workday-template', (event) => {
            @this.schedules.forEach((schedule, index) => {
                if (schedule.is_workday) {
                    @this.set(`schedules.${index}.start_time`, event.start);
                    @this.set(`schedules.${index}.end_time`, event.end);
                    @this.set(`schedules.${index}.late_tolerance`, event.tolerance);
                }
            });
        });

        // Set weekend as off days
        Livewire.on('apply-weekend-off', () => {
            @this.schedules.forEach((schedule, index) => {
                if (schedule.day_of_week === 'saturday' || schedule.day_of_week === 'sunday') {
                    @this.set(`schedules.${index}.is_workday`, false);
                } else {
                    @this.set(`schedules.${index}.is_workday`, true);
                }
            });
        });
    });
</script>
