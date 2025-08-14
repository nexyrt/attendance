<div class="space-y-6">
    {{-- Welcome Section --}}
    <div
        class="bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-600 dark:to-purple-700 text-white p-6 rounded-lg">
        <h1 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-blue-100 dark:text-blue-200 mt-2">{{ now()->format('l, F j, Y') }}</p>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Check In/Out Status --}}
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Today's Status</h3>
                    @if ($this->todayAttendance)
                        @if ($this->todayAttendance->check_in && !$this->todayAttendance->check_out)
                            <p class="text-green-600 dark:text-green-400 font-medium">Checked In</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $this->todayAttendance->check_in->format('H:i') }}</p>
                        @elseif($this->todayAttendance->check_in && $this->todayAttendance->check_out)
                            <p class="text-blue-600 dark:text-blue-400 font-medium">Checked Out</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $this->todayAttendance->check_out->format('H:i') }}</p>
                        @endif
                    @else
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Not Checked In</p>
                    @endif
                </div>
                <div class="text-3xl">
                    @if ($this->todayAttendance?->check_in)
                        @if ($this->todayAttendance->check_out)
                            🏠
                        @else
                            🏢
                        @endif
                    @else
                        ⏰
                    @endif
                </div>
            </div>
        </x-card>

        {{-- Leave Balance --}}
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leave Balance</h3>
                    @if ($this->leaveBalance)
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $this->leaveBalance->remaining_balance }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">of {{ $this->leaveBalance->total_balance }}
                            days</p>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No data</p>
                    @endif
                </div>
                <div class="text-3xl">📅</div>
            </div>
        </x-card>

        {{-- Pending Requests --}}
        <x-card>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Requests</h3>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $this->pendingLeaveRequests }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">awaiting approval</p>
                </div>
                <div class="text-3xl">⏳</div>
            </div>
        </x-card>
    </div>

    {{-- Monthly Statistics --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- This Month Stats --}}
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">This Month Statistics</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Present Days</span>
                    <x-badge color="green" text="{{ $this->currentMonthStats['present_days'] }}" />
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Late Days</span>
                    <x-badge color="red" text="{{ $this->currentMonthStats['late_days'] }}" />
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-300">Total Working Hours</span>
                    <x-badge color="blue"
                        text="{{ number_format($this->currentMonthStats['total_working_hours'], 1) }}h" />
                </div>
                @if ($this->currentMonthStats['total_late_hours'] > 0)
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-300">Total Late Hours</span>
                        <x-badge color="orange"
                            text="{{ number_format($this->currentMonthStats['total_late_hours'], 1) }}h" />
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Recent Leave Requests --}}
        <x-card>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Leave Requests</h3>
                {{-- <x-button size="sm" color="blue" text="View All" /> --}}
            </div>

            @if ($this->recentLeaveRequests->count() > 0)
                <div class="space-y-3">
                    @foreach ($this->recentLeaveRequests as $request)
                        <div
                            class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ ucfirst($request->type) }}
                                    Leave</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $request->start_date->format('M j') }} -
                                    {{ $request->end_date->format('M j, Y') }}
                                </p>
                            </div>
                            <x-badge :color="match ($request->status) {
                                'approved' => 'green',
                                'rejected_manager', 'rejected_hr', 'rejected_director' => 'red',
                                default => 'yellow',
                            }" :text="ucfirst(str_replace(['_', 'pending_'], [' ', ''], $request->status))" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <div class="text-4xl mb-2">📝</div>
                    <p>No leave requests yet</p>
                </div>
            @endif
        </x-card>
    </div>
</div>
