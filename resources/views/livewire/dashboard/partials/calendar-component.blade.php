{{-- Simple Calendar - Holidays & Events Only --}}
<x-card>
    <x-slot:header>
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-2">
                <x-icon name="calendar" class="w-5 h-5 text-blue-600" />
                <span class="text-base md:text-lg font-semibold">{{ $this->calendarData['monthName'] }}</span>
            </div>
            <div class="flex gap-2">
                <x-button.circle icon="chevron-left" wire:click="previousMonth" size="sm" color="gray" />
                <x-button.circle icon="chevron-right" wire:click="nextMonth" size="sm" color="gray" />
            </div>
        </div>
    </x-slot:header>

    <div class="p-4">
        {{-- Calendar Grid --}}
        <div class="grid grid-cols-7 gap-1">
            {{-- Day Headers --}}
            @foreach (['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $day)
                <div class="text-center text-xs font-semibold text-gray-600 dark:text-gray-400 py-2">
                    {{ $day }}
                </div>
            @endforeach

            {{-- Empty cells before month starts --}}
            @for ($i = 0; $i < $this->calendarData['startDayOfWeek']; $i++)
                <div class="aspect-square"></div>
            @endfor

            {{-- Date cells --}}
            @foreach ($this->calendarData['days'] as $index => $day)
                <button wire:click="selectedDayIndex = {{ $index }}; $wire.$toggle('eventModal')"
                    @class([
                        'aspect-square rounded-lg flex flex-col items-center justify-center text-sm transition-all',
                        'hover:shadow-md' => $day['exception'],
                        'cursor-pointer' => $day['exception'],
                        'cursor-default' => !$day['exception'],
                    
                        // Today
                        'bg-blue-500 text-white font-bold hover:bg-blue-600' =>
                            $day['isToday'] && !$day['exception'],
                    
                        // Holiday
                        'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 border-2 border-red-300 dark:border-red-700 hover:bg-red-200 dark:hover:bg-red-900/50' =>
                            $day['exception'] && $day['exception']['status'] === 'holiday',
                    
                        // Event
                        'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 border-2 border-purple-300 dark:border-purple-700 hover:bg-purple-200 dark:hover:bg-purple-900/50' =>
                            $day['exception'] && $day['exception']['status'] === 'event',
                    
                        // Regular (if no event today)
                        'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 border-2 border-green-300 dark:border-green-700' =>
                            $day['exception'] && $day['exception']['status'] === 'regular',
                    
                        // Weekend
                        'bg-gray-50 dark:bg-gray-800/50 text-gray-400 dark:text-gray-600' =>
                            $day['isWeekend'] && !$day['exception'] && !$day['isToday'],
                    
                        // Normal day
                        'hover:bg-gray-50 dark:hover:bg-gray-800' =>
                            !$day['isWeekend'] && !$day['exception'] && !$day['isToday'],
                    ]) type="button">
                    <span class="font-semibold">{{ $day['day'] }}</span>
                    @if ($day['exception'])
                        <span class="text-[8px] mt-0.5 leading-tight text-center px-1">
                            {{ Str::limit($day['exception']['title'], 10) }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3 text-xs">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 rounded bg-red-100 dark:bg-red-900/30 border-2 border-red-300 dark:border-red-700">
                </div>
                <span class="text-gray-600 dark:text-gray-400">Holiday</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-3 h-3 rounded bg-purple-100 dark:bg-purple-900/30 border-2 border-purple-300 dark:border-purple-700">
                </div>
                <span class="text-gray-600 dark:text-gray-400">Event</span>
            </div>
            <div class="flex items-center gap-2">
                <div
                    class="w-3 h-3 rounded bg-green-100 dark:bg-green-900/30 border-2 border-green-300 dark:border-green-700">
                </div>
                <span class="text-gray-600 dark:text-gray-400">Regular</span>
            </div>
        </div>

        {{-- Events & Holidays List --}}
        @php
            $monthEvents = collect($this->calendarData['days'])->filter(fn($day) => $day['exception'])->sortBy('day');
        @endphp

        @if ($monthEvents->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 space-y-2">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-50 mb-3">This Month</h4>

                @foreach ($monthEvents as $index => $event)
                    @php
                        $dayIndex = array_search($event, $this->calendarData['days']);
                    @endphp
                    <button wire:click="selectedDayIndex = {{ $dayIndex }}; $wire.$toggle('eventModal')"
                        class="w-full text-left rounded-lg border p-3 transition-all hover:shadow-md @if ($event['exception']['status'] === 'holiday') border-red-200 dark:border-red-800 bg-red-50/50 dark:bg-red-900/10 hover:bg-red-50 dark:hover:bg-red-900/20 @elseif($event['exception']['status'] === 'event') border-purple-200 dark:border-purple-800 bg-purple-50/50 dark:bg-purple-900/10 hover:bg-purple-50 dark:hover:bg-purple-900/20 @else border-green-200 dark:border-green-800 bg-green-50/50 dark:bg-green-900/10 hover:bg-green-50 dark:hover:bg-green-900/20 @endif">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-12 text-center">
                                <div
                                    class="text-2xl font-bold @if ($event['exception']['status'] === 'holiday') text-red-600 dark:text-red-400 @elseif($event['exception']['status'] === 'event') text-purple-600 dark:text-purple-400 @else text-green-600 dark:text-green-400 @endif">
                                    {{ $event['day'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $event['date']->format('D') }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h5 class="font-medium text-sm text-gray-900 dark:text-gray-50 truncate">
                                        {{ $event['exception']['title'] }}
                                    </h5>
                                    <x-badge :text="ucfirst($event['exception']['status'])" :color="$event['exception']['status'] === 'holiday' ? 'red' : ($event['exception']['status'] === 'event' ? 'purple' : 'green')" xs />
                                </div>
                                @if ($event['exception']['start_time'] || $event['exception']['end_time'])
                                    <div class="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                        <x-icon name="clock" class="w-3 h-3" />
                                        <span>
                                            @if ($event['exception']['start_time'] && $event['exception']['end_time'])
                                                {{ $event['exception']['start_time'] }} -
                                                {{ $event['exception']['end_time'] }}
                                            @elseif($event['exception']['start_time'])
                                                From {{ $event['exception']['start_time'] }}
                                            @else
                                                Until {{ $event['exception']['end_time'] }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <x-icon name="chevron-right" class="w-4 h-4 text-gray-400 flex-shrink-0" />
                        </div>
                    </button>
                @endforeach
            </div>
        @endif
    </div>
</x-card>

{{-- Event Detail Modal --}}
<x-modal wire="eventModal" size="lg" center>
    @if ($this->selectedEvent)
        <x-slot:title>
            <div class="flex items-center gap-4 my-3">
                <div @class([
                    'h-12 w-12 rounded-xl flex items-center justify-center',
                    'bg-red-50 dark:bg-red-900/20' => $this->selectedEvent['status'] === 'holiday',
                    'bg-purple-50 dark:bg-purple-900/20' =>
                        $this->selectedEvent['status'] === 'event',
                    'bg-green-50 dark:bg-green-900/20' =>
                        $this->selectedEvent['status'] === 'regular',
                ])>
                    <x-icon :name="$this->selectedEvent['status'] === 'holiday' ? 'calendar-days' : ($this->selectedEvent['status'] === 'event' ? 'sparkles' : 'calendar')" @class([
                        'w-6 h-6',
                        'text-red-600 dark:text-red-400' => $this->selectedEvent['status'] === 'holiday',
                        'text-purple-600 dark:text-purple-400' =>
                            $this->selectedEvent['status'] === 'event',
                        'text-green-600 dark:text-green-400' =>
                            $this->selectedEvent['status'] === 'regular',
                    ]) />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-50">
                        {{ $this->selectedEvent['title'] }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $this->selectedEvent['date'] }}</p>
                </div>
            </div>
        </x-slot:title>

        <div class="space-y-4">
            {{-- Status Badge --}}
            <div>
                <x-badge :text="ucfirst($this->selectedEvent['status'])" :color="$this->selectedEvent['status'] === 'holiday' ? 'red' : ($this->selectedEvent['status'] === 'event' ? 'purple' : 'green')" lg />
            </div>

            {{-- Time Info (if exists) --}}
            @if ($this->selectedEvent['start_time'] || $this->selectedEvent['end_time'])
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <x-icon name="clock" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-50">Schedule</span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if ($this->selectedEvent['start_time'] && $this->selectedEvent['end_time'])
                            {{ $this->selectedEvent['start_time'] }} - {{ $this->selectedEvent['end_time'] }}
                        @elseif($this->selectedEvent['start_time'])
                            From {{ $this->selectedEvent['start_time'] }}
                        @else
                            Until {{ $this->selectedEvent['end_time'] }}
                        @endif
                    </div>
                </div>
            @endif

            {{-- Note/Description --}}
            @if ($this->selectedEvent['note'])
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <x-icon name="document-text" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-50">Details</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-line">
                        {{ $this->selectedEvent['note'] }}
                    </p>
                </div>
            @else
                <div class="text-sm text-gray-500 dark:text-gray-400 italic text-center py-4">
                    No additional details
                </div>
            @endif
        </div>

        <x-slot:footer>
            <x-button wire:click="$toggle('eventModal')" color="gray" class="w-full">
                Close
            </x-button>
        </x-slot:footer>
    @endif
</x-modal>
