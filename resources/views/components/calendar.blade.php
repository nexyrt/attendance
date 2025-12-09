<div x-data="{
    currentMonth: new Date(),
    selectedDate: @entangle('selectedDate').live,
    holidays: @js($scheduleExceptions),

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
        // Format: YYYY-MM-DD untuk konsistensi
        const year = day.fullDate.getFullYear();
        const month = String(day.fullDate.getMonth() + 1).padStart(2, '0');
        const date = String(day.date).padStart(2, '0');
        const dateStr = `${year}-${month}-${date}`;
        return this.holidays.find(h => h.date === dateStr);
    }
}" class="p-3 w-full">

    {{-- Calendar Header --}}
    <div class="flex justify-center pt-1 relative items-center mb-4">
        {{-- Previous Month Button --}}
        <button @click="prevMonth()" type="button"
            class="absolute left-1 h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100 inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-300 dark:border-gray-700">
            <x-icon name="chevron-left" class="h-4 w-4" />
        </button>

        {{-- Month/Year Label --}}
        <div class="text-sm font-medium" x-text="monthName"></div>

        {{-- Next Month Button --}}
        <button @click="nextMonth()" type="button"
            class="absolute right-1 h-7 w-7 bg-transparent p-0 opacity-50 hover:opacity-100 inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors hover:bg-gray-100 dark:hover:bg-gray-800 border border-gray-300 dark:border-gray-700">
            <x-icon name="chevron-right" class="h-4 w-4" />
        </button>
    </div>

    {{-- Calendar Grid --}}
    <div class="w-full">
        {{-- Weekday Headers --}}
        <div class="flex w-full justify-between mb-2">
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Su</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Mo</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Tu</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">We</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Th</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Fr</div>
            <div class="text-gray-500 dark:text-gray-400 flex-1 text-center font-normal text-xs">Sa</div>
        </div>

        {{-- Calendar Days --}}
        <div class="space-y-1">
            <template x-for="week in 6" :key="week">
                <div class="flex w-full justify-between">
                    <template x-for="dayIndex in 7" :key="dayIndex">
                        <div class="flex-1 text-center text-sm p-0 relative">
                            <template x-if="daysInMonth[(week - 1) * 7 + (dayIndex - 1)]">
                                <button type="button" @click="selectDate(daysInMonth[(week - 1) * 7 + (dayIndex - 1)])"
                                    :class="{
                                        'opacity-50': !daysInMonth[(week - 1) * 7 + (dayIndex - 1)].isCurrentMonth,
                                        'bg-blue-600 text-white hover:bg-blue-700': isSelected(daysInMonth[(week - 1) *
                                            7 + (dayIndex - 1)]),
                                        'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-50': isToday(
                                            daysInMonth[(week - 1) * 7 + (dayIndex - 1)]) && !isSelected(
                                            daysInMonth[(week - 1) * 7 + (dayIndex - 1)]),
                                        'ring-2 ring-red-500': getException(daysInMonth[(week - 1) * 7 + (dayIndex -
                                            1)])?.status === 'holiday',
                                        'ring-2 ring-blue-500': getException(daysInMonth[(week - 1) * 7 + (dayIndex -
                                            1)])?.status === 'event',
                                    }"
                                    class="h-9 w-9 p-0 font-normal rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors cursor-pointer"
                                    x-text="daysInMonth[(week - 1) * 7 + (dayIndex - 1)].date">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
