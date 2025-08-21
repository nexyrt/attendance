<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Attendance</h1>
        <p class="text-gray-600 dark:text-gray-400">Monitor your team's daily attendance</p>
    </div>

    {{-- Date Range & Filter Controls --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-date label="Date Range" wire:model.blur="dateRange" range format="YYYY-MM-DD" />
            <x-input label="Search Staff" wire:model.live.debounce.500ms="search" placeholder="Search by name or email..."
                icon="magnifying-glass" />
            <x-select.styled label="Filter by Status" wire:model.live="statusFilter" placeholder="All Status"
                :options="[
                    ['label' => 'Present', 'value' => 'present'],
                    ['label' => 'Late', 'value' => 'late'],
                    ['label' => 'Early Leave', 'value' => 'early_leave'],
                    ['label' => 'Holiday', 'value' => 'holiday'],
                    ['label' => 'Pending Present', 'value' => 'pending present'],
                ]" />
        </div>
    </x-card>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Staff</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['total_staff'] }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Present</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $attendanceStats['present'] }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Late</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $attendanceStats['late'] }}
                    </p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Absent</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $attendanceStats['absent'] }}</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Action Buttons --}}
    <div class="flex gap-3">
        <x-button color="blue" icon="chart-bar" href="{{ route('manager.team-attendance.analytics') }}">
            Analytics
        </x-button>
        <x-button color="green" icon="document-arrow-down" disabled>
            Export Report
            <x-slot:right>
                <x-badge text="Soon" color="yellow" light xs />
            </x-slot:right>
        </x-button>
    </div>

    {{-- Attendance Table --}}
    <x-table :$headers :$sort :rows="$this->rows" :quantity="[5, 10, 15, 20]" filter paginate loading>
        @interact('column_name', $row)
            <div class="flex items-center">
                @if ($row->image)
                    <img src="{{ Storage::url($row->image) }}" alt="{{ $row->name }}" class="w-8 h-8 rounded-full mr-3">
                @else
                    <div class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center mr-3">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-300">
                            {{ substr($row->name, 0, 2) }}
                        </span>
                    </div>
                @endif
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $row->name }}</p>
                </div>
            </div>
        @endinteract

        @interact('column_email', $row)
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $row->email }}</span>
        @endinteract

        @interact('column_latest_date', $row)
            @php
                $latestAttendance = $row->attendances->first();
            @endphp
            @if ($latestAttendance)
                <div class="text-center">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $latestAttendance->date->format('M d') }}
                    </span>
                    <p class="text-xs text-gray-400">{{ $latestAttendance->date->format('Y') }}</p>
                </div>
            @else
                <div class="text-center">
                    <span class="text-sm text-gray-400">-</span>
                </div>
            @endif
        @endinteract

        @interact('column_check_in', $row)
            @php
                $latestAttendance = $row->attendances->first();
            @endphp
            @if ($latestAttendance && $latestAttendance->check_in)
                <div class="text-center">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $latestAttendance->check_in->format('H:i') }}
                    </span>
                </div>
            @else
                <div class="text-center">
                    <span class="text-sm text-gray-400">-</span>
                </div>
            @endif
        @endinteract

        @interact('column_check_out', $row)
            @php
                $latestAttendance = $row->attendances->first();
            @endphp
            @if ($latestAttendance && $latestAttendance->check_out)
                <div class="text-center">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $latestAttendance->check_out->format('H:i') }}
                    </span>
                </div>
            @else
                <div class="text-center">
                    <span class="text-sm text-gray-400">-</span>
                </div>
            @endif
        @endinteract

        @interact('column_avg_hours', $row)
            @php
                $avgHours = $row->attendances->avg('working_hours');
            @endphp
            @if ($avgHours)
                <div class="text-center">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ number_format($avgHours, 1) }}h
                    </span>
                </div>
            @else
                <div class="text-center">
                    <span class="text-sm text-gray-400">-</span>
                </div>
            @endif
        @endinteract

        @interact('column_status', $row)
            @php
                $latestAttendance = $row->attendances->first();
                $status = $latestAttendance ? $latestAttendance->status : 'absent';
                $badgeColor = $this->getStatusBadgeColor($status);
            @endphp
            <div class="flex justify-center">
                <x-badge :text="ucfirst(str_replace('_', ' ', $status))" :color="$badgeColor" light />
            </div>
        @endinteract

        @interact('column_notes', $row)
            @php
                $latestAttendance = $row->attendances->first();
            @endphp
            @if ($latestAttendance && $latestAttendance->notes)
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ Str::limit($latestAttendance->notes, 30) }}
                </span>
            @else
                <span class="text-sm text-gray-400">-</span>
            @endif
        @endinteract
    </x-table>
</div>
