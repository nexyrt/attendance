<div>
    {{-- Header Section --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Attendance</h1>
        <p class="text-gray-600 dark:text-gray-400">Monitor your team's attendance and performance</p>
    </div>

    {{-- Monthly Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <x-icon name="users" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Attendances</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->monthlyStats['total_attendances'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                    <x-icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Attendance Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->monthlyStats['attendance_rate'] }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <x-icon name="clock" class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Punctuality Rate</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->monthlyStats['punctuality_rate'] }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <x-icon name="clock" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Hours</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->monthlyStats['total_working_hours'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Team Performance Overview --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-6 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Team Performance Overview</h3>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Performance Table --}}
            <div>
                <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Individual Performance</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Member</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Rate</th>
                                <th
                                    class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                    Hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($this->teamPerformance as $performance)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ $performance['user']->name }}</td>
                                    <td class="px-4 py-2">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if ($performance['attendance_rate'] >= 90) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @elseif($performance['attendance_rate'] >= 80) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                                            {{ $performance['attendance_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-white">
                                        {{ $performance['total_hours'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div>
                <h4 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-3">Quick Actions</h4>
                <div class="space-y-3">
                    <livewire:manager.team-attendance.analytics />
                    <livewire:manager.team-attendance.export />
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance Records</h3>
                <div class="flex gap-2">
                    <x-button.circle icon="chart-bar" wire:click="$dispatch('show-analytics')" />
                    <x-button.circle icon="arrow-down-tray" wire:click="$dispatch('show-export')" />
                </div>
            </div>
        </div>

        <x-table :headers="$headers" :rows="$this->rows" :sort="$sort" paginate filter loading>
            {{-- Custom filters --}}
            <x-slot:header>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <x-select.styled label="Team Member" :options="$this->getMemberOptions()" wire:model.live="member" />

                    <x-date label="Date Range" wire:model.live="dateRange" range format="YYYY-MM-DD" />

                    <x-input type="month" label="Month" wire:model.live="month" />

                    <x-select.styled label="Status" :options="$this->getStatusOptions()" wire:model.live="status" />
                </div>
            </x-slot:header>

            {{-- Table interactions --}}
            @interact('column_user', $row)
                <div class="flex items-center">
                    @if ($row->user && $row->user->image && \Storage::disk('public')->exists($row->user->image))
                        <img class="w-8 h-8 rounded-full mr-3" src="{{ Storage::url($row->user->image) }}"
                            alt="{{ $row->user->name }}">
                    @else
                        <div
                            class="w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full mr-3 flex items-center justify-center">
                            <span
                                class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ $row->user ? substr($row->user->name, 0, 2) : 'N/A' }}</span>
                        </div>
                    @endif
                    <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $row->user->name ?? 'Unknown' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $row->user->email ?? 'No email' }}</div>
                    </div>
                </div>
            @endinteract

            @interact('column_date', $row)
                <div class="text-sm text-gray-900 dark:text-white">
                    {{ $row->date->format('M d, Y') }}
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row->date->format('l') }}</div>
                </div>
            @endinteract

            @interact('column_check_in', $row)
                @if ($row->check_in)
                    <span class="text-sm text-gray-900 dark:text-white">{{ $row->check_in->format('H:i') }}</span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_check_out', $row)
                @if ($row->check_out)
                    <span class="text-sm text-gray-900 dark:text-white">{{ $row->check_out->format('H:i') }}</span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_working_hours', $row)
                @if ($row->working_hours)
                    <span class="text-sm text-gray-900 dark:text-white">{{ number_format($row->working_hours, 2) }}h</span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_late_hours', $row)
                @if ($row->late_hours)
                    <span class="text-sm text-red-600 dark:text-red-400">{{ number_format($row->late_hours, 2) }}h</span>
                @else
                    <span class="text-xs text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_status', $row)
                <x-badge :text="ucfirst($row->status)" :color="match ($row->status) {
                    'present' => 'green',
                    'late' => 'yellow',
                    'early_leave' => 'orange',
                    'holiday' => 'blue',
                    default => 'gray',
                }" />
            @endinteract

            @interact('column_office', $row)
                <div class="text-sm">
                    @if ($row->checkInOffice)
                        <div class="text-gray-900 dark:text-white">{{ $row->checkInOffice->name }}</div>
                    @endif
                    @if ($row->checkOutOffice && $row->checkOutOffice->id !== $row->checkInOffice?->id)
                        <div class="text-xs text-gray-500 dark:text-gray-400">Out: {{ $row->checkOutOffice->name }}</div>
                    @endif
                </div>
            @endinteract
        </x-table>
    </div>
</div>
