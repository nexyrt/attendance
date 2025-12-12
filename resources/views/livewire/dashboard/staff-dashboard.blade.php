<div class="space-y-4 md:space-y-6">

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

            {{-- CHECK-IN COMPONENT INTEGRATION --}}
            <livewire:attendance.check-in />
        </div>

        {{-- CENTER COLUMN - Calendar --}}
        <div class="lg:col-span-4 space-y-5">
            @include('livewire.dashboard.partials.calendar-component')

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
</div>
