<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1
                class="text-4xl font-bold bg-gradient-to-r from-dark-900 via-primary-800 to-primary-800 dark:from-white dark:via-primary-200 dark:to-primary-200 bg-clip-text text-transparent">
                Dashboard
            </h1>
            <p class="text-dark-600 dark:text-dark-400 text-lg mt-1">
                Selamat datang kembali, {{ auth()->user()->name }}
            </p>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Hari Kerja --}}
        <div
            class="rounded-xl p-5 bg-primary-50 dark:bg-primary-900/10 border border-primary-200 dark:border-primary-800/20 transition-all hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-dark-600 dark:text-dark-400">Hari Kerja Bulan Ini</p>
                    <p class="text-3xl font-bold text-dark-900 dark:text-dark-50">{{ $this->workingDays }}</p>
                    <p class="text-xs text-dark-500 dark:text-dark-500">Total hari kerja</p>
                </div>
                <div class="rounded-lg p-2.5 bg-primary-500">
                    <x-heroicon-o-calendar class="h-5 w-5 text-white" />
                </div>
            </div>
        </div>

        {{-- Total Kehadiran --}}
        <div
            class="rounded-xl p-5 bg-green-50 dark:bg-green-900/10 border border-green-200 dark:border-green-800/20 transition-all hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-dark-600 dark:text-dark-400">Total Kehadiran</p>
                    <p class="text-3xl font-bold text-dark-900 dark:text-dark-50">
                        {{ $this->attendanceSummary['present'] + $this->attendanceSummary['late'] }}
                    </p>
                    <p class="text-xs text-dark-500 dark:text-dark-500">
                        {{ $this->attendanceSummary['late'] }} hari terlambat
                    </p>
                </div>
                <div class="rounded-lg p-2.5 bg-green-500">
                    <x-heroicon-o-clock class="h-5 w-5 text-white" />
                </div>
            </div>
        </div>

        {{-- Sisa Cuti --}}
        <div
            class="rounded-xl p-5 bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/20 transition-all hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-dark-600 dark:text-dark-400">Sisa Cuti</p>
                    <p class="text-3xl font-bold text-dark-900 dark:text-dark-50">
                        {{ $this->leaveBalance?->remaining_balance ?? 0 }}
                    </p>
                    <p class="text-xs text-dark-500 dark:text-dark-500">
                        dari {{ $this->leaveBalance?->total_balance ?? 12 }} hari
                    </p>
                </div>
                <div class="rounded-lg p-2.5 bg-blue-500">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-white" />
                </div>
            </div>
        </div>

        {{-- Tim Hadir --}}
        <div
            class="rounded-xl p-5 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800/20 transition-all hover:shadow-lg">
            <div class="flex items-start justify-between">
                <div class="space-y-1">
                    <p class="text-sm font-medium text-dark-600 dark:text-dark-400">Tim Hadir</p>
                    <p class="text-3xl font-bold text-dark-900 dark:text-dark-50">
                        {{ $this->teamAttendanceToday['present'] }}/{{ $this->teamAttendanceToday['total'] }}
                    </p>
                    <p class="text-xs text-dark-500 dark:text-dark-500">Hari ini</p>
                </div>
                <div class="rounded-lg p-2.5 bg-yellow-500">
                    <x-heroicon-o-users class="h-5 w-5 text-white" />
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - 2/3 width --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Attendance Summary --}}
            @include('livewire.staff.dashboard.partials.attendance-summary')

            {{-- Holiday Calendar --}}
            @include('livewire.staff.dashboard.partials.holiday-calendar')
        </div>

        {{-- Right Column - 1/3 width --}}
        <div class="space-y-6">
            {{-- Clock In Widget --}}
            @include('livewire.staff.dashboard.partials.clock-in-widget')

            {{-- Leave Balance Card --}}
            @include('livewire.staff.dashboard.partials.leave-balance')
        </div>
    </div>
</div>
