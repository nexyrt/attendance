<div x-data="{
    currentMonth: new Date(),
    selectedDate: @entangle('selectedDate').live,
    exceptions: @js($this->scheduleExceptions),
    weekData: @js($this->weekData),

    get monthName() {
        return this.currentMonth.toLocaleString('default', { month: 'long', year: 'numeric' });
    },

    get daysInMonth() {
        const year = this.currentMonth.getFullYear();
        const month = this.currentMonth.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();

        let days = [];

        // Previous month padding
        const prevMonthLastDay = new Date(year, month, 0).getDate();
        for (let i = startingDayOfWeek - 1; i >= 0; i--) {
            days.push({
                date: prevMonthLastDay - i,
                isCurrentMonth: false,
                fullDate: new Date(year, month - 1, prevMonthLastDay - i)
            });
        }

        // Current month days
        for (let i = 1; i <= daysInMonth; i++) {
            days.push({
                date: i,
                isCurrentMonth: true,
                fullDate: new Date(year, month, i)
            });
        }

        // Next month padding
        const remainingDays = 42 - days.length;
        for (let i = 1; i <= remainingDays; i++) {
            days.push({
                date: i,
                isCurrentMonth: false,
                fullDate: new Date(year, month + 1, i)
            });
        }

        return days;
    },

    prevMonth() {
        this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1, 1);
    },

    nextMonth() {
        this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 1);
    },

    goToToday() {
        this.currentMonth = new Date();
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const date = String(today.getDate()).padStart(2, '0');
        this.selectedDate = `${year}-${month}-${date}`;
    },

    selectDate(day) {
        if (day.isCurrentMonth) {
            const year = day.fullDate.getFullYear();
            const month = String(day.fullDate.getMonth() + 1).padStart(2, '0');
            const date = String(day.date).padStart(2, '0');
            this.selectedDate = `${year}-${month}-${date}`;
        }
    },

    isToday(day) {
        const today = new Date();
        return day.isCurrentMonth &&
            day.date === today.getDate() &&
            day.fullDate.getMonth() === today.getMonth() &&
            day.fullDate.getFullYear() === today.getFullYear();
    },

    isSelected(day) {
        if (!this.selectedDate || !day.isCurrentMonth) return false;
        const selected = new Date(this.selectedDate);
        return day.date === selected.getDate() &&
            day.fullDate.getMonth() === selected.getMonth() &&
            day.fullDate.getFullYear() === selected.getFullYear();
    },

    getException(day) {
        if (!day.isCurrentMonth) return null;
        const year = day.fullDate.getFullYear();
        const month = String(day.fullDate.getMonth() + 1).padStart(2, '0');
        const date = String(day.date).padStart(2, '0');
        const dateStr = `${year}-${month}-${date}`;
        return this.exceptions.find(h => h.date === dateStr);
    },

    getAttendance(day) {
        if (!day.isCurrentMonth) return null;
        const year = day.fullDate.getFullYear();
        const month = String(day.fullDate.getMonth() + 1).padStart(2, '0');
        const date = String(day.date).padStart(2, '0');
        const dateStr = `${year}-${month}-${date}`;
        return this.weekData.find(w => w.full_date === dateStr);
    },

    getTooltipText(day) {
        const exception = this.getException(day);
        const attendance = this.getAttendance(day);

        let text = '';
        if (exception) {
            text = exception.title;
            if (exception.note) {
                text += ' - ' + exception.note;
            }
        }
        if (attendance && attendance.status) {
            const statusText = attendance.status === 'present' ? 'Present' :
                attendance.status === 'late' ? 'Late' :
                attendance.status === 'early_leave' ? 'Early Leave' :
                attendance.status;
            text += (text ? ' | ' : '') + statusText;
            if (attendance.hours) {
                text += ` (${attendance.hours}h)`;
            }
        }
        return text;
    }
}" class="space-y-4">

    {{-- Calendar Header --}}
    <div class="flex justify-between items-center">
        {{-- Previous Month Button --}}
        <button @click="prevMonth()" type="button"
            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-sm font-medium transition-all hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
            <x-icon name="chevron-left" class="h-4 w-4" />
        </button>

        {{-- Month/Year Label + Today Button --}}
        <div class="flex items-center gap-2">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-50" x-text="monthName"></div>
            <button @click="goToToday()" type="button"
                class="text-xs px-2 py-1 rounded bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors font-medium">
                Today
            </button>
        </div>

        {{-- Next Month Button --}}
        <button @click="nextMonth()" type="button"
            class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-sm font-medium transition-all hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300">
            <x-icon name="chevron-right" class="h-4 w-4" />
        </button>
    </div>

    {{-- Calendar Grid --}}
    <div class="w-full">
        {{-- Weekday Headers --}}
        <div class="grid grid-cols-7 gap-1 mb-2">
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Su</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Mo</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Tu</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">We</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Th</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Fr</div>
            <div class="text-gray-500 dark:text-gray-400 text-center font-medium text-xs py-1">Sa</div>
        </div>

        {{-- Calendar Days --}}
        <div class="space-y-1">
            <template x-for="week in 6" :key="week">
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="dayIndex in 7" :key="dayIndex">
                        <div class="flex items-center justify-center">
                            <template x-if="daysInMonth[(week - 1) * 7 + (dayIndex - 1)]">
                                <div class="relative group w-full flex items-center justify-center">
                                    <button type="button"
                                        @click="selectDate(daysInMonth[(week - 1) * 7 + (dayIndex - 1)])"
                                        :class="{
                                            'opacity-40 hover:opacity-60': !daysInMonth[(week - 1) * 7 + (dayIndex - 1)]
                                                .isCurrentMonth,
                                            'bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-sm': isSelected(
                                                daysInMonth[(week - 1) * 7 + (dayIndex - 1)]),
                                            'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-50 font-medium': isToday(
                                                daysInMonth[(week - 1) * 7 + (dayIndex - 1)]) && !isSelected(
                                                daysInMonth[(week - 1) * 7 + (dayIndex - 1)]),
                                            'ring-2 ring-red-500 ring-inset': getException(daysInMonth[(week - 1) * 7 +
                                                (dayIndex - 1)])?.status === 'holiday',
                                            'ring-2 ring-blue-500 ring-inset': getException(daysInMonth[(week - 1) * 7 +
                                                (dayIndex - 1)])?.status === 'event',
                                            'ring-2 ring-yellow-500 ring-inset': getException(daysInMonth[(week - 1) *
                                                7 + (dayIndex - 1)])?.status === 'regular',
                                            'hover:bg-gray-100 dark:hover:bg-gray-800': !isSelected(daysInMonth[(week -
                                                1) * 7 + (dayIndex - 1)]) && !isToday(daysInMonth[(week - 1) * 7 + (
                                                dayIndex - 1)])
                                        }"
                                        class="h-10 w-full rounded-lg transition-all cursor-pointer text-sm flex flex-col items-center justify-center relative overflow-hidden"
                                        x-text="daysInMonth[(week - 1) * 7 + (dayIndex - 1)].date">

                                        {{-- Attendance Indicator Dot --}}
                                        <template
                                            x-if="getAttendance(daysInMonth[(week - 1) * 7 + (dayIndex - 1)])?.status">
                                            <div class="absolute bottom-1 w-1 h-1 rounded-full"
                                                :class="{
                                                    'bg-green-500': getAttendance(daysInMonth[(week - 1) * 7 + (
                                                        dayIndex - 1)])?.status === 'present',
                                                    'bg-orange-500': getAttendance(daysInMonth[(week - 1) * 7 + (
                                                        dayIndex - 1)])?.status === 'late',
                                                    'bg-yellow-500': getAttendance(daysInMonth[(week - 1) * 7 + (
                                                        dayIndex - 1)])?.status === 'early_leave',
                                                    'bg-red-500': getAttendance(daysInMonth[(week - 1) * 7 + (dayIndex -
                                                        1)])?.status === 'holiday'
                                                }">
                                            </div>
                                        </template>
                                    </button>

                                    {{-- Tooltip --}}
                                    <div x-show="getTooltipText(daysInMonth[(week - 1) * 7 + (dayIndex - 1)])" x-cloak
                                        class="absolute bottom-full mb-2 hidden group-hover:block z-10 px-3 py-1.5 text-xs font-medium text-white bg-gray-900 dark:bg-gray-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none"
                                        x-text="getTooltipText(daysInMonth[(week - 1) * 7 + (dayIndex - 1)])">
                                        <div
                                            class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900 dark:bg-gray-700">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    {{-- Legend --}}
    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Legend:</div>
        <div class="space-y-2">
            {{-- Calendar Indicators --}}
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <div
                        class="h-5 w-5 rounded border-2 border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-800">
                    </div>
                    <span class="text-gray-600 dark:text-gray-400">Today</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 rounded bg-blue-600"></div>
                    <span class="text-gray-600 dark:text-gray-400">Selected</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 rounded ring-2 ring-red-500 ring-inset bg-white dark:bg-gray-900"></div>
                    <span class="text-gray-600 dark:text-gray-400">Holiday</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-5 w-5 rounded ring-2 ring-blue-500 ring-inset bg-white dark:bg-gray-900"></div>
                    <span class="text-gray-600 dark:text-gray-400">Event</span>
                </div>
            </div>

            {{-- Attendance Dots --}}
            <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-green-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Present</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Late</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-2 w-2 rounded-full bg-yellow-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Early Leave</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
