<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Pengaturan Jadwal Kerja</h1>
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            @if ($this->canManage)
                {{-- Smart Create Button --}}
                @if ($this->canCreateSchedule)
                    <x-button wire:click="$dispatch('load::create-schedule')" color="blue" icon="plus"
                        class="w-full sm:w-auto">
                        <span class="hidden sm:inline">Tambah Jadwal</span>
                        <span class="sm:hidden">Tambah</span>
                        <x-badge color="white" text="{{ 7 - $this->schedules->count() }}" class="ml-2" />
                    </x-button>
                @else
                    <x-button color="gray" disabled class="w-full sm:w-auto">
                        <x-slot:left>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                    clip-rule="evenodd" />
                            </svg>
                        </x-slot:left>
                        <span class="hidden sm:inline">Jadwal Lengkap</span>
                        <span class="sm:hidden">Lengkap</span>
                    </x-button>
                @endif

                @if ($this->hasSchedules)
                    <x-button wire:click="$dispatch('load::edit-schedule')" color="purple" icon="pencil"
                        class="w-full sm:w-auto">
                        <span class="hidden sm:inline">Edit Jadwal</span>
                        <span class="sm:hidden">Edit</span>
                    </x-button>
                @endif

                <x-button wire:click="$dispatch('load::manage-exceptions')" color="orange" icon="calendar"
                    class="w-full sm:w-auto">
                    <span class="hidden sm:inline">Kelola Pengecualian</span>
                    <span class="sm:hidden">Pengecualian</span>
                </x-button>
            @endif
        </div>
    </div>

    {{-- Schedule Completion Status --}}
    @if ($this->canManage)
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status Kelengkapan Jadwal</h2>
                <x-badge :color="$this->scheduleCompletionStatus['percentage'] == 100 ? 'green' : 'blue'" :text="$this->scheduleCompletionStatus['percentage'] . '%'" />
            </div>

            <div class="space-y-3">
                {{-- Progress Bar --}}
                <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                        style="width: {{ $this->scheduleCompletionStatus['percentage'] }}%">
                    </div>
                </div>

                {{-- Status Info --}}
                <div class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>
                        <strong>{{ $this->scheduleCompletionStatus['existing'] }}</strong> dari
                        <strong>{{ $this->scheduleCompletionStatus['total'] }}</strong> hari sudah diatur
                    </span>
                    @if ($this->scheduleCompletionStatus['missing'] > 0)
                        <span class="text-orange-600 dark:text-orange-400">
                            {{ $this->scheduleCompletionStatus['missing'] }} hari belum diatur
                        </span>
                    @else
                        <span class="text-green-600 dark:text-green-400">
                            ✓ Semua hari sudah lengkap
                        </span>
                    @endif
                </div>
            </div>
        </x-card>
    @endif

    {{-- Default Work Schedule --}}
    <x-card>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Jadwal Kerja Saat Ini</h2>
            @if ($this->canManage && $this->schedules->count() > 0)
                <div class="flex space-x-2">
                    <x-badge :color="$this->schedules->count() == 7 ? 'green' : 'blue'" :text="$this->schedules->count() . '/7 hari'" />
                </div>
            @endif
        </div>

        @if ($this->schedules->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 gap-4">
                @foreach ($this->schedules as $schedule)
                    <div
                        class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg {{ $schedule->start_time ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                        <div class="text-center">
                            <div
                                class="font-semibold text-gray-900 dark:text-white text-sm mb-2 flex items-center justify-center">
                                @if ($schedule->start_time)
                                    <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-3 h-3 mr-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                {{ match ($schedule->day_of_week) {
                                    'monday' => 'Senin',
                                    'tuesday' => 'Selasa',
                                    'wednesday' => 'Rabu',
                                    'thursday' => 'Kamis',
                                    'friday' => 'Jumat',
                                    'saturday' => 'Sabtu',
                                    'sunday' => 'Minggu',
                                } }}
                            </div>

                            @if ($schedule->start_time && $schedule->end_time)
                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ $schedule->start_time->format('H:i') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">sampai</div>
                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                    {{ $schedule->end_time->format('H:i') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Toleransi: {{ $schedule->late_tolerance }} menit
                                </div>
                                <div class="text-xs text-green-600 dark:text-green-400 mt-1 font-medium">
                                    Hari Kerja
                                </div>
                            @else
                                <div class="text-sm text-gray-500 dark:text-gray-400 py-4">
                                    <svg class="w-8 h-8 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zM14 9a1 1 0 100-2 1 1 0 000 2zm-7 3a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Hari Libur
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Quick Stats --}}
            <div
                class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $this->schedules->whereNotNull('start_time')->count() }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">Hari Kerja</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-gray-500 dark:text-gray-400">
                            {{ $this->schedules->whereNull('start_time')->count() }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">Hari Libur</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                            {{ round($this->schedules->whereNotNull('start_time')->avg('late_tolerance')) ?? 0 }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">Rata-rata Toleransi</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                            @php
                                $totalMinutes = $this->schedules->whereNotNull('start_time')->sum(function ($schedule) {
                                    return $schedule->start_time->diffInMinutes($schedule->end_time);
                                });
                                $avgHours =
                                    $totalMinutes > 0
                                        ? round(
                                            $totalMinutes / $this->schedules->whereNotNull('start_time')->count() / 60,
                                            1,
                                        )
                                        : 0;
                            @endphp
                            {{ $avgHours }}
                        </div>
                        <div class="text-xs text-gray-600 dark:text-gray-300">Rata-rata Jam Kerja</div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Jadwal</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-4">Buat jadwal kerja untuk mengatur jam kerja karyawan.
                </p>
                @if ($this->canManage)
                    <x-button wire:click="$dispatch('load::create-schedule')" color="blue" icon="plus">
                        Buat Jadwal Pertama
                    </x-button>
                @endif
            </div>
        @endif
    </x-card>

    {{-- Upcoming Exceptions --}}
    <x-card>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengecualian Mendatang</h2>
            @if ($this->canManage)
                <x-button wire:click="$dispatch('load::manage-exceptions')" color="purple" size="sm"
                    icon="plus">
                    Tambah Pengecualian
                </x-button>
            @endif
        </div>

        @if ($this->upcomingExceptions->count() > 0)
            <div class="space-y-3">
                @foreach ($this->upcomingExceptions as $exception)
                    <div
                        class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 {{ match ($exception->status) {
                                    'holiday' => 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400',
                                    'event' => 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
                                    default => 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
                                } }} rounded-full flex items-center justify-center">
                                @if ($exception->status === 'holiday')
                                    🏖️
                                @elseif($exception->status === 'event')
                                    📅
                                @else
                                    ⏰
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $exception->title ?? 'Pengecualian Jadwal' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $exception->date->format('l, d F Y') }}
                                    @if ($exception->start_time && $exception->end_time)
                                        • {{ $exception->start_time->format('H:i') }} -
                                        {{ $exception->end_time->format('H:i') }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $exception->departments->pluck('name')->join(', ') }}
                                </div>
                            </div>
                        </div>
                        <div>
                            <x-badge :color="match ($exception->status) {
                                'holiday' => 'red',
                                'event' => 'blue',
                                default => 'green',
                            }" :text="match ($exception->status) {
                                'holiday' => 'Libur',
                                'event' => 'Event',
                                default => 'Khusus',
                            }" />
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6">
                <svg class="w-8 h-8 mx-auto text-gray-400 dark:text-gray-600 mb-2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-500 dark:text-gray-400">Tidak ada pengecualian jadwal mendatang</p>
            </div>
        @endif
    </x-card>

    {{-- Department Access (Manager Only) --}}
    @if (Auth::user()->role === 'manager')
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Akses Departemen</h2>
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-2" fill="currentColor"
                        viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-blue-900 dark:text-blue-100">
                        Anda dapat melihat jadwal untuk departemen:
                        <strong>{{ Auth::user()->department?->name }}</strong>
                    </span>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Modals --}}
    @if ($this->canManage)
        <livewire:schedule.create @created="$refresh" />
        @if ($this->hasSchedules)
            <livewire:schedule.edit @updated="$refresh" />
        @endif
        <livewire:schedule.exception @updated="$refresh" />
    @endif

    <livewire:schedule.calendar />
</div>
