{{-- Holiday Calendar --}}
<div class="rounded-2xl bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 overflow-hidden"
    x-data="{
        currentMonth: new Date(),
        holidays: @js($this->holidays->toArray()),
        selectedEvent: null,
        showEventModal: false,
    
        get monthStart() {
            return new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
        },
    
        get monthEnd() {
            return new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 0);
        },
    
        get calendarDays() {
            const start = new Date(this.monthStart);
            start.setDate(start.getDate() - (start.getDay() === 0 ? 6 : start.getDay() - 1));
    
            const days = [];
            const current = new Date(start);
    
            for (let i = 0; i < 42; i++) {
                days.push(new Date(current));
                current.setDate(current.getDate() + 1);
            }
    
            return days;
        },
    
        getHolidayForDate(date) {
            const dateStr = date.toISOString().split('T')[0];
            return this.holidays.find(h => h.date === dateStr);
        },
    
        isCurrentMonth(date) {
            return date.getMonth() === this.currentMonth.getMonth();
        },
    
        isToday(date) {
            const today = new Date();
            return date.toDateString() === today.toDateString();
        },
    
        isWeekend(date) {
            return date.getDay() === 0 || date.getDay() === 6;
        },
    
        previousMonth() {
            this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1);
        },
    
        nextMonth() {
            this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1);
        },
    
        formatMonth() {
            return this.currentMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        }
    }">
    <div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-semibold text-dark-900 dark:text-dark-50 text-lg">Kalender</h3>
            <div class="flex items-center gap-1">
                <x-button.circle icon="chevron-left" color="secondary" size="sm" x-on:click="previousMonth()" />
                <span class="text-sm font-medium min-w-[120px] text-center text-dark-900 dark:text-dark-50"
                    x-text="formatMonth()"></span>
                <x-button.circle icon="chevron-right" color="secondary" size="sm" x-on:click="nextMonth()" />
            </div>
        </div>

        {{-- Calendar Grid --}}
        <div class="mb-6">
            {{-- Day Headers --}}
            <div class="grid grid-cols-7 gap-1 mb-2">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                    <div class="text-center text-xs font-medium text-dark-500 dark:text-dark-400 py-2">
                        {{ $day }}
                    </div>
                @endforeach
            </div>

            {{-- Days Grid --}}
            <div class="grid grid-cols-7 gap-1">
                <template x-for="(day, idx) in calendarDays" :key="idx">
                    <div class="relative aspect-square flex items-center justify-center rounded-lg text-sm transition-all cursor-pointer group"
                        :class="{
                            'text-dark-300 dark:text-dark-600': !isCurrentMonth(day),
                            'text-dark-900 dark:text-dark-50 hover:bg-dark-50 dark:hover:bg-dark-700/30': isCurrentMonth(
                                day) && !getHolidayForDate(day) && !isWeekend(day),
                            'text-dark-400 dark:text-dark-500': isWeekend(day) && isCurrentMonth(day),
                            'bg-red-50 dark:bg-red-900/10 text-red-700 dark:text-red-400 font-medium hover:bg-red-100 dark:hover:bg-red-900/20': getHolidayForDate(
                                day)?.status === 'holiday' && isCurrentMonth(day),
                            'bg-blue-50 dark:bg-blue-900/10 text-blue-700 dark:text-blue-400 font-medium hover:bg-blue-100 dark:hover:bg-blue-900/20': getHolidayForDate(
                                day)?.status === 'event' && isCurrentMonth(day),
                            'ring-2 ring-primary-500 ring-offset-2 ring-offset-white dark:ring-offset-dark-800 font-bold': isToday(
                                day)
                        }"
                        @click="if(getHolidayForDate(day)) { selectedEvent = getHolidayForDate(day); showEventModal = true; }">
                        <span x-text="day.getDate()"></span>

                        {{-- Tooltip on hover --}}
                        <template x-if="getHolidayForDate(day) && isCurrentMonth(day)">
                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-dark-900 dark:bg-dark-700 text-white text-xs rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10"
                                x-text="getHolidayForDate(day)?.title">
                            </div>
                        </template>

                        {{-- Indicator dot --}}
                        <template x-if="getHolidayForDate(day) && isCurrentMonth(day)">
                            <div class="absolute bottom-0.5 left-1/2 -translate-x-1/2">
                                <div class="w-1 h-1 rounded-full"
                                    :class="getHolidayForDate(day)?.status === 'holiday' ? 'bg-red-500' : 'bg-blue-500'">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex items-center gap-4 text-xs text-dark-500 dark:text-dark-400 mb-6">
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-red-500"></div>
                <span>Libur</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <span>Event</span>
            </div>
        </div>

        {{-- Upcoming Holidays --}}
        @if ($this->upcomingHolidays->count() > 0)
            <div>
                <h4 class="text-sm font-medium text-dark-900 dark:text-dark-50 mb-3 flex items-center gap-2">
                    <x-heroicon-o-gift class="h-4 w-4 text-primary-500" />
                    Hari Libur Mendatang
                </h4>
                <div class="space-y-2">
                    @foreach ($this->upcomingHolidays as $holiday)
                        <div class="flex items-center justify-between py-2 px-3 rounded-lg bg-dark-50 dark:bg-dark-700/50 hover:bg-dark-100 dark:hover:bg-dark-700 transition-colors cursor-pointer"
                            @click="selectedEvent = @js($holiday); showEventModal = true;">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-star class="h-3.5 w-3.5 text-yellow-500" />
                                <span
                                    class="text-sm font-medium text-dark-900 dark:text-dark-50">{{ $holiday->title }}</span>
                            </div>
                            <span class="text-xs text-dark-500 dark:text-dark-400">
                                {{ \Carbon\Carbon::parse($holiday->date)->isoFormat('D MMM') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Event Detail Modal --}}
    <x-modal wire="showEventModal" title="Detail Event" size="md" center>
        <div class="space-y-4" x-show="selectedEvent">
            {{-- Event Type Badge --}}
            <div class="flex items-center gap-2">
                <template x-if="selectedEvent?.status === 'holiday'">
                    <x-badge text="Hari Libur" color="red" />
                </template>
                <template x-if="selectedEvent?.status === 'event'">
                    <x-badge text="Event" color="blue" />
                </template>
            </div>

            {{-- Title --}}
            <div>
                <h3 class="text-2xl font-bold text-dark-900 dark:text-dark-50" x-text="selectedEvent?.title"></h3>
            </div>

            {{-- Date --}}
            <div class="flex items-center gap-2 text-dark-600 dark:text-dark-400">
                <x-heroicon-o-calendar class="h-5 w-5" />
                <span
                    x-text="selectedEvent?.date ? new Date(selectedEvent.date).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : ''"></span>
            </div>

            {{-- Time (if event) --}}
            <template
                x-if="selectedEvent?.status === 'event' && (selectedEvent?.start_time || selectedEvent?.end_time)">
                <div class="flex items-center gap-2 text-dark-600 dark:text-dark-400">
                    <x-heroicon-o-clock class="h-5 w-5" />
                    <span>
                        <template x-if="selectedEvent?.start_time">
                            <span x-text="selectedEvent.start_time.substring(0, 5)"></span>
                        </template>
                        <template x-if="selectedEvent?.start_time && selectedEvent?.end_time">
                            <span> - </span>
                        </template>
                        <template x-if="selectedEvent?.end_time">
                            <span x-text="selectedEvent.end_time.substring(0, 5)"></span>
                        </template>
                    </span>
                </div>
            </template>

            {{-- Note --}}
            <template x-if="selectedEvent?.note">
                <div class="p-4 bg-dark-50 dark:bg-dark-700/50 rounded-lg">
                    <p class="text-sm text-dark-600 dark:text-dark-400 whitespace-pre-line" x-text="selectedEvent.note">
                    </p>
                </div>
            </template>

            {{-- No note message --}}
            <template x-if="!selectedEvent?.note">
                <div class="p-4 bg-dark-50 dark:bg-dark-700/50 rounded-lg text-center">
                    <p class="text-sm text-dark-500 dark:text-dark-500 italic">Tidak ada catatan tambahan</p>
                </div>
            </template>
        </div>

        <x-slot:footer>
            <div class="flex justify-end">
                <x-button @click="showEventModal = false" color="secondary">
                    Tutup
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
