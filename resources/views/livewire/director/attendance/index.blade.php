<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Kehadiran Karyawan</h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm">Monitor kehadiran semua departemen</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Kehadiran --}}
        <x-card
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-blue-200 dark:border-blue-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Total Kehadiran</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $this->stats['total'] }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-500 dark:bg-blue-600 rounded-full flex items-center justify-center">
                    <x-icon name="users" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        {{-- Hadir Tepat Waktu --}}
        <x-card
            class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-green-200 dark:border-green-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-green-600 dark:text-green-400">Tepat Waktu</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $this->stats['present'] }}</p>
                </div>
                <div class="h-12 w-12 bg-green-500 dark:bg-green-600 rounded-full flex items-center justify-center">
                    <x-icon name="check-circle" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        {{-- Terlambat --}}
        <x-card
            class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 border-yellow-200 dark:border-yellow-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Terlambat</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $this->stats['late'] }}</p>
                </div>
                <div class="h-12 w-12 bg-yellow-500 dark:bg-yellow-600 rounded-full flex items-center justify-center">
                    <x-icon name="clock" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>

        {{-- Pulang Cepat --}}
        <x-card
            class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border-orange-200 dark:border-orange-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-orange-600 dark:text-orange-400">Pulang Cepat</p>
                    <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">{{ $this->stats['early_leave'] }}
                    </p>
                </div>
                <div class="h-12 w-12 bg-orange-500 dark:bg-orange-600 rounded-full flex items-center justify-center">
                    <x-icon name="arrow-right-on-rectangle" class="w-6 h-6 text-white" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Date Range Filter --}}
            <div>
                <x-date label="Periode Tanggal" wire:model.live="date_range" range :max-date="now()" helpers />
            </div>

            {{-- Department Filter --}}
            <div>
                <x-select.styled label="Filter Departemen" placeholder="Semua Departemen"
                    wire:model.live="department_filter" :options="$this->departments
                        ->map(
                            fn($dept) => [
                                'label' => $dept->name,
                                'value' => $dept->id,
                            ],
                        )
                        ->toArray()" />
            </div>

            {{-- Status Filter --}}
            <div>
                <x-select.native label="Filter Status" wire:model.live="status_filter">
                    <option value="">Semua Status</option>
                    <option value="present">Tepat Waktu</option>
                    <option value="late">Terlambat</option>
                    <option value="early_leave">Pulang Cepat</option>
                    <option value="holiday">Libur</option>
                </x-select.native>
            </div>
        </div>
    </x-card>

    {{-- Attendance Table --}}
    <x-card>
        <x-table :$headers :$sort :rows="$this->rows" paginate filter loading>
            {{-- User Column --}}
            @interact('column_user', $row)
                @if ($row->user)
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ strtoupper(substr($row->user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white">{{ $row->user->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row->user->email }}</div>
                        </div>
                    </div>
                @else
                    <span class="text-gray-400 italic">User Deleted</span>
                @endif
            @endinteract

            {{-- Department Column --}}
            @interact('column_department', $row)
                @if ($row->user && $row->user->department)
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-3 h-3 rounded-full {{ match ($row->user->department->name) {
                                'Digital Marketing' => 'bg-blue-500',
                                'Sydital' => 'bg-green-500',
                                'Detax' => 'bg-yellow-500',
                                'HR' => 'bg-purple-500',
                                default => 'bg-gray-500',
                            } }}">
                        </div>
                        <span class="text-gray-900 dark:text-white">{{ $row->user->department->name }}</span>
                    </div>
                @else
                    <span class="text-gray-400 italic">-</span>
                @endif
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
                                Terlambat {{ number_format($row->late_hours, 0) }} menit
                            </div>
                        @endif
                    </div>
                @else
                    <span class="text-gray-400 italic">Belum Check In</span>
                @endif
            @endinteract

            {{-- Check Out Column --}}
            @interact('column_check_out', $row)
                @if ($row->check_out)
                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $row->check_out->format('H:i') }}
                    </div>
                @else
                    <span class="text-gray-400 italic">Belum Check Out</span>
                @endif
            @endinteract

            {{-- Working Hours Column --}}
            @interact('column_working_hours', $row)
                @if ($row->working_hours)
                    <div class="text-sm">
                        <x-badge :color="$row->working_hours >= 8 ? 'green' : 'orange'" :text="number_format($row->working_hours, 1) . ' jam'" />
                    </div>
                @else
                    <span class="text-gray-400 italic">-</span>
                @endif
            @endinteract

            {{-- Status Column --}}
            @interact('column_status', $row)
                <x-badge :color="match ($row->status) {
                    'present' => 'green',
                    'late' => 'yellow',
                    'early_leave' => 'orange',
                    'holiday' => 'blue',
                    'pending present' => 'gray',
                    default => 'gray',
                }" :text="match ($row->status) {
                    'present' => 'Tepat Waktu',
                    'late' => 'Terlambat',
                    'early_leave' => 'Pulang Cepat',
                    'holiday' => 'Libur',
                    'pending present' => 'Menunggu',
                    default => ucfirst($row->status),
                }" />
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
        </x-table>
    </x-card>
</div>
