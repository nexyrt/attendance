<div>
    <x-button icon="chart-bar" wire:click="$toggle('modal')" color="blue" outline>
        View Analytics
    </x-button>

    <x-modal title="Team Attendance Analytics" wire size="4xl">
        {{-- Period Selector --}}
        <div class="mb-6">
            <div class="flex gap-4 items-end">
                <x-select.styled label="Period" :options="$this->getPeriodOptions()" wire:model.live="period" />

                @if ($period === 'month')
                    <x-input type="month" label="Month" wire:model.live="month" />
                @endif
            </div>
        </div>

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            {{-- Attendance Chart --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-lg font-semibold mb-4">Attendance Overview</h4>
                <div class="h-64" id="attendance-chart" wire:ignore>
                    <canvas id="attendanceCanvas"></canvas>
                </div>
            </div>

            {{-- Trends Chart --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-lg font-semibold mb-4">Daily Trends</h4>
                <div class="h-64" id="trends-chart" wire:ignore>
                    <canvas id="trendsCanvas"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="mb-6">
            <h4 class="text-lg font-semibold mb-4">Top Performers</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($this->topPerformers as $index => $performer)
                        <div class="bg-white rounded-lg p-4 shadow-sm">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="font-medium text-gray-900">{{ $performer['user']->name }}</h5>
                                <x-badge :text="'#' . ($index + 1)" color="blue" />
                            </div>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Attendance Rate:</span>
                                    <span class="font-medium text-green-600">{{ $performer['attendance_rate'] }}%</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total Hours:</span>
                                    <span class="font-medium">{{ $performer['total_hours'] }}h</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Avg Daily:</span>
                                    <span class="font-medium">{{ $performer['avg_daily_hours'] }}h</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Late Arrivals --}}
        <div class="mb-6">
            <h4 class="text-lg font-semibold mb-4">Recent Late Arrivals</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                @if (count($this->lateArrivals) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Check In
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Late
                                        Hours</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($this->lateArrivals as $late)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $late['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($late['date'])->format('M d, Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($late['check_in'])->format('H:i') }}</td>
                                        <td class="px-4 py-2">
                                            <x-badge :text="number_format($late['late_hours'], 2) . 'h'" color="red" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No late arrivals in this period!</p>
                @endif
            </div>
        </div>

        {{-- Chart Scripts --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Attendance Chart
                const attendanceCtx = document.getElementById('attendanceCanvas');
                if (attendanceCtx) {
                    new Chart(attendanceCtx, {
                        type: 'bar',
                        data: @json($this->chartData),
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                }

                // Trends Chart
                const trendsCtx = document.getElementById('trendsCanvas');
                if (trendsCtx) {
                    const trendsData = @json($this->attendanceTrends);
                    new Chart(trendsCtx, {
                        type: 'line',
                        data: {
                            labels: trendsData.map(item => item.date),
                            datasets: [{
                                label: 'Attendance Rate %',
                                data: trendsData.map(item => item.attendance_rate),
                                borderColor: '#3B82F6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }
            });
        </script>
    </x-modal>
</div>

{{-- Include Chart.js if not already included --}}
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
@endpush
