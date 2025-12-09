<div x-data="{
    currentTime: @entangle('currentTime'),
    status: @entangle('status'),
    checkInTime: @entangle('checkInTime'),
    checkOutTime: @entangle('checkOutTime'),
    elapsed: 0,
    intervalId: null,

    // Geolocation management
    getLocation() {
        if (navigator.geolocation) {
            $wire.set('locationLoading', true);
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    $wire.updateLocation(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    let message = 'Unable to get location. ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Please allow location access.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Location unavailable.';
                            break;
                        case error.TIMEOUT:
                            message += 'Request timed out.';
                            break;
                    }
                    $wire.setLocationError(message);
                }
            );
        } else {
            $wire.setLocationError('Geolocation not supported.');
        }
    },

    updateElapsed() {
        if (!this.checkInTime) {
            this.elapsed = 0;
            return;
        }
        const checkIn = new Date(this.checkInTime);
        const now = new Date();
        this.elapsed = Math.floor((now - checkIn) / 1000);
    },

    calculateFinalElapsed() {
        if (!this.checkInTime || !this.checkOutTime) {
            this.elapsed = 0;
            return;
        }
        const checkIn = new Date(this.checkInTime);
        const checkOut = new Date(this.checkOutTime);
        this.elapsed = Math.floor((checkOut - checkIn) / 1000);
    },

    formatWorkingHours() {
        const h = Math.floor(this.elapsed / 3600);
        const m = Math.floor((this.elapsed % 3600) / 60);
        const s = this.elapsed % 60;
        return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
    }
}" x-init="// Auto-detect location on load
getLocation();

// Update clock every second
setInterval(() => {
    const now = new Date();
    currentTime = now.toTimeString().split(' ')[0];
}, 1000);

// Watch status for working hours counter
$watch('status', (value) => {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
    }

    if (value === 'checked_in') {
        updateElapsed();
        intervalId = setInterval(() => {
            updateElapsed();
        }, 1000);
    } else if (value === 'completed') {
        calculateFinalElapsed();
    } else {
        elapsed = 0;
    }
});

