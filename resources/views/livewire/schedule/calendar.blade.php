<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Kalender Jadwal Kerja</h1>
        <div class="flex space-x-2">
            <x-button wire:click="goToday" color="blue" size="sm">Hari Ini</x-button>
        </div>
    </div>

    {{-- Calendar Navigation --}}
    <x-card>
        <div class="flex justify-between items-center mb-6">
            <x-button.circle icon="chevron-left" wire:click="previousMonth" />
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ $this->monthName }}</h2>
            <x-button.circle icon="chevron-right" wire:click="nextMonth" />
        </div>

        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-1">
            {{-- Day Headers --}}
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                <div class="p-2 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $day }}
                </div>
            @endforeach

            {{-- Calendar Days --}}
            @foreach ($this->calendarDays as $week)
                @foreach ($week as $day)
                    <div
                        wire:click="showDateDetails('{{ $day['dateString'] }}')"
                        class="min-h-[100px] p-1 border border-gray-200 dark:border-gray-700 cursor-pointer
                        hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors duration-200
                        {{ $day['isCurrentMonth'] ? '' : 'bg-gray-50 dark:bg-gray-800' }}
                        {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                        {{ $day['isWeekend'] ? 'bg-red-50 dark:bg-red-900/10' : '' }}
                        {{ $day['hasActivity'] ? 'ring-1 ring-green-400' : '' }}">

                        {{-- Date Number --}}
                        <div class="flex items-center justify-between mb-1">
                            <div
                                class="text-sm font-medium {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}
                                {{ $day['isToday'] ? 'text-blue-600 font-bold' : '' }}">
                                {{ $day['date']->day }}
                            </div>
                            
                            {{-- Activity Indicator --}}
                            @if($day['hasActivity'])
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            @endif
                        </div>

                        {{-- Exception Badge --}}
                        @if ($day['exception'])
                            <div class="space-y-1">
                                <div
                                    class="px-1 py-0.5 text-xs rounded {{ match ($day['exception']->status) {
                                        'holiday' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'event' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        default => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                    } }}">
                                    {{ match ($day['exception']->status) {
                                        'holiday' => '🏖️ Libur',
                                        'event' => '📅 Event',
                                        default => '⏰ Khusus',
                                    } }}
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                    {{ $day['exception']->title }}
                                </div>
                                @if ($day['exception']->start_time && $day['exception']->end_time)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $day['exception']->start_time->format('H:i') }}-{{ $day['exception']->end_time->format('H:i') }}
                                    </div>
                                @endif
                            </div>
                        @elseif($day['schedule'] && !$day['isWeekend'])
                            {{-- Regular Schedule --}}
                            <div class="space-y-1">
                                <div class="text-xs text-gray-600 dark:text-gray-300">
                                    ⏰ {{ $day['schedule']->start_time->format('H:i') }}-{{ $day['schedule']->end_time->format('H:i') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    Toleransi: {{ $day['schedule']->late_tolerance }}m
                                </div>
                            </div>
                        @elseif($day['isWeekend'])
                            <div class="mt-1">
                                <div class="text-xs text-red-600 dark:text-red-400">
                                    🏖️ Weekend
                                </div>
                            </div>
                        @endif

                        {{-- Click hint for non-empty dates --}}
                        @if($day['hasActivity'] || $day['schedule'] || $day['exception'])
                            <div class="text-xs text-blue-500 dark:text-blue-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                Klik untuk detail
                            </div>
                        @endif
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-card>

    {{-- Date Details Modal --}}
    <x-modal :title="$selectedDate ? 'Detail Tanggal: ' . $selectedDate->locale('id')->format('d F Y') : 'Detail Tanggal'" center 
             wire size="4xl">
            @if($this->selectedDateDetails)
                <div class="space-y-6">
                    {{-- Date Header --}}
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $this->selectedDateDetails['dayName'] }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                {{ $this->selectedDateDetails['dateFormatted'] }}
                            </p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @if($this->selectedDateDetails['isToday'])
                                <x-badge color="blue" text="Hari Ini" />
                            @endif
                            @if($this->selectedDateDetails['isPast'])
                                <x-badge color="gray" text="Masa Lalu" />
                            @endif
                            <x-badge 
                                :color="match($this->selectedDateDetails['status']) {
                                    'libur' => 'red',
                                    'event' => 'blue',
                                    'khusus' => 'purple',
                                    'kerja' => 'green',
                                    default => 'gray'
                                }"
                                :text="match($this->selectedDateDetails['status']) {
                                    'libur' => 'Libur',
                                    'event' => 'Event',
                                    'khusus' => 'Khusus',
                                    'kerja' => 'Hari Kerja',
                                    default => 'Normal'
                                }"
                            />
                        </div>
                    </div>

                    {{-- Working Schedule Info --}}
                    <x-card>
                        <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            Jadwal Kerja
                        </h4>
                        <p class="text-gray-700 dark:text-gray-300">
                            {{ $this->selectedDateDetails['workingSchedule'] }}
                        </p>
                        
                        @if($this->selectedDateDetails['exception'])
                            <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="font-medium text-yellow-800 dark:text-yellow-200">
                                            {{ $this->selectedDateDetails['exception']->title }}
                                        </p>
                                        @if($this->selectedDateDetails['exception']->note)
                                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                                {{ $this->selectedDateDetails['exception']->note }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </x-card>

                    {{-- Attendance Records --}}
                    @if($this->selectedDateDetails['attendances']->count() > 0)
                        <x-card>
                            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Rekap Kehadiran ({{ $this->selectedDateDetails['attendanceCount'] }} karyawan)
                            </h4>
                            
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($this->selectedDateDetails['attendances'] as $attendance)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                    {{ substr($attendance->user->name, 0, 1) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    {{ $attendance->user->name }}
                                                </p>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ $attendance->user->department->name ?? 'No Department' }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="text-right">
                                            <div class="flex items-center space-x-2 mb-1">
                                                @if($attendance->check_in)
                                                    <span class="text-sm text-green-600 dark:text-green-400">
                                                        Masuk: {{ $attendance->check_in->format('H:i') }}
                                                    </span>
                                                @endif
                                                @if($attendance->check_out)
                                                    <span class="text-sm text-blue-600 dark:text-blue-400">
                                                        Pulang: {{ $attendance->check_out->format('H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                            <x-badge 
                                                :color="match($attendance->status) {
                                                    'present' => 'green',
                                                    'late' => 'yellow',
                                                    'early_leave' => 'orange',
                                                    'holiday' => 'blue',
                                                    default => 'gray'
                                                }"
                                                :text="match($attendance->status) {
                                                    'present' => 'Hadir',
                                                    'late' => 'Terlambat',
                                                    'early_leave' => 'Pulang Awal',
                                                    'holiday' => 'Libur',
                                                    'pending_present' => 'Pending',
                                                    default => ucfirst($attendance->status)
                                                }"
                                                size="sm"
                                            />
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-card>
                    @else
                        <x-card>
                            <div class="text-center py-6">
                                <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Tidak Ada Data Kehadiran</h3>
                                <p class="text-gray-500 dark:text-gray-400">
                                    Belum ada karyawan yang melakukan absensi pada tanggal ini.
                                </p>
                            </div>
                        </x-card>
                    @endif
                </div>
            @endif
        <x-slot:footer>
            <x-button color="gray" wire:click="$set('modal', false)">
                Tutup
            </x-button>
        </x-slot:footer>
    </x-modal>

    {{-- Legend --}}
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Keterangan</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-blue-100 border border-blue-200 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Hari Kerja Normal</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-red-100 border border-red-200 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Libur/Weekend</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-green-100 border border-green-200 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Jadwal Khusus</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Ada Aktivitas Karyawan</span>
            </div>
        </div>
    </x-card>
</div>