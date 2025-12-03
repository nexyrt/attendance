{{-- Leave Balance Card --}}
<div class="rounded-2xl bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700 overflow-hidden">
    <div class="p-6">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/10 p-2.5">
                    <x-heroicon-o-calendar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-dark-900 dark:text-dark-50">Sisa Cuti</h3>
                    <p class="text-xs text-dark-500 dark:text-dark-400">Tahun
                        {{ $this->leaveBalance?->year ?? now()->year }}</p>
                </div>
            </div>
        </div>

        {{-- Main Balance Display --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-baseline">
                <span class="text-5xl font-bold text-dark-900 dark:text-dark-50">
                    {{ $this->leaveBalance?->remaining_balance ?? 0 }}
                </span>
                <span class="text-lg text-dark-500 dark:text-dark-400 ml-1">hari</span>
            </div>
            <p class="text-sm text-dark-500 dark:text-dark-400 mt-1">
                tersisa dari {{ $this->leaveBalance?->total_balance ?? 12 }} hari
            </p>
        </div>

        {{-- Progress Bar --}}
        @php
            $totalBalance = $this->leaveBalance?->total_balance ?? 12;
            $usedBalance = $this->leaveBalance?->used_balance ?? 0;
            $usedPercentage = $totalBalance > 0 ? ($usedBalance / $totalBalance) * 100 : 0;
        @endphp
        <div class="space-y-2 mb-6">
            <div class="flex justify-between text-xs">
                <span class="text-dark-500 dark:text-dark-400">Terpakai</span>
                <span class="font-medium text-dark-900 dark:text-dark-50">{{ $usedBalance }} hari</span>
            </div>
            <div class="h-2 bg-dark-100 dark:bg-dark-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 transition-all duration-500 rounded-full"
                    style="width: {{ $usedPercentage }}%"></div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-dark-50 dark:bg-dark-700/50 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" />
                    <span class="text-xs text-dark-500 dark:text-dark-400">Jatah Tahunan</span>
                </div>
                <p class="text-lg font-semibold text-dark-900 dark:text-dark-50">{{ $totalBalance }} hari</p>
            </div>
            <div class="rounded-lg bg-dark-50 dark:bg-dark-700/50 p-3">
                <div class="flex items-center gap-2 mb-1">
                    <x-heroicon-o-clock class="h-3.5 w-3.5 text-yellow-600 dark:text-yellow-400" />
                    <span class="text-xs text-dark-500 dark:text-dark-400">Terpakai</span>
                </div>
                <p class="text-lg font-semibold text-dark-900 dark:text-dark-50">{{ $usedBalance }} hari</p>
            </div>
        </div>
    </div>
</div>