// Initialize on mount
if (status === 'checked_in') {
    updateElapsed();
    intervalId = setInterval(() => {
        updateElapsed();
    }, 1000);
} else if (status === 'completed') {
    calculateFinalElapsed();
}" class="space-y-4 md:space-y-6">

    {{-- HEADER - Welcome & Quick Stats --}}
    <div class="space-y-4 md:space-y-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl md:text-4xl font-bold text-gray-900 dark:text-gray-50">
                Welcome back, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-400">
                {{ auth()->user()->roles->first()?->name ? ucfirst(auth()->user()->roles->first()->name) : 'Staff' }} ·
                {{ auth()->user()->department?->name ?? 'No Department' }}
            </p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            @foreach ([['icon' => 'chart-bar', 'value' => $this->quickStats['attendance_rate'] . '%', 'label' => 'Attendance Rate', 'color' => 'text-blue-600'], ['icon' => 'clock', 'value' => $this->quickStats['late_count'], 'label' => 'Late Arrivals', 'color' => 'text-orange-600'], ['icon' => 'clock', 'value' => $this->quickStats['avg_hours'] . 'h', 'label' => 'Avg Hours', 'color' => 'text-purple-600'], ['icon' => 'calendar', 'value' => $this->quickStats['days_worked'], 'label' => 'Days Worked', 'color' => 'text-green-600']] as $stat)
                <x-card class="hover:shadow-md transition-shadow">
                    <div class="p-4 md:p-6">
                        <div class="flex items-center justify-between mb-2">
                            <x-icon name="{{ $stat['icon'] }}" class="w-4 h-4 md:w-5 md:h-5 {{ $stat['color'] }}" />
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
                        <template x-if="status === 'not_started'">
                            <x-badge color="gray" class="text-xs md:text-sm">
                                <x-icon name="clock" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Not Checked In
                            </x-badge>
                        </template>
                        <template x-if="status === 'checked_in'">
                            <x-badge color="green" class="text-xs md:text-sm">
                                <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Checked In
                            </x-badge>
                        </template>
                        <template x-if="status === 'completed'">
                            <x-badge color="blue" class="text-xs md:text-sm">
                                <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                                Checked Out
                            </x-badge>
                        </template>
                    </div>

                    {{-- Check-in/out Times --}}
                    <div class="grid grid-cols-2 gap-3 md:gap-4">
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                            <div class="flex items-center gap-2 text-green-600 mb-1">
                                <x-icon name="arrow-right-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                                <span class="text-xs md:text-sm font-medium">Check In</span>
                            </div>
                            <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50"
                                x-text="checkInTime ? new Date(checkInTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '--:--'">
                                --:--
                            </div>
                        </div>
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                            <div class="flex items-center gap-2 text-blue-600 mb-1">
                                <x-icon name="arrow-left-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                                <span class="text-xs md:text-sm font-medium">Check Out</span>
                            </div>
                            <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50"
                                x-text="checkOutTime ? new Date(checkOutTime).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) : '--:--'">
                                --:--
                            </div>
                        </div>
                    </div>

                    {{-- Working Hours --}}
                    <div
                        class="rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-900 bg-blue-50/30 dark:bg-blue-950/30 p-3 md:p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300">Working
                                Hours</span>
                            <x-icon name="clock" class="w-4 h-4 text-blue-600" />
                        </div>
                        <div class="mt-2 text-2xl md:text-3xl font-bold text-blue-600 tabular-nums"
                            x-text="formatWorkingHours()">
                            00:00:00
                        </div>
                    </div>

                    {{-- Location Status --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300">Location
                                Status</span>
                            <button x-on:click="getLocation()"
                                class="text-xs text-blue-600 hover:text-blue-700 font-medium">
                                Refresh
                            </button>
                        </div>

                        <div x-show="$wire.locationLoading" class="text-center py-4">
                            <div
                                class="w-6 h-6 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-2">
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Getting location...</p>
                        </div>

                        <div x-show="$wire.locationError && !$wire.locationLoading"
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <x-icon name="exclamation-circle" class="w-5 h-5 text-red-600 dark:text-red-400" />
                                <div>
                                    <p class="text-sm font-medium text-red-900 dark:text-red-100">Location Error</p>
                                    <p class="text-xs text-red-700 dark:text-red-300" x-text="$wire.locationError"></p>
                                </div>
                            </div>
                        </div>

                        <div x-show="!$wire.locationLoading && !$wire.locationError" class="space-y-2">
                            @foreach ($this->officeLocations as $office)
                                @php
                                    $distance = null;
                                    $isInRange = false;
                                    if ($this->latitude && $this->longitude) {
                                        $distance = $this->calculateDistance(
                                            $this->latitude,
                                            $this->longitude,
                                            $office->latitude,
                                            $office->longitude,
                                        );
                                        $isInRange = $distance <= $office->radius;
                                    }
                                @endphp

                                <div
                                    class="flex items-center justify-between p-3 rounded-lg {{ $isInRange ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-lg flex items-center justify-center {{ $isInRange ? 'bg-green-100 dark:bg-green-800' : 'bg-gray-200 dark:bg-gray-600' }}">
                                            <x-icon name="building-office"
                                                class="w-4 h-4 {{ $isInRange ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}" />
                                        </div>
                                        <div>
                                            <div
                                                class="text-sm font-medium {{ $isInRange ? 'text-green-900 dark:text-green-100' : 'text-gray-900 dark:text-gray-100' }}">
                                                {{ $office->name }}
                                            </div>
                                            @if ($distance !== null)
                                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ number_format($distance, 0) }}m away
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-badge :color="$isInRange ? 'green' : 'gray'" :text="$office->radius . 'm'" xs />
                                        @if ($isInRange)
                                            <x-icon name="check-circle"
                                                class="w-4 h-4 text-green-600 dark:text-green-400" />
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="grid grid-cols-2 gap-3 md:gap-4">
                        <x-button wire:click="checkIn" :disabled="!$this->canCheckIn" color="green" class="w-full"
                            loading="checkIn">
                            <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 mr-2" />
                            Check In
                        </x-button>
                        <x-button wire:click="openNotesModal" :disabled="!$this->canCheckOut" color="blue" class="w-full">
                            <x-icon name="arrow-left-on-rectangle" class="w-4 h-4 mr-2" />
                            Check Out
                        </x-button>
                    </div>
                </div>
            </x-card>

            {{-- LEAVE BALANCE WIDGET --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">Leave Balance</span>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6 space-y-3 md:space-y-4">
                    @foreach ($this->leaveBalances as $leave)
                        <div
                            class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/30 dark:bg-gray-900/30 p-3 md:p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <x-icon name="{{ $leave['icon'] }}" class="w-4 h-4 {{ $leave['color'] }}" />
                                    <span class="text-xs md:text-sm font-medium text-gray-900 dark:text-gray-50">
                                        {{ $leave['type'] }}
                                    </span>
                                </div>
                                <span class="text-xs md:text-sm font-bold text-gray-900 dark:text-gray-50">
                                    {{ $leave['total'] - $leave['used'] }}/{{ $leave['total'] }}
                                </span>
                            </div>
                            <div class="h-1.5 md:h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                <div class="h-full rounded-full transition-all {{ str_replace('text-', 'bg-', $leave['color']) }}"
                                    style="width: {{ $leave['total'] > 0 ? (($leave['total'] - $leave['used']) / $leave['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>
        </div>

        {{-- CENTER COLUMN - Calendar --}}
        <div class="lg:col-span-4">
            <x-card>
                <x-slot:header>
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" class="w-5 h-5 text-blue-600" />
                        <span class="text-base md:text-lg font-semibold">Calendar</span>
                    </div>
                </x-slot:header>

                <div class="p-4 md:p-6">
                    @include('livewire.dashboard.partials.calendar-component')
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
                        @foreach ($this->weekData as $day)
                            <div
                                class="flex flex-col items-center rounded-lg border p-1.5 md:p-3 transition-all
                                @if ($day['status'] === 'present') bg-green-500/10 border-green-500/30
                                @elseif($day['status'] === 'late') bg-yellow-500/10 border-yellow-500/30
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
                                @elseif($day['status'] === 'early_leave')
                                    <x-icon name="arrow-left-on-rectangle"
                                        class="h-3 w-3 md:h-4 md:w-4 text-orange-600" />
                                @else
                                    <x-icon name="minus" class="h-3 w-3 md:h-4 md:w-4 text-gray-400" />
                                @endif
                                @if ($day['hours'])
                                    <span
                                        class="mt-0.5 md:mt-1 text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($day['hours'], 1) }}h
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 md:mt-6 grid grid-cols-3 gap-2 md:gap-4">
                        @php
                            $presentDays = collect($this->weekData)
                                ->whereIn('status', ['present', 'late'])
                                ->count();
                            $totalHours = collect($this->weekData)->sum('hours');
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
                                    {{ number_format($totalHours, 1) }}h
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
                    @forelse($this->activities as $act)
                        <div
                            class="flex items-center gap-3 md:gap-4 p-3 md:p-4 transition-colors hover:bg-gray-50/30 dark:hover:bg-gray-900/30">
                            <div
                                class="flex h-8 w-8 md:h-10 md:w-10 shrink-0 items-center justify-center rounded-full
                                @if ($act['type'] === 'check_in') bg-green-500/10
                                @else bg-blue-500/10 @endif">
                                @if ($act['type'] === 'check_in')
                                    <x-icon name="arrow-right-on-rectangle"
                                        class="h-3 w-3 md:h-4 md:w-4 text-green-600" />
                                @else
                                    <x-icon name="arrow-left-on-rectangle"
                                        class="h-3 w-3 md:h-4 md:w-4 text-blue-600" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs md:text-sm font-medium text-gray-900 dark:text-gray-50 truncate">
                                    {{ $act['desc'] }}
                                </p>
                                <p class="text-[10px] md:text-xs text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($act['time'])->diffForHumans() }}
                                </p>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($act['time'])->format('H:i') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 p-4">
                            <x-icon name="inbox" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-2" />
                            <p class="text-sm text-gray-600 dark:text-gray-400">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </x-card>
        </div>
    </div>

    {{-- Notes Modal for Checkout --}}
    <x-modal wire="modal" size="2xl" center :title="$this->isEarlyLeave ? 'Check Out - Early Leave' : 'Check Out - Work Summary'">
        <form wire:submit="checkOut" class="space-y-4">
            {{-- Work Notes dengan Quill --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                    What did you work on today? *
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    Describe your tasks and achievements (minimum 10 characters)
                </p>

                <div x-data="{
                    quill: null,
                    init() {
                        this.quill = new Quill(this.$refs.editor, {
                            theme: 'snow',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                                ]
                            },
                            placeholder: 'Example:\n• Meeting with marketing team\n• Completed monthly report\n• Reviewed client proposal'
                        });
                
                        this.quill.on('text-change', () => {
                            $wire.set('notes', this.quill.root.innerHTML);
                        });
                
                        if ($wire.notes) {
                            this.quill.root.innerHTML = $wire.notes;
                        }
                    }
                }" wire:ignore>
                    <div x-ref="editor" style="min-height: 150px;"></div>
                </div>
                @error('notes')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Early Leave Reason --}}
            @if ($this->isEarlyLeave)
                <div
                    class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <x-icon name="exclamation-triangle" class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                        <div>
                            <h4 class="font-medium text-orange-900 dark:text-orange-100">Early Leave Detected</h4>
                            <p class="text-sm text-orange-700 dark:text-orange-300">
                                You are checking out before work hours end. Please provide a reason.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                        Early Leave Reason *
                    </label>

                    <div x-data="{
                        quill: null,
                        init() {
                            this.quill = new Quill(this.$refs.editor, {
                                theme: 'snow',
                                modules: {
                                    toolbar: [
                                        ['bold', 'italic', 'underline'],
                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                                    ]
                                },
                                placeholder: 'Example:\n• Urgent family matter\n• Doctor appointment'
                            });
                    
                            this.quill.on('text-change', () => {
                                $wire.set('early_leave_reason', this.quill.root.innerHTML);
                            });
                    
                            if ($wire.early_leave_reason) {
                                this.quill.root.innerHTML = $wire.early_leave_reason;
                            }
                        }
                    }" wire:ignore>
                        <div x-ref="editor" style="min-height: 120px;"></div>
                    </div>
                    @error('early_leave_reason')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Info --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                <div class="flex items-start space-x-2">
                    <x-icon name="information-circle" class="w-4 h-4 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium">Information:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>This note will be visible to your supervisor</li>
                            <li>Working hours: {{ $this->todayAttendance?->check_in->format('H:i') ?? '--:--' }} -
                                {{ now()->format('H:i') }}</li>
                            <li>Total:
                                {{ $this->todayAttendance ? round($this->todayAttendance->check_in->diffInMinutes(now()) / 60, 1) : 0 }}h
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-2">
                <x-button color="gray" wire:click="closeNotesModal">Cancel</x-button>
                <x-button color="blue" wire:click="checkOut" loading="checkOut">
                    <x-icon name="check" class="w-4 h-4 mr-2" />
                    Confirm Check Out
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>

{{-- Quill CSS & JS --}}
@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
@endpush
