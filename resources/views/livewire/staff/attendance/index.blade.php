<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Attendance History</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Track your attendance and working hours</p>
        </div>
        <x-button href="{{ route('attendance.check-in') }}" color="blue" icon="cursor-arrow-rays" wire:navigate>
            Check In/Out
        </x-button>
    </div>

    {{-- Monthly Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        {{-- Total Days --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Days</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        {{ $this->monthlyStats['total_days'] }}
                    </p>
                </div>
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                    <x-icon name="calendar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
        </div>

        {{-- Present --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Present</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                        {{ $this->monthlyStats['present_days'] }}
                    </p>
                </div>
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                    <x-icon name="check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>

        {{-- Late --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Late</p>
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">
                        {{ $this->monthlyStats['late_days'] }}
                    </p>
                </div>
                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                    <x-icon name="clock" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
            </div>
        </div>

        {{-- Early Leave --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Early Leave</p>
                    <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1">
                        {{ $this->monthlyStats['early_leave_days'] }}
                    </p>
                </div>
                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                    <x-icon name="arrow-right-on-rectangle" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                </div>
            </div>
        </div>

        {{-- Total Hours --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Hours</p>
                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                        {{ number_format($this->monthlyStats['total_hours'], 1) }}h
                    </p>
                </div>
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                    <x-icon name="clock" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
            </div>
        </div>

        {{-- Average Hours --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Avg/Day</p>
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ number_format($this->monthlyStats['avg_hours'], 1) }}h
                    </p>
                </div>
                <div class="p-3 bg-indigo-100 dark:bg-indigo-900 rounded-full">
                    <x-icon name="chart-bar" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
            </div>
        </div>

        {{-- Total Late Hours --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Late Hours</p>
                    <p class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                        {{ number_format($this->monthlyStats['total_late_hours'], 1) }}h
                    </p>
                </div>
                <div class="p-3 bg-rose-100 dark:bg-rose-900 rounded-full">
                    <x-icon name="exclamation-triangle" class="w-6 h-6 text-rose-600 dark:text-rose-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <x-date label="Date Range" wire:model.live="dateRange" range format="YYYY-MM-DD" helpers />
            </div>

            <div>
                <x-select.native label="Status Filter" wire:model.live="status" :options="$this->getStatusOptions()"
                    select="label:label|value:value" />
            </div>

            <div class="flex items-end">
                <x-button color="secondary" wire:click="resetFilters" icon="arrow-path" class="w-full">
                    Reset Filters
                </x-button>
            </div>
        </div>
    </div>

    {{-- Attendance Table --}}
    <x-table :$headers :$sort :rows="$this->rows" paginate filter loading :quantity="[5, 10, 15, 25]" striped>
        @interact('column_date', $row)
            <div class="py-1">
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ $row->date->format('D, M j, Y') }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $row->date->diffForHumans() }}
                </div>
            </div>
        @endinteract

        @interact('column_check_in', $row)
            <div class="py-1">
                @if ($row->check_in)
                    <div class="flex items-center gap-2">
                        <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 text-green-500" />
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $row->check_in->format('H:i') }}
                        </span>
                    </div>
                    @if ($row->late_hours > 0)
                        <div class="flex items-center gap-1 mt-1">
                            <x-icon name="clock" class="w-3 h-3 text-red-500" />
                            <span class="text-xs text-red-600 dark:text-red-400">
                                Late: {{ number_format($row->late_hours, 1) }}h
                            </span>
                        </div>
                    @endif
                @else
                    <span class="text-gray-400 text-sm">Not checked in</span>
                @endif
            </div>
        @endinteract

        @interact('column_check_out', $row)
            <div class="py-1">
                @if ($row->check_out)
                    <div class="flex items-center gap-2">
                        <x-icon name="arrow-left-on-rectangle" class="w-4 h-4 text-blue-500" />
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $row->check_out->format('H:i') }}
                        </span>
                    </div>
                @else
                    <span class="text-gray-400 text-sm">Not checked out</span>
                @endif
            </div>
        @endinteract

        @interact('column_working_hours', $row)
            @if ($row->working_hours)
                <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-900">
                    <x-icon name="clock" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                    <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">
                        {{ number_format($row->working_hours, 1) }}h
                    </span>
                </div>
            @else
                <span class="text-gray-400 text-sm">-</span>
            @endif
        @endinteract

        @interact('column_status', $row)
            <x-badge :color="match ($row->status) {
                'present' => 'green',
                'late' => 'red',
                'early_leave' => 'orange',
                'holiday' => 'blue',
                'pending present' => 'yellow',
                default => 'gray',
            }" :text="ucfirst(str_replace('_', ' ', $row->status))" />
        @endinteract

        @interact('column_office', $row)
            <div class="text-sm space-y-1">
                @if ($row->checkInOffice)
                    <div class="flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                        <x-icon name="map-pin" class="w-4 h-4 text-green-500" />
                        <span>{{ $row->checkInOffice->name }}</span>
                    </div>
                @endif
                @if ($row->checkOutOffice && $row->checkOutOffice->id !== $row->checkInOffice?->id)
                    <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                        <x-icon name="map-pin" class="w-4 h-4 text-blue-500" />
                        <span>{{ $row->checkOutOffice->name }}</span>
                    </div>
                @endif
                @if (!$row->checkInOffice && !$row->checkOutOffice)
                    <span class="text-gray-400">No location</span>
                @endif
            </div>
        @endinteract

        @interact('column_action', $row)
            @if ($row->notes || $row->early_leave_reason)
                <x-button.circle icon="document-text" color="secondary"
                    wire:click="viewNotes({{ $row->id }})" sm />
            @endif
        @endinteract
    </x-table>

    {{-- Notes Modal --}}
    <x-modal wire center :title="$selectedAttendanceId
        ? 'Attendance Details - ' . $this->selectedAttendance?->date->format('M j, Y')
        : 'Attendance Details'" size="2xl">
        @if ($selectedAttendanceId && $this->selectedAttendance)
            <div class="space-y-4">
                {{-- Basic Info --}}
                <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Date</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $this->selectedAttendance->date->format('l, F j, Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Status</p>
                        <x-badge :color="match ($this->selectedAttendance->status) {
                            'present' => 'green',
                            'late' => 'red',
                            'early_leave' => 'orange',
                            'holiday' => 'blue',
                            default => 'gray',
                        }" :text="ucfirst(str_replace('_', ' ', $this->selectedAttendance->status))" />
                    </div>
                </div>

                {{-- Time Info --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">Check In</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->selectedAttendance->check_in?->format('H:i') ?? '-' }}
                        </p>
                    </div>
                    <div class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">Check Out</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $this->selectedAttendance->check_out?->format('H:i') ?? '-' }}
                        </p>
                    </div>
                    <div class="p-3 border border-gray-200 dark:border-gray-600 rounded-lg">
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">Working Hours</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ number_format($this->selectedAttendance->working_hours ?? 0, 1) }}h
                        </p>
                    </div>
                </div>

                {{-- Location Info --}}
                @if ($this->selectedAttendance->checkInOffice || $this->selectedAttendance->checkOutOffice)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <x-icon name="map-pin" class="w-4 h-4" />
                            Location
                        </h4>
                        <div class="space-y-2 text-sm">
                            @if ($this->selectedAttendance->checkInOffice)
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Check In:</span>
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ $this->selectedAttendance->checkInOffice->name }}
                                    </span>
                                </div>
                            @endif
                            @if ($this->selectedAttendance->checkOutOffice)
                                <div class="flex items-start gap-2">
                                    <span class="text-gray-500 dark:text-gray-400 min-w-[80px]">Check Out:</span>
                                    <span class="text-gray-900 dark:text-white font-medium">
                                        {{ $this->selectedAttendance->checkOutOffice->name }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Notes --}}
                @if ($this->selectedAttendance->notes)
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <x-icon name="document-text" class="w-4 h-4" />
                            Notes
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {!! $this->selectedAttendance->notes !!}
                        </p>
                    </div>
                @endif

                {{-- Early Leave Reason --}}
                @if ($this->selectedAttendance->early_leave_reason)
                    <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                            <x-icon name="exclamation-circle" class="w-4 h-4" />
                            Early Leave Reason
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {!! $this->selectedAttendance->early_leave_reason !!}
                        </p>
                    </div>
                @endif

                {{-- Device Info --}}
                @if ($this->selectedAttendance->device_type)
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                        Device: {{ $this->selectedAttendance->device_type }}
                    </div>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <x-button color="secondary" wire:click="$set('modal', false)">
                Close
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>
