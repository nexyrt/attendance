<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Attendance</h1>
        <p class="text-gray-600 dark:text-gray-400">Monitor your team's daily attendance</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-card
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Staff</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $attendanceStats['total_staff'] }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center">
                    <x-icon name="users" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        <x-card
            class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-purple-200 dark:border-purple-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Total Records</p>
                    <p class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                        {{ $attendanceStats['total_records'] }}</p>
                </div>
                <div class="h-12 w-12 bg-purple-500 dark:bg-purple-600 rounded-full flex items-center justify-center">
                    <x-icon name="clipboard-document-list" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        <x-card
            class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-green-200 dark:border-green-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Present</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $attendanceStats['present'] }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center">
                    <x-icon name="check-circle" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        <x-card
            class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-yellow-200 dark:border-yellow-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Late</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $attendanceStats['late'] }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-yellow-500 dark:bg-yellow-600 rounded-full flex items-center justify-center">
                    <x-icon name="clock" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        <x-card
            class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border-orange-200 dark:border-orange-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Early Leave</p>
                    <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                        {{ $attendanceStats['early_leave'] }}</p>
                </div>
                <div class="h-12 w-12 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center">
                    <x-icon name="arrow-right-on-rectangle" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-date label="Date Range" wire:model.live="dateRange" range format="YYYY-MM-DD" helpers />

            <x-input label="Search Staff" wire:model.live.debounce.500ms="search"
                placeholder="Search by name or email..." icon="magnifying-glass" />

            <x-select.native label="Filter by Status" wire:model.live="statusFilter">
                <option value="">All Status</option>
                <option value="present">Present</option>
                <option value="late">Late</option>
                <option value="early_leave">Early Leave</option>
                <option value="holiday">Holiday</option>
                <option value="pending present">Pending Present</option>
            </x-select.native>
        </div>
    </x-card>

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
    <x-card>
        @if ($this->rows->total() > 0)
            <x-table :$headers :$sort :rows="$this->rows" :quantity="[5, 10, 15, 20]" filter paginate loading>
                {{-- User Column --}}
                @interact('column_user', $row)
                    <div class="flex items-center space-x-3">
                        @if ($row->user->image)
                            <img src="{{ Storage::url($row->user->image) }}" alt="{{ $row->user->name }}"
                                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                        @else
                            <div
                                class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                                <span class="text-sm font-bold text-white">
                                    {{ strtoupper(substr($row->user->name, 0, 2)) }}
                                </span>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $row->user->name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $row->user->email }}</p>
                        </div>
                    </div>
                @endinteract

                {{-- Date Column --}}
                @interact('column_date', $row)
                    <div class="text-sm">
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ $row->date->format('d M Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $row->date->format('l') }}
                        </div>
                    </div>
                @endinteract

                {{-- Check In Column --}}
                @interact('column_check_in', $row)
                    @if ($row->check_in)
                        <div class="text-sm">
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $row->check_in->format('H:i') }}
                            </div>
                            @if ($row->late_hours > 0)
                                <div class="text-xs text-red-500">
                                    Late {{ number_format($row->late_hours * 60, 0) }} min
                                </div>
                            @endif
                        </div>
                    @else
                        <span class="text-gray-400 italic">-</span>
                    @endif
                @endinteract

                {{-- Check Out Column --}}
                @interact('column_check_out', $row)
                    @if ($row->check_out)
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $row->check_out->format('H:i') }}
                        </div>
                    @else
                        <span class="text-gray-400 italic">-</span>
                    @endif
                @endinteract

                {{-- Working Hours Column --}}
                @interact('column_working_hours', $row)
                    @if ($row->working_hours)
                        <x-badge :color="$row->working_hours >= 8 ? 'green' : 'orange'" :text="number_format($row->working_hours, 1) . ' hrs'" />
                    @else
                        <span class="text-gray-400 italic">-</span>
                    @endif
                @endinteract

                {{-- Status Column --}}
                @interact('column_status', $row)
                    <x-badge :color="$this->getStatusBadgeColor($row->status)" :text="ucfirst(str_replace('_', ' ', $row->status))" />
                @endinteract

                {{-- Office Location Column --}}
                @interact('column_office', $row)
                    @if ($row->checkInOffice)
                        <div class="text-sm text-gray-900 dark:text-white">
                            {{ $row->checkInOffice->name }}
                        </div>
                    @else
                        <span class="text-gray-400 italic">-</span>
                    @endif
                @endinteract

                {{-- Notes Column --}}
                @interact('column_notes', $row)
                    @if ($row->notes || $row->early_leave_reason)
                        <div class="flex items-center gap-2">
                            <div class="max-w-xs flex-1">
                                <p class="text-sm text-gray-600 dark:text-gray-400 truncate"
                                    title="{{ $row->notes ?? $row->early_leave_reason }}">
                                    {{ Str::limit($row->notes ?? $row->early_leave_reason, 30) }}
                                </p>
                            </div>
                            <x-button.circle icon="eye" color="blue" sm
                                wire:click="showNotes({{ $row->id }})" />
                        </div>
                    @else
                        <span class="text-sm text-gray-400">No notes</span>
                    @endif
                @endinteract
            </x-table>
        @else
            <div class="text-center py-12">
                <x-icon name="inbox" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">No attendance data</p>
                <p class="text-gray-500 dark:text-gray-500 text-sm mt-2">No attendance records found for selected period
                </p>
            </div>
        @endif
    </x-card>

    {{-- Notes Modal --}}
    <x-modal title="Attendance Details" wire size="lg" center>
        @if ($selectedAttendance)
            <div class="space-y-4">
                {{-- Staff Info --}}
                <div class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    @if ($selectedAttendance->user->image)
                        <img src="{{ Storage::url($selectedAttendance->user->image) }}"
                            alt="{{ $selectedAttendance->user->name }}" class="w-16 h-16 rounded-full object-cover">
                    @else
                        <div
                            class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-xl font-bold text-white">
                                {{ strtoupper(substr($selectedAttendance->user->name, 0, 2)) }}
                            </span>
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $selectedAttendance->user->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $selectedAttendance->user->email }}</p>
                    </div>
                </div>

                {{-- Attendance Details --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Date</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $selectedAttendance->date->format('l, d F Y') }}
                        </p>
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Status</p>
                        <x-badge :text="ucfirst(str_replace('_', ' ', $selectedAttendance->status))" :color="$this->getStatusBadgeColor($selectedAttendance->status)" />
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Check In</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $selectedAttendance->check_in ? $selectedAttendance->check_in->format('H:i:s') : '-' }}
                        </p>
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Check Out</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                            {{ $selectedAttendance->check_out ? $selectedAttendance->check_out->format('H:i:s') : '-' }}
                        </p>
                    </div>

                    @if ($selectedAttendance->working_hours)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Working Hours</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ number_format($selectedAttendance->working_hours, 2) }} hours
                            </p>
                        </div>
                    @endif

                    @if ($selectedAttendance->late_hours)
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Late Hours</p>
                            <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                {{ number_format($selectedAttendance->late_hours, 2) }} hours
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Location Info --}}
                @if ($selectedAttendance->check_in_latitude && $selectedAttendance->check_in_longitude)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2 flex items-center">
                            <x-icon name="map-pin" class="w-4 h-4 mr-2" />
                            Check-in Location
                        </h4>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Latitude:</span>
                                <span
                                    class="text-gray-900 dark:text-white font-mono ml-1">{{ $selectedAttendance->check_in_latitude }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400">Longitude:</span>
                                <span
                                    class="text-gray-900 dark:text-white font-mono ml-1">{{ $selectedAttendance->check_in_longitude }}</span>
                            </div>
                        </div>
                        @if ($selectedAttendance->checkInOffice)
                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                                Office: <span
                                    class="font-semibold">{{ $selectedAttendance->checkInOffice->name }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Early Leave Reason --}}
                @if ($selectedAttendance->early_leave_reason)
                    <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-orange-900 dark:text-orange-300 mb-2 flex items-center">
                            <x-icon name="exclamation-triangle" class="w-4 h-4 mr-2" />
                            Early Leave Reason
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                            {{ $selectedAttendance->early_leave_reason }}</p>
                    </div>
                @endif

                {{-- General Notes --}}
                @if ($selectedAttendance->notes)
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center">
                            <x-icon name="document-text" class="w-4 h-4 mr-2" />
                            Notes
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                            {{ $selectedAttendance->notes }}</p>
                    </div>
                @endif

                {{-- Device Info --}}
                @if ($selectedAttendance->device_type)
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        Recorded from: <span
                            class="font-semibold">{{ ucfirst($selectedAttendance->device_type) }}</span>
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-end">
                <x-button color="secondary" wire:click="$toggle('modal')">
                    Close
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
