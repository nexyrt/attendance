{{-- Attendance Summary Card --}}
<div class="rounded-2xl bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 overflow-hidden">
    <div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-semibold text-dark-900 dark:text-dark-50 text-lg">Ringkasan Kehadiran</h3>
                <p class="text-sm text-dark-500 dark:text-dark-400">
                    {{ now()->isoFormat('MMMM YYYY') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold text-dark-900 dark:text-dark-50">{{ $this->attendanceRate }}%</p>
                <p class="text-xs text-dark-500 dark:text-dark-400">Tingkat Kehadiran</p>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="relative h-3 rounded-full bg-dark-100 dark:bg-dark-700 overflow-hidden mb-6">
            @php
                $summary = $this->attendanceSummary;
                $presentPercent = $summary['workingDays'] > 0 ? ($summary['present'] / $summary['workingDays']) * 100 : 0;
                $latePercent = $summary['workingDays'] > 0 ? ($summary['late'] / $summary['workingDays']) * 100 : 0;
                $leavePercent = $summary['workingDays'] > 0 ? ($summary['leave'] / $summary['workingDays']) * 100 : 0;
            @endphp
            
            {{-- Present --}}
            <div class="absolute left-0 top-0 h-full bg-green-500 rounded-full transition-all duration-500"
                 style="width: {{ $presentPercent }}%"></div>
            
            {{-- Late --}}
            <div class="absolute top-0 h-full bg-yellow-500 rounded-full transition-all duration-500"
                 style="left: {{ $presentPercent }}%; width: {{ $latePercent }}%"></div>
            
            {{-- Leave --}}
            <div class="absolute top-0 h-full bg-blue-500 rounded-full transition-all duration-500"
                 style="left: {{ $presentPercent + $latePercent }}%; width: {{ $leavePercent }}%"></div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-3">
            {{-- Hadir --}}
            <div class="rounded-xl p-4 bg-green-50 dark:bg-green-900/10 transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="check-circle" class="h-4 w-4 text-green-600 dark:text-green-400" />
                    <span class="text-sm text-dark-600 dark:text-dark-400">Hadir</span>
                </div>
                <p class="text-2xl font-bold text-dark-900 dark:text-dark-50">{{ $summary['present'] }}</p>
                <p class="text-xs text-dark-500 dark:text-dark-400">dari {{ $summary['workingDays'] }} hari kerja</p>
            </div>

            {{-- Terlambat --}}
            <div class="rounded-xl p-4 bg-yellow-50 dark:bg-yellow-900/10 transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="exclamation-circle" class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                    <span class="text-sm text-dark-600 dark:text-dark-400">Terlambat</span>
                </div>
                <p class="text-2xl font-bold text-dark-900 dark:text-dark-50">{{ $summary['late'] }}</p>
                <p class="text-xs text-dark-500 dark:text-dark-400">dari {{ $summary['workingDays'] }} hari kerja</p>
            </div>

            {{-- Cuti/Izin --}}
            <div class="rounded-xl p-4 bg-blue-50 dark:bg-blue-900/10 transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="calendar" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span class="text-sm text-dark-600 dark:text-dark-400">Cuti/Izin</span>
                </div>
                <p class="text-2xl font-bold text-dark-900 dark:text-dark-50">{{ $summary['leave'] }}</p>
                <p class="text-xs text-dark-500 dark:text-dark-400">dari {{ $summary['workingDays'] }} hari kerja</p>
            </div>

            {{-- Tidak Hadir --}}
            <div class="rounded-xl p-4 bg-red-50 dark:bg-red-900/10 transition-colors">
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="x-circle" class="h-4 w-4 text-red-600 dark:text-red-400" />
                    <span class="text-sm text-dark-600 dark:text-dark-400">Tidak Hadir</span>
                </div>
                <p class="text-2xl font-bold text-dark-900 dark:text-dark-50">{{ $summary['absent'] }}</p>
                <p class="text-xs text-dark-500 dark:text-dark-400">dari {{ $summary['workingDays'] }} hari kerja</p>
            </div>
        </div>
    </div>
</div>