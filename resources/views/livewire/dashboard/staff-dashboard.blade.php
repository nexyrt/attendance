<div x-data="{
    currentTime: @entangle('currentTime'),
    status: @entangle('status'),
    checkInTime: @entangle('checkInTime'),
    checkOutTime: @entangle('checkOutTime'),
    elapsed: 0,
    intervalId: null,

    updateElapsed() {
        if (this.checkInTime && this.status === 'checked_in') {
            const checkIn = new Date();
            const [h, m, s] = this.checkInTime.split(':');
            checkIn.setHours(h, m, s);
            const diff = Math.floor((new Date() - checkIn) / 1000);
            this.elapsed = diff;
        }
    },

    formatWorkingHours() {
        const h = Math.floor(this.elapsed / 3600);
        const m = Math.floor((this.elapsed % 3600) / 60);
        const s = this.elapsed % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
}" x-init="setInterval(() => $wire.updateTime(), 1000);

$watch('status', (value) => {
    if (value === 'checked_in') {
        if (intervalId) clearInterval(intervalId);
        intervalId = setInterval(() => updateElapsed(), 1000);
    } else if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }
});

if (status === 'checked_in') {
    intervalId = setInterval(() => updateElapsed(), 1000);
}" class="space-y-4 md:space-y-6">

    {{-- HEADER - Welcome & Quick Stats --}}
    <div class="space-y-4 md:space-y-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-gray-50">
                Welcome back, {{ $currentUser['name'] }} 👋
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-400">
                {{ $currentUser['role'] }} · {{ $currentUser['department'] }}
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            @foreach ($stats as $stat)
                <x-card class="hover:shadow-md transition-shadow">
                    <div class="p-4 md:p-6">
                        <div class="flex items-center justify-between mb-2">
                            <x-icon name="{{ $stat['icon'] }}" class="w-4 h-4 md:w-5 md:h-5 {{ $stat['color'] }}" />
                            @if (isset($stat['trend']))
                                <div class="flex items-center gap-1 text-xs">
                                    @if ($stat['trend'] === 'up')
                                        <x-icon name="arrow-trending-up" class="w-3 h-3 text-green-600" />
                                        <span class="text-green-600">{{ $stat['change'] }}</span>
                                    @else
                                        <x-icon name="arrow-trending-down" class="w-3 h-3 text-green-600" />
                                        <span class="text-green-600">{{ $stat['change'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-50">
                            {{ $stat['value'] }}
                        </div>
                        <div class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            {{ $stat['label'] }}
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    </div>

    {{-- MAIN CONTENT - 3 Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">

        {{-- LEFT COLUMN - Check-in & Leave Balance --}}
        <div class="lg:col-span-4 space-y-4 md:space-y-6">

            {{-- CHECK-IN WIDGET --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">Today's Attendance</span>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6 space-y-4 md:space-y-6">
                    {{-- Current Time --}}
                    <div class="text-center">
                        <div class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-gray-50 tabular-nums"
                            x-text="currentTime">
                            {{ $currentTime }}
                        </div>
                        <div class="mt-1 md:mt-2 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            {{ now()->format('l, F j, Y') }}
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div class="flex justify-center">
                        @if ($status === 'not_checked_in')
                            <x-badge color="gray" class="text-xs md:text-sm">
                                <x-icon name="clock" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Not Checked In
                            </x-badge>
                        @elseif($status === 'checked_in')
                            <x-badge color="green" class="text-xs md:text-sm">
                                <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Checked In
                            </x-badge>
                        @else
                            <x-badge color="blue" class="text-xs md:text-sm">
                                <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Checked Out
                            </x-badge>
                        @endif
                    </div>

                    {{-- Check-in/out Times --}}
                    @if ($checkInTime || $checkOutTime)
                        <div class="grid grid-cols-2 gap-3 md:gap-4">
                            <div
                                class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                                <div class="flex items-center gap-2 text-green-600 mb-1">
                                    <x-icon name="arrow-right-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="text-xs md:text-sm font-medium">Check In</span>
                                </div>
                                <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50">
                                    {{ $checkInTime ?? '--:--:--' }}
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                                <div class="flex items-center gap-2 text-blue-600 mb-1">
                                    <x-icon name="arrow-left-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                                    <span class="text-xs md:text-sm font-medium">Check Out</span>
                                </div>
                                <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50">
                                    {{ $checkOutTime ?? '--:--:--' }}
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Working Hours Counter (Active) --}}
                    @if ($status === 'checked_in')
                        <div
                            class="rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-900 bg-blue-50/50 dark:bg-blue-950/20 p-4 md:p-6 text-center">
                            <div class="flex items-center justify-center gap-2 text-blue-600 mb-2">
                                <x-icon name="clock" class="w-4 h-4 md:w-5 md:h-5" />
                                <span class="text-xs md:text-sm font-medium">Working Hours</span>
                            </div>
                            <div class="text-2xl md:text-3xl font-bold text-blue-600 tabular-nums"
                                x-text="formatWorkingHours()">
                                00:00:00
                            </div>
                        </div>
                    @endif

                    {{-- Working Hours Counter (Completed) --}}
                    @if ($status === 'checked_out' && $checkInTime && $checkOutTime)
                        <div class="rounded-lg border-2 border-green-200 dark:border-green-900 bg-green-50/50 dark:bg-green-950/20 p-4 md:p-6 text-center"
                            x-data="{
                                totalSeconds: 0,
                                init() {
                                    const checkIn = new Date();
                                    const checkOut = new Date();
                                    const [hIn, mIn, sIn] = '{{ $checkInTime }}'.split(':');
                                    const [hOut, mOut, sOut] = '{{ $checkOutTime }}'.split(':');
                                    checkIn.setHours(hIn, mIn, sIn);
                                    checkOut.setHours(hOut, mOut, sOut);
                                    this.totalSeconds = Math.floor((checkOut - checkIn) / 1000);
                                },
                                formatTime() {
                                    const h = Math.floor(this.totalSeconds / 3600);
                                    const m = Math.floor((this.totalSeconds % 3600) / 60);
                                    const s = this.totalSeconds % 60;
                                    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                                }
                            }">
                            <div class="flex items-center justify-center gap-2 text-green-600 mb-2">
                                <x-icon name="check-circle" class="w-4 h-4 md:w-5 md:h-5" />
                                <span class="text-xs md:text-sm font-medium">Total Working Hours</span>
                            </div>
                            <div class="text-2xl md:text-3xl font-bold text-green-600 tabular-nums"
                                x-text="formatTime()">
                                00:00:00
                            </div>
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="space-y-2 md:space-y-3">
                        @if ($status === 'not_checked_in')
                            <x-button wire:click="checkIn" color="green" class="w-full" size="lg">
                                <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 md:w-5 md:h-5 mr-2" />
                                Check In
                            </x-button>
                        @elseif($status === 'checked_in')
                            <x-button wire:click="checkOut" color="blue" class="w-full" size="lg">
                                <x-icon name="arrow-left-on-rectangle" class="w-4 h-4 md:w-5 md:h-5 mr-2" />
                                Check Out
                            </x-button>
                        @else
                            <div
                                class="rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950/20 p-3 md:p-4 text-center">
                                <x-icon name="check-circle" class="w-6 h-6 md:w-8 md:h-8 mx-auto mb-2 text-green-600" />
                                <p class="text-xs md:text-sm font-medium text-green-600">
                                    You've completed today's attendance
                                </p>
                            </div>
                        @endif

                        <div class="flex items-center gap-2 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                            <x-icon name="map-pin" class="w-3 h-3 md:w-4 md:h-4" />
                            <span>Main Office · Samarinda</span>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- LEAVE BALANCE WIDGET --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="shield-exclamation" class="w-5 h-5 text-blue-600" />
                            <span class="text-base md:text-lg font-semibold">Leave Balance</span>
                        </div>
                        <x-badge color="blue" light class="text-xs">2025</x-badge>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6 space-y-3 md:space-y-4">
                    @foreach ($leaveBalances as $leave)
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-900/30 p-3 md:p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-2 md:mb-3">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <div
                                        class="flex h-8 w-8 md:h-10 md:w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                        <x-icon name="{{ $leave['icon'] }}"
                                            class="w-4 h-4 md:w-5 md:h-5 {{ $leave['color'] }}" />
                                    </div>
                                    <div>
                                        <div class="text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-50">
                                            {{ $leave['type'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $leave['used'] }}/{{ $leave['total'] }} days used
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg md:text-xl font-bold {{ $leave['color'] }}">
                                        {{ $leave['total'] - $leave['used'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">left</div>
                                </div>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 md:h-2">
                                <div class="h-1.5 md:h-2 rounded-full transition-all {{ str_replace('text-', 'bg-', $leave['color']) }}"
                                    style="width: {{ ($leave['used'] / $leave['total']) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- CENTER COLUMN - Calendar & Events --}}
        <div class="lg:col-span-4 space-y-4 md:space-y-6">
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">Calendar</span>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6 space-y-4 md:space-y-6">
                    @include('components.calendar', ['scheduleExceptions' => $scheduleExceptions])

                    @if ($selectedDateException)
                        <div
                            class="rounded-lg border-2 border-dashed p-3 md:p-4
                            {{ $selectedDateException['status'] === 'holiday'
                                ? 'border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950/20'
                                : 'border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-950/20' }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 md:gap-3">
                                    <x-icon
                                        name="{{ $selectedDateException['status'] === 'holiday' ? 'star' : 'calendar' }}"
                                        class="w-4 h-4 md:w-5 md:h-5 {{ $selectedDateException['status'] === 'holiday' ? 'text-red-600' : 'text-blue-600' }}" />
                                    <div>
                                        <div class="text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-50">
                                            {{ $selectedDateException['title'] }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($selectedDateException['date'])->format('F j, Y') }}
                                        </div>
                                    </div>
                                </div>
                                <x-badge :color="$selectedDateException['status'] === 'holiday' ? 'red' : 'blue'" class="capitalize text-xs">
                                    {{ $selectedDateException['status'] }}
                                </x-badge>
                            </div>
                            @if ($selectedDateException['note'])
                                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $selectedDateException['note'] }}
                                </p>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-4 text-xs">
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-full bg-red-500/20 ring-1 ring-red-500/50"></span>
                            <span class="text-gray-600 dark:text-gray-400">Holiday</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-3 w-3 rounded-full bg-blue-500/20 ring-1 ring-blue-500/50"></span>
                            <span class="text-gray-600 dark:text-gray-400">Event</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="mb-2 md:mb-3 text-xs md:text-sm font-medium text-gray-900 dark:text-gray-50">
                            Upcoming
                        </h4>
                        <div class="space-y-2">
                            @foreach ($upcomingExceptions as $ex)
                                <div
                                    class="flex items-center justify-between rounded-lg border border-gray-200/50 dark:border-gray-800/50 bg-gray-50/50 dark:bg-gray-900/50 p-2 md:p-3 text-xs md:text-sm">
                                    <div class="flex items-center gap-2 md:gap-3">
                                        <div
                                            class="flex h-8 w-8 md:h-10 md:w-10 flex-col items-center justify-center rounded-lg
                                            {{ $ex['status'] === 'holiday' ? 'bg-red-500/10 text-red-600' : 'bg-blue-500/10 text-blue-600' }}">
                                            <span class="text-[10px] md:text-xs font-medium">
                                                {{ \Carbon\Carbon::parse($ex['date'])->format('M') }}
                                            </span>
                                            <span class="text-sm md:text-lg font-bold leading-none">
                                                {{ \Carbon\Carbon::parse($ex['date'])->format('d') }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-gray-50">{{ $ex['title'] }}
                                            </p>
                                            <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                                {{ \Carbon\Carbon::parse($ex['date'])->format('l') }}
                                            </p>
                                        </div>
                                    </div>
                                    <x-badge outline :color="$ex['status'] === 'holiday' ? 'red' : 'blue'" class="capitalize text-[10px] md:text-xs">
                                        {{ $ex['status'] }}
                                    </x-badge>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- RIGHT COLUMN - Weekly & Activity --}}
        <div class="lg:col-span-4 space-y-4 md:space-y-6">

            {{-- WEEKLY ATTENDANCE WIDGET --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="chart-bar-square" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">This Week</span>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6">
                    <div class="grid grid-cols-7 gap-1 md:gap-2">
                        @foreach ($weekData as $day)
                            <div
                                class="flex flex-col items-center rounded-lg border p-1.5 md:p-3 transition-all
                                @if ($day['status'] === 'present') bg-green-500/10 border-green-500/30
                                @elseif($day['status'] === 'late') bg-yellow-500/10 border-yellow-500/30
                                @elseif($day['status'] === 'absent') bg-red-500/10 border-red-500/30
                                @else bg-gray-500/10 border-gray-500/30 @endif">
                                <span class="text-[10px] md:text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $day['day'] }}
                                </span>
                                <span
                                    class="my-0.5 md:my-1 text-xs md:text-sm font-semibold text-gray-900 dark:text-gray-50">
                                    {{ $day['date'] }}
                                </span>
                                @if ($day['status'] === 'present')
                                    <x-icon name="check-circle" class="h-3 w-3 md:h-4 md:w-4 text-green-600" />
                                @elseif($day['status'] === 'late')
                                    <x-icon name="clock" class="h-3 w-3 md:h-4 md:w-4 text-yellow-600" />
                                @elseif($day['status'] === 'absent')
                                    <x-icon name="x-circle" class="h-3 w-3 md:h-4 md:w-4 text-red-600" />
                                @else
                                    <x-icon name="exclamation-circle" class="h-3 w-3 md:h-4 md:w-4 text-gray-600" />
                                @endif
                                @if ($day['hours'] > 0)
                                    <span
                                        class="mt-0.5 md:mt-1 text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                        {{ $day['hours'] }}h
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 md:mt-6 grid grid-cols-3 gap-2 md:gap-4">
                        @php
                            $presentDays = collect($weekData)
                                ->whereIn('status', ['present', 'late'])
                                ->count();
                            $totalHours = collect($weekData)->sum('hours');
                            $avgHours = $presentDays > 0 ? round($totalHours / $presentDays, 1) : 0;
                        @endphp

                        <x-card>
                            <div class="p-2 md:p-4 text-center">
                                <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-gray-50">
                                    {{ $presentDays }}
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                    Days Present
                                </div>
                            </div>
                        </x-card>

                        <x-card>
                            <div class="p-2 md:p-4 text-center">
                                <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-gray-50">
                                    {{ $totalHours }}h
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                    Total Hours
                                </div>
                            </div>
                        </x-card>

                        <x-card>
                            <div class="p-2 md:p-4 text-center">
                                <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-gray-50">
                                    {{ $avgHours }}h
                                </div>
                                <div class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                    Avg/Day
                                </div>
                            </div>
                        </x-card>
                    </div>
                </div>
            </x-card>

            {{-- RECENT ACTIVITY WIDGET --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="bolt" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">Recent Activity</span>
                    </div>
                </x-slot:header>

                <div class="divide-y divide-gray-200/50 dark:divide-gray-800/50">
                    @foreach ($activities as $act)
                        <div
                            class="flex items-center gap-3 md:gap-4 p-3 md:p-4 transition-colors hover:bg-gray-50/30 dark:hover:bg-gray-900/30">
                            <div
                                class="flex h-8 w-8 md:h-10 md:w-10 shrink-0 items-center justify-center rounded-full
                                @if ($act['type'] === 'check_in') bg-green-500/10
                                @elseif($act['type'] === 'check_out') bg-blue-500/10
                                @elseif($act['type'] === 'leave_approved') bg-green-500/10
                                @else bg-red-500/10 @endif">
                                @if ($act['type'] === 'check_in')
                                    <x-icon name="arrow-right-on-rectangle"
                                        class="h-3 w-3 md:h-4 md:w-4 text-green-600" />
                                @elseif($act['type'] === 'check_out')
                                    <x-icon name="arrow-left-on-rectangle"
                                        class="h-3 w-3 md:h-4 md:w-4 text-blue-600" />
                                @elseif($act['type'] === 'leave_approved')
                                    <x-icon name="check-circle" class="h-3 w-3 md:h-4 md:w-4 text-green-600" />
                                @else
                                    <x-icon name="clock" class="h-3 w-3 md:h-4 md:w-4 text-red-600" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs md:text-sm font-medium text-gray-900 dark:text-gray-50 truncate">
                                    {{ $act['desc'] }}
                                </p>
                                <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($act['time'])->format('D, M j, g:i A') }}
                                </p>
                            </div>
                            @if (isset($act['status']))
                                <x-badge color="green" class="shrink-0 text-[10px] md:text-xs capitalize">
                                    {{ $act['status'] }}
                                </x-badge>
                            @endif
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>

    {{-- FOOTER --}}
    <footer
        class="border-t border-gray-200/50 dark:border-gray-800/50 pt-4 text-center text-xs md:text-sm text-gray-500 dark:text-gray-400">
        <p>AttendEase - Staff Attendance Management System</p>
        <p class="mt-1 text-[10px] md:text-xs">Laravel/Livewire Implementation</p>
    </footer>
</div>
