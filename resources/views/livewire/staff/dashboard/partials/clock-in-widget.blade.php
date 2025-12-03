{{-- Clock In Widget --}}
<div class="relative overflow-hidden rounded-2xl bg-white dark:bg-dark-800 border border-dark-200 dark:border-dark-700">
    {{-- Gradient Accent --}}
    <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary-400 to-primary-600"></div>

    <div class="p-6">
        {{-- Current Time Display --}}
        <div class="text-center mb-6" x-data="{
            time: new Date(),
            updateTime() {
                this.time = new Date();
            }
        }" x-init="setInterval(() => updateTime(), 1000)">
            <p class="text-sm font-medium text-dark-600 dark:text-dark-400 mb-1"
                x-text="time.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })">
            </p>
            <p class="text-5xl font-bold tracking-tight text-dark-900 dark:text-dark-50">
                <span x-text="time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })"></span>
                <span class="text-2xl text-dark-500 dark:text-dark-400"
                    x-text="':' + String(time.getSeconds()).padStart(2, '0')"></span>
            </p>
        </div>

        {{-- Status Section --}}
        <div class="space-y-4">
            @if ($this->todayAttendance?->check_in && !$this->todayAttendance?->check_out)
                {{-- Checked In --}}
                <div
                    class="bg-green-50 dark:bg-green-900/10 rounded-xl p-4 border border-green-200 dark:border-green-800/20">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400">Sedang Bekerja</span>
                        </div>
                        <span class="text-xs text-dark-600 dark:text-dark-400">
                            Masuk: {{ $this->todayAttendance->check_in->format('H:i') }}
                        </span>
                    </div>

                    <div class="text-center" x-data="{
                        checkInTime: new Date('{{ $this->todayAttendance->check_in }}'),
                        elapsed: 0,
                        updateElapsed() {
                            const diff = Math.floor((new Date() - this.checkInTime) / 1000);
                            this.elapsed = diff;
                        },
                        formatTime(seconds) {
                            const h = Math.floor(seconds / 3600);
                            const m = Math.floor((seconds % 3600) / 60);
                            const s = seconds % 60;
                            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                        }
                    }" x-init="updateElapsed();
                    setInterval(() => updateElapsed(), 1000)">
                        <p class="text-xs text-dark-600 dark:text-dark-400 mb-1">Durasi Kerja</p>
                        <p class="text-3xl font-mono font-bold text-dark-900 dark:text-dark-50"
                            x-text="formatTime(elapsed)"></p>
                    </div>
                </div>
            @elseif($this->todayAttendance?->check_in && $this->todayAttendance?->check_out)
                {{-- Checked Out --}}
                <div class="bg-dark-100 dark:bg-dark-700 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-dark-400"></div>
                            <span class="text-sm font-medium text-dark-600 dark:text-dark-400">Selesai</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div>
                            <p class="text-xs text-dark-600 dark:text-dark-400">Check In</p>
                            <p class="text-lg font-semibold text-dark-900 dark:text-dark-50">
                                {{ $this->todayAttendance->check_in->format('H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-dark-600 dark:text-dark-400">Check Out</p>
                            <p class="text-lg font-semibold text-dark-900 dark:text-dark-50">
                                {{ $this->todayAttendance->check_out->format('H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Not Checked In --}}
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/10 rounded-xl p-4 border border-yellow-200 dark:border-yellow-800/20">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-clock class="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                        <span class="text-sm font-medium text-yellow-700 dark:text-yellow-400">Belum Check In</span>
                    </div>
                    <p class="text-xs text-dark-600 dark:text-dark-400 mt-1">
                        Jam kerja dimulai pukul 09:00
                    </p>
                </div>
            @endif

            {{-- Action Button --}}
            <x-button href="{{ route('attendance.check-in') }}" wire:navigate
                class="w-full h-12 text-base font-semibold" :color="$this->todayAttendance?->check_in && !$this->todayAttendance?->check_out ? 'red' : 'blue'" :disabled="!!$this->todayAttendance?->check_out">
                @if ($this->todayAttendance?->check_in && !$this->todayAttendance?->check_out)
                    <x-heroicon-o-arrow-right-on-rectangle class="mr-2 h-5 w-5" />
                    Check Out
                @else
                    <x-heroicon-o-arrow-left-on-rectangle class="mr-2 h-5 w-5" />
                    Check In
                @endif
            </x-button>

            {{-- Location Indicator --}}
            <div class="flex items-center justify-center gap-2 text-xs text-dark-500 dark:text-dark-400">
                <x-heroicon-o-map-pin class="h-3 w-3" />
                <span>Deteksi lokasi aktif</span>
            </div>
        </div>
    </div>
</div>
