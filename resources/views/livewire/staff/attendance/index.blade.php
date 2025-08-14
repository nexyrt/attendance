<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Attendance History</h1>
        <x-button href="{{ route('attendance.check-in') }}" color="blue" wire:navigate>
            Check In/Out
        </x-button>
    </div>

    {{-- Monthly Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-card class="text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ $this->monthlyStats['total_days'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Days</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ $this->monthlyStats['present_days'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Present</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                {{ $this->monthlyStats['late_days'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Late</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                {{ number_format($this->monthlyStats['total_hours'], 1) }}h
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Hours</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                {{ number_format($this->monthlyStats['avg_hours'], 1) }}h
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Avg/Day</div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-date label="Search Date" wire:model.live="search" format="YYYY-MM-DD" />
            </div>

            <div>
                <x-select.native label="Status" wire:model.live="status" :options="collect($this->getStatusOptions())
                    ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                    ->values()" />
            </div>

            <div>
                <x-date label="Month Filter" wire:model.live="month" month-year-only format="YYYY-MM" />
            </div>

            <div class="flex items-end">
                <x-button color="gray"
                    wire:click="$set('search', null); $set('status', null); $set('month', '{{ now()->format('Y-m') }}')"
                    class="w-full">
                    Reset Filters
                </x-button>
            </div>
        </div>
    </x-card>

    {{-- Attendance Table --}}
    <x-card>
        <x-table :$headers :$sort :rows="$this->rows" paginate filter loading :quantity="[5, 10, 15, 25]">
            @interact('column_date', $row)
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $row->date->format('M j, Y') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $row->date->format('l') }}
                    </div>
                </div>
            @endinteract

            @interact('column_check_in', $row)
                @if ($row->check_in)
                    <div class="text-gray-900 dark:text-white">
                        {{ $row->check_in->format('H:i') }}
                    </div>
                    @if ($row->late_hours > 0)
                        <div class="text-xs text-red-600 dark:text-red-400">
                            Late: {{ number_format($row->late_hours, 1) }}h
                        </div>
                    @endif
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_check_out', $row)
                @if ($row->check_out)
                    <span class="text-gray-900 dark:text-white">{{ $row->check_out->format('H:i') }}</span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_working_hours', $row)
                @if ($row->working_hours)
                    <span class="font-medium text-gray-900 dark:text-white">
                        {{ number_format($row->working_hours, 1) }}h
                    </span>
                @else
                    <span class="text-gray-400">-</span>
                @endif
            @endinteract

            @interact('column_status', $row)
                <x-badge :color="match ($row->status) {
                    'present' => 'green',
                    'late' => 'red',
                    'early_leave' => 'orange',
                    'holiday' => 'blue',
                    default => 'gray',
                }" :text="ucfirst(str_replace('_', ' ', $row->status))" />
            @endinteract

            @interact('column_office', $row)
                <div>
                    @if ($row->checkInOffice)
                        <div class="text-sm text-gray-900 dark:text-white">
                            📍 {{ $row->checkInOffice->name }}
                        </div>
                    @endif
                    @if ($row->checkOutOffice && $row->checkOutOffice->id !== $row->checkInOffice?->id)
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            🚪 {{ $row->checkOutOffice->name }}
                        </div>
                    @endif
                </div>
            @endinteract
        </x-table>
    </x-card>
</div>
