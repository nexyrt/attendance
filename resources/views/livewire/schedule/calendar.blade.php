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
                        class="min-h-[100px] p-1 border border-gray-200 dark:border-gray-700 
                        {{ $day['isCurrentMonth'] ? '' : 'bg-gray-50 dark:bg-gray-800' }}
                        {{ $day['isToday'] ? 'ring-2 ring-blue-500' : '' }}
                        {{ $day['isWeekend'] ? 'bg-red-50 dark:bg-red-900/10' : '' }}">

                        {{-- Date Number --}}
                        <div
                            class="text-sm font-medium {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400' }}
                            {{ $day['isToday'] ? 'text-blue-600 font-bold' : '' }}">
                            {{ $day['date']->day }}
                        </div>

                        {{-- Exception Badge --}}
                        @if ($day['exception'])
                            <div class="mt-1">
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
                                <div class="text-xs text-gray-600 dark:text-gray-300 mt-1 truncate">
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
                            <div class="mt-1">
                                <div class="text-xs text-gray-600 dark:text-gray-300">
                                    ⏰
                                    {{ $day['schedule']->start_time->format('H:i') }}-{{ $day['schedule']->end_time->format('H:i') }}
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
                    </div>
                @endforeach
            @endforeach
        </div>
    </x-card>

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
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-200 rounded"></div>
                <span class="text-sm text-gray-700 dark:text-gray-300">Event/Training</span>
            </div>
        </div>
    </x-card>
</div>
