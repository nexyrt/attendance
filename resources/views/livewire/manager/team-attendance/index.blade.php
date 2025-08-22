<div class="space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Attendance</h1>
        <p class="text-gray-600 dark:text-gray-400">Monitor your team's daily attendance</p>
    </div>

    {{-- Date Range & Filter Controls --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-date label="Date Range" 
                    wire:model.blur="dateRange" 
                    range 
                    format="YYYY-MM-DD" />
            <x-input label="Search Staff" 
                     wire:model.live.debounce.500ms="search"
                     placeholder="Search by name or email..." 
                     icon="magnifying-glass" />
            <x-select.styled label="Filter by Status"
                             wire:model.live="statusFilter"
                             placeholder="All Status"
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
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Staff</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $attendanceStats['total_staff'] }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Present</p>
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $attendanceStats['present'] }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Late</p>
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $attendanceStats['late'] }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
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
    <div class="space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Team Members</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Click column headers to sort</p>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Showing attendance data for selected period
            </div>
        </div>

        <x-table :$headers :$sort :rows="$this->rows" :quantity="[5,10,15,20]" filter paginate loading>
            @interact('column_name', $row)
                <div class="flex items-center space-x-3">
                    @if($row->image)
                        <img src="{{ Storage::url($row->image) }}" alt="{{ $row->name }}" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 dark:border-gray-600">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-2 border-gray-200 dark:border-gray-600">
                            <span class="text-sm font-bold text-white">
                                {{ substr($row->name, 0, 2) }}
                            </span>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $row->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $row->email }}</p>
                    </div>
                </div>
            @endinteract

            @interact('column_email', $row)
                <div class="text-sm">
                    <span class="text-gray-600 dark:text-gray-400">{{ $row->email }}</span>
                    <div class="text-xs text-gray-400 mt-1">
                        ID: #{{ $row->id }}
                    </div>
                </div>
            @endinteract

            @interact('column_latest_date', $row)
                @php
                    $latestAttendance = $row->attendances->first();
                @endphp
                @if($latestAttendance)
                    <div class="text-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $latestAttendance->date->format('M d') }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ $latestAttendance->date->format('Y') }}
                        </div>
                        <div class="text-xs text-blue-500 mt-1">
                            {{ $latestAttendance->date->diffForHumans() }}
                        </div>
                    </div>
                @else
                    <div class="text-center">
                        <span class="text-sm text-gray-400">No data</span>
                    </div>
                @endif
            @endinteract

            @interact('column_check_in', $row)
                @php
                    $latestAttendance = $row->attendances->first();
                @endphp
                @if($latestAttendance && $latestAttendance->check_in)
                    <div class="text-center">
                        <div class="inline-flex items-center px-2 py-1 rounded-md bg-green-50 dark:bg-green-900/20">
                            <svg class="w-3 h-3 text-green-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            </svg>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400">
                                {{ $latestAttendance->check_in->format('H:i') }}
                            </span>
                        </div>
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
                @if($latestAttendance && $latestAttendance->check_out)
                    <div class="text-center">
                        <div class="inline-flex items-center px-2 py-1 rounded-md bg-red-50 dark:bg-red-900/20">
                            <svg class="w-3 h-3 text-red-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/>
                            </svg>
                            <span class="text-sm font-medium text-red-700 dark:text-red-400">
                                {{ $latestAttendance->check_out->format('H:i') }}
                            </span>
                        </div>
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
                @if($avgHours)
                    <div class="text-center">
                        <div class="inline-flex items-center px-2 py-1 rounded-md bg-blue-50 dark:bg-blue-900/20">
                            <svg class="w-3 h-3 text-blue-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                            </svg>
                            <span class="text-sm font-medium text-blue-700 dark:text-blue-400">
                                {{ number_format($avgHours, 1) }}h
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">average</div>
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
                    
                    $statusConfig = [
                        'present' => ['icon' => 'check-circle', 'bg' => 'bg-green-100 dark:bg-green-900/20', 'text' => 'text-green-800 dark:text-green-400'],
                        'late' => ['icon' => 'clock', 'bg' => 'bg-yellow-100 dark:bg-yellow-900/20', 'text' => 'text-yellow-800 dark:text-yellow-400'],
                        'early_leave' => ['icon' => 'arrow-right-on-rectangle', 'bg' => 'bg-orange-100 dark:bg-orange-900/20', 'text' => 'text-orange-800 dark:text-orange-400'],
                        'absent' => ['icon' => 'x-circle', 'bg' => 'bg-red-100 dark:bg-red-900/20', 'text' => 'text-red-800 dark:text-red-400'],
                        'holiday' => ['icon' => 'calendar', 'bg' => 'bg-purple-100 dark:bg-purple-900/20', 'text' => 'text-purple-800 dark:text-purple-400']
                    ];
                    
                    $config = $statusConfig[$status] ?? $statusConfig['absent'];
                @endphp
                <div class="flex justify-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }}">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            @if($config['icon'] === 'check-circle')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                            @elseif($config['icon'] === 'clock')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                            @elseif($config['icon'] === 'x-circle')
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            @else
                                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z"/>
                            @endif
                        </svg>
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </div>
                </div>
            @endinteract

            @interact('column_notes', $row)
                @php
                    $latestAttendance = $row->attendances->first();
                @endphp
                @if($latestAttendance && $latestAttendance->notes)
                    <div class="max-w-xs">
                        <p class="text-sm text-gray-600 dark:text-gray-400 truncate" title="{{ $latestAttendance->notes }}">
                            {{ Str::limit($latestAttendance->notes, 30) }}
                        </p>
                        @if(strlen($latestAttendance->notes) > 30)
                            <button class="text-xs text-blue-500 hover:text-blue-700 mt-1" onclick="alert('{{ addslashes($latestAttendance->notes) }}')">
                                Read more
                            </button>
                        @endif
                    </div>
                @else
                    <span class="text-sm text-gray-400">No notes</span>
                @endif
            @endinteract
        </x-table>
    </div>
</div>