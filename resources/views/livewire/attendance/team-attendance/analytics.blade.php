<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Analytics</h1>
            <p class="text-gray-600 dark:text-gray-400">Comprehensive attendance analytics for your team</p>
        </div>
        <x-button href="{{ route('manager.team-attendance') }}" color="gray" icon="arrow-left">
            Back to Attendance
        </x-button>
    </div>

    {{-- Date Range Filter --}}
    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-48">
                <x-select.styled label="Date Range" wire:model.live="dateRange" :options="[
                    ['label' => 'This Week', 'value' => 'week'],
                    ['label' => 'This Month', 'value' => 'month'],
                    ['label' => 'This Quarter', 'value' => 'quarter'],
                ]" />
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ Carbon\Carbon::parse($startDate)->format('M d') }} -
                {{ Carbon\Carbon::parse($endDate)->format('M d, Y') }}
            </div>
        </div>
    </x-card>

    {{-- Weekly Comparison --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $currentPresent = $weeklyComparison['current']['present'] ?? 0;
            $previousPresent = $weeklyComparison['previous']['present'] ?? 0;
            $currentLate = $weeklyComparison['current']['late'] ?? 0;
            $previousLate = $weeklyComparison['previous']['late'] ?? 0;
        @endphp

        <x-card>
            <div class="text-center">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Present This Week</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $currentPresent }}</p>
                @php $change = $this->getChangePercentage($currentPresent, $previousPresent) @endphp
                <p class="text-xs {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $change >= 0 ? '+' : '' }}{{ $change }}% from last week
                </p>
            </div>
        </x-card>

        <x-card>
            <div class="text-center">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Late This Week</p>
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $currentLate }}</p>
                @php $change = $this->getChangePercentage($currentLate, $previousLate) @endphp
                <p class="text-xs {{ $change <= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $change >= 0 ? '+' : '' }}{{ $change }}% from last week
                </p>
            </div>
        </x-card>

        <x-card>
            <div class="text-center">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Working Hours</p>
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($individualStats->avg('avg_working_hours'), 1) }}h
                </p>
                <p class="text-xs text-gray-500">Daily average</p>
            </div>
        </x-card>

        <x-card>
            <div class="text-center">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Team Attendance Rate</p>
                <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                    {{ number_format($individualStats->avg('attendance_rate'), 1) }}%
                </p>
                <p class="text-xs text-gray-500">Overall team rate</p>
            </div>
        </x-card>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Attendance Trends</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Daily attendance breakdown</p>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="attendanceTrendsChart"></canvas>
            </div>
        </x-card>

        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Distribution</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Overall attendance status breakdown</p>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="statusDistributionChart"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Punctuality & Working Hours --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Punctuality Analysis</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Top late arrivals and early leavers</p>
            </div>

            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Most Late Arrivals</h4>
                    @forelse($punctualityStats['late_arrivals'] as $name => $count)
                        <div class="flex justify-between items-center py-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $name }}</span>
                            <x-badge text="{{ $count }} times" color="yellow" light xs />
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No late arrivals</p>
                    @endforelse
                </div>

                <hr class="border-gray-200 dark:border-gray-600">

                <div>
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Most Early Leavers</h4>
                    @forelse($punctualityStats['early_leavers'] as $name => $count)
                        <div class="flex justify-between items-center py-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $name }}</span>
                            <x-badge text="{{ $count }} times" color="orange" light xs />
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No early leavers</p>
                    @endforelse
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Average Working Hours</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Team working hours comparison</p>
            </div>
            <div class="h-64" wire:ignore>
                <canvas id="workingHoursChart"></canvas>
            </div>
        </x-card>
    </div>

    {{-- Individual Performance Table --}}
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Individual Performance</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Detailed breakdown per team member</p>
        </div>

        <x-table :headers="$performanceHeaders" :rows="collect($individualStats)">
            @interact('column_name', $row)
                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
            @endinteract

            @interact('column_total_days', $row)
                <div class="text-center">
                    <span class="text-sm text-gray-900 dark:text-white">{{ $row['total_days'] }}</span>
                </div>
            @endinteract

            @interact('column_present_days', $row)
                <div class="flex justify-center">
                    <x-badge text="{{ $row['present_days'] }}" color="green" light xs />
                </div>
            @endinteract

            @interact('column_late_days', $row)
                <div class="flex justify-center">
                    <x-badge text="{{ $row['late_days'] }}" color="yellow" light xs />
                </div>
            @endinteract

            @interact('column_early_leave_days', $row)
                <div class="flex justify-center">
                    <x-badge text="{{ $row['early_leave_days'] }}" color="orange" light xs />
                </div>
            @endinteract

            @interact('column_avg_working_hours', $row)
                <div class="text-center">
                    <span
                        class="text-sm text-gray-900 dark:text-white">{{ number_format($row['avg_working_hours'], 1) }}h</span>
                </div>
            @endinteract

            @interact('column_attendance_rate', $row)
                @php
                    $rate = $row['attendance_rate'];
                    $color = $rate >= 90 ? 'green' : ($rate >= 80 ? 'yellow' : 'red');
                @endphp
                <div class="flex justify-center">
                    <x-badge text="{{ $rate }}%" :color="$color" light xs />
                </div>
            @endinteract
        </x-table>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let attendanceTrendsChart, statusDistributionChart, workingHoursChart;

        function initializeCharts() {
            // Small delay to ensure DOM is ready
            setTimeout(() => {
                // Destroy existing charts
                if (attendanceTrendsChart) {
                    attendanceTrendsChart.destroy();
                    attendanceTrendsChart = null;
                }
                if (statusDistributionChart) {
                    statusDistributionChart.destroy();
                    statusDistributionChart = null;
                }
                if (workingHoursChart) {
                    workingHoursChart.destroy();
                    workingHoursChart = null;
                }

                // Get canvas elements
                const trendsCanvas = document.getElementById('attendanceTrendsChart');
                const distributionCanvas = document.getElementById('statusDistributionChart');
                const hoursCanvas = document.getElementById('workingHoursChart');

                // Check if all canvases exist
                if (!trendsCanvas || !distributionCanvas || !hoursCanvas) {
                    setTimeout(initializeCharts, 200);
                    return;
                }

                // Get data from backend
                const attendanceTrends = @json($attendanceTrends);
                const weeklyComparison = @json($weeklyComparison);
                const individualStats = @json($individualStats);

                // Attendance Trends Chart
                const trendDates = Object.keys(attendanceTrends);
                attendanceTrendsChart = new Chart(trendsCanvas.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: trendDates.map(date => new Date(date).toLocaleDateString()),
                        datasets: [{
                            label: 'Present',
                            data: trendDates.map(date => attendanceTrends[date]['present'] || 0),
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            tension: 0.1
                        }, {
                            label: 'Late',
                            data: trendDates.map(date => attendanceTrends[date]['late'] || 0),
                            borderColor: 'rgb(234, 179, 8)',
                            backgroundColor: 'rgba(234, 179, 8, 0.1)',
                            tension: 0.1
                        }, {
                            label: 'Early Leave',
                            data: trendDates.map(date => attendanceTrends[date]['early_leave'] ||
                                0),
                            borderColor: 'rgb(249, 115, 22)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });

                // Status Distribution Chart
                const currentWeek = weeklyComparison.current;
                statusDistributionChart = new Chart(distributionCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Present', 'Late', 'Early Leave'],
                        datasets: [{
                            data: [
                                currentWeek['present'] || 0,
                                currentWeek['late'] || 0,
                                currentWeek['early_leave'] || 0
                            ],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(234, 179, 8, 0.8)',
                                'rgba(249, 115, 22, 0.8)'
                            ],
                            borderColor: [
                                'rgb(34, 197, 94)',
                                'rgb(234, 179, 8)',
                                'rgb(249, 115, 22)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Working Hours Chart
                workingHoursChart = new Chart(hoursCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: individualStats.map(staff => staff.name.split(' ')[0]),
                        datasets: [{
                            label: 'Average Hours',
                            data: individualStats.map(staff => staff.avg_working_hours),
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10
                            }
                        }
                    }
                });
            }, 100);
        }

        // Initialize charts
        document.addEventListener('DOMContentLoaded', initializeCharts);
        document.addEventListener('livewire:navigated', initializeCharts);
        Livewire.hook('morph.updated', () => {
            setTimeout(initializeCharts, 50);
        });
    </script>
@endpush
