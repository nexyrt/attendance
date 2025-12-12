<div x-data="{
    currentTime: '{{ now()->format('H:i:s') }}',
    status: '{{ $this->todayAttendance?->check_in && !$this->todayAttendance?->check_out ? 'checked_in' : ($this->todayAttendance?->check_out ? 'completed' : 'not_started') }}',
    checkInTime: {{ $check_in_time ? "'" . $check_in_time . "'" : 'null' }},
    checkOutTime: {{ $check_out_time ? "'" . $check_out_time . "'" : 'null' }},
    workingHours: '00:00:00',
    workingHoursInterval: null,
    totalWorkingHours: {{ $working_hours ?? 'null' }},
    hasCheckedOut: {{ $has_checked_out ? 'true' : 'false' }},

    updateClock() {
        setInterval(() => {
            const now = new Date();
            this.currentTime = now.toLocaleTimeString('en-US', { hour12: false });
        }, 1000);
    },

    updateWorkingHours() {
        // Jika sudah check-out, hitung dari selisih waktu
        if (this.hasCheckedOut && this.checkInTime && this.checkOutTime) {
            const checkIn = new Date(this.checkInTime);
            const checkOut = new Date(this.checkOutTime);
            const diffInSeconds = Math.floor((checkOut - checkIn) / 1000);
            this.workingHours = this.formatTimeFromSeconds(diffInSeconds);
            return;
        }

        // Jika sudah check-in tapi belum check-out, hitung real-time
        if (this.checkInTime && !this.checkOutTime) {
            this.workingHoursInterval = setInterval(() => {
                const checkIn = new Date(this.checkInTime);
                const now = new Date();
                const diffInSeconds = Math.floor((now - checkIn) / 1000);
                this.workingHours = this.formatTimeFromSeconds(diffInSeconds);
            }, 1000);
        } else {
            this.workingHours = '00:00:00';
        }
    },

    formatTimeFromSeconds(totalSeconds) {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    },

    startStopwatch(checkInTime) {
        this.checkInTime = checkInTime;
        this.checkOutTime = null;
        this.hasCheckedOut = false;

        // Clear existing interval
        if (this.workingHoursInterval) {
            clearInterval(this.workingHoursInterval);
        }

        // Start new interval
        this.workingHoursInterval = setInterval(() => {
            const checkIn = new Date(this.checkInTime);
            const now = new Date();
            const diffInSeconds = Math.floor((now - checkIn) / 1000);
            this.workingHours = this.formatTimeFromSeconds(diffInSeconds);
        }, 1000);
    },

    stopStopwatch(data) {
        this.hasCheckedOut = true;
        this.checkOutTime = data.checkOutTime;

        // Stop interval
        if (this.workingHoursInterval) {
            clearInterval(this.workingHoursInterval);
            this.workingHoursInterval = null;
        }

        // Hitung langsung dari selisih check-in dan check-out
        if (this.checkInTime && this.checkOutTime) {
            const checkIn = new Date(this.checkInTime);
            const checkOut = new Date(this.checkOutTime);
            const diffInSeconds = Math.floor((checkOut - checkIn) / 1000);
            this.workingHours = this.formatTimeFromSeconds(diffInSeconds);
        }
    },

    getLocation() {
        if (navigator.geolocation) {
            $wire.set('locationLoading', true);
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    $wire.updateLocation(position.coords.latitude, position.coords.longitude);
                },
                (error) => {
                    let message = 'Unable to get location. ';
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            message += 'Please allow location access.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            message += 'Location unavailable.';
                            break;
                        case error.TIMEOUT:
                            message += 'Request timed out.';
                            break;
                    }
                    $wire.setLocationError(message);
                }
            );
        } else {
            $wire.setLocationError('Geolocation not supported.');
        }
    }
}" x-init="updateClock();
updateWorkingHours();
getLocation();"
    x-on:start-stopwatch.window="startStopwatch($event.detail[0].checkInTime); status = 'checked_in';"
    x-on:stop-stopwatch.window="stopStopwatch($event.detail[0].workingHours); status = 'completed'; checkOutTime = new Date().toISOString();">

    {{-- CHECK-IN WIDGET --}}
    <x-card>
        <x-slot:header>
            <div class="flex items-center gap-2">
                <x-icon name="clock" class="w-5 h-5 text-blue-600" />
                <span class="text-base md:text-lg font-semibold">Today's Attendance</span>
            </div>
        </x-slot:header>

        <div class="p-4 md:p-6 space-y-4 md:space-y-6">
            {{-- Current Time --}}
            <div class="text-center">
                <div class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-gray-50 tabular-nums"
                    x-text="currentTime">
                    {{ now()->format('H:i:s') }}
                </div>
                <div class="mt-1 md:mt-2 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>

            {{-- Status Badge --}}
            <div class="flex justify-center">
                <template x-if="status === 'not_started'">
                    <x-badge color="gray" class="text-xs md:text-sm">
                        <x-icon name="clock" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                        Not Checked In
                    </x-badge>
                </template>
                <template x-if="status === 'checked_in'">
                    <x-badge color="green" class="text-xs md:text-sm">
                        <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                        Checked In
                    </x-badge>
                </template>
                <template x-if="status === 'completed'">
                    <x-badge color="blue" class="text-xs md:text-sm">
                        <x-icon name="check-circle" class="w-3 h-3 md:w-4 md:h-4 mr-1" />
                        Checked Out
                    </x-badge>
                </template>
            </div>

            {{-- Check-in/out Times --}}
            <div class="grid grid-cols-2 gap-3 md:gap-4">
                <div
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                    <div class="flex items-center gap-2 text-green-600 mb-1">
                        <x-icon name="arrow-right-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                        <span class="text-xs md:text-sm font-medium">Check In</span>
                    </div>
                    <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50">
                        {{ $check_in_time ? \Carbon\Carbon::parse($check_in_time)->format('H:i:s') : '--:--' }}
                    </div>
                </div>
                <div
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                    <div class="flex items-center gap-2 text-blue-600 mb-1">
                        <x-icon name="arrow-left-on-rectangle" class="w-3 h-3 md:w-4 md:h-4" />
                        <span class="text-xs md:text-sm font-medium">Check Out</span>
                    </div>
                    <div class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-50">
                        {{ $check_out_time ? \Carbon\Carbon::parse($check_out_time)->format('H:i:s') : '--:--' }}
                    </div>
                </div>
            </div>

            {{-- Working Hours --}}
            <div
                class="rounded-lg border-2 border-dashed border-blue-200 dark:border-blue-900 bg-blue-50/30 dark:bg-blue-950/30 p-3 md:p-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs md:text-sm font-medium text-gray-700 dark:text-gray-300">Working
                        Hours</span>
                    <x-icon name="clock" class="w-4 h-4 text-blue-600" />
                </div>
                <div class="mt-2 text-2xl md:text-3xl font-bold text-blue-600 tabular-nums" x-text="workingHours">
                    00:00:00
                </div>
            </div>

            {{-- Schedule Info --}}
            @if ($this->todaySchedule)
                <div
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" class="w-4 h-4 text-blue-600" />
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-50">
                                    {{ $this->todaySchedule['title'] }}
                                </div>
                                @if ($this->todaySchedule['status'] !== 'holiday')
                                    <div class="text-xs text-gray-600 dark:text-gray-400">
                                        {{ $this->todaySchedule['start_time'] }} -
                                        {{ $this->todaySchedule['end_time'] }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if ($this->todaySchedule['status'] === 'holiday')
                            <x-badge color="red" class="text-xs">Holiday</x-badge>
                        @elseif ($this->todaySchedule['status'] === 'event')
                            <x-badge color="blue" class="text-xs">Event</x-badge>
                        @else
                            <x-badge color="green" class="text-xs">Work Day</x-badge>
                        @endif
                    </div>
                </div>
            @else
                <div
                    class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4 text-center">
                    <x-icon name="calendar-days" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-sm text-gray-600 dark:text-gray-400">No schedule available for today</p>
                </div>
            @endif

            {{-- Location Status --}}
            <div
                class="rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 p-3 md:p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="map-pin" class="w-4 h-4 text-blue-600" />
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-50">Location</span>
                    </div>
                    @if ($locationLoading)
                        <div class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Getting location...</span>
                        </div>
                    @elseif($locationError)
                        <x-badge color="red" class="text-xs">{{ $locationError }}</x-badge>
                    @elseif($latitude && $longitude)
                        <x-badge color="green" class="text-xs">
                            <x-icon name="check-circle" class="w-3 h-3 mr-1" />
                            Located
                        </x-badge>
                    @endif
                </div>

                @if ($latitude && $longitude)
                    <div class="mt-2 space-y-1">
                        <div class="text-xs text-gray-600 dark:text-gray-400">
                            Lat: {{ number_format($latitude, 6) }}, Lng: {{ number_format($longitude, 6) }}
                        </div>

                        @php
                            $validOffice = null;
                            foreach ($this->officeLocations as $office) {
                                $distance = $this->calculateDistance(
                                    $latitude,
                                    $longitude,
                                    $office->latitude,
                                    $office->longitude,
                                );
                                if ($distance <= $office->radius) {
                                    $validOffice = $office;
                                    break;
                                }
                            }
                        @endphp

                        @if ($validOffice)
                            <div class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                <x-icon name="check-circle" class="w-3 h-3" />
                                <span>In {{ $validOffice->name }}</span>
                            </div>
                        @else
                            <div class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
                                <x-icon name="x-circle" class="w-3 h-3" />
                                <span>Outside office area</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="grid grid-cols-2 gap-3 md:gap-4">
                <x-button wire:click="checkIn" :disabled="!$this->canCheckIn" color="green" class="w-full" loading="checkIn">
                    <x-icon name="arrow-right-on-rectangle" class="w-4 h-4 mr-2" />
                    Check In
                </x-button>
                <x-button wire:click="$toggle('modal')" :disabled="!$this->canCheckOut" color="blue" class="w-full">
                    <x-icon name="arrow-left-on-rectangle" class="w-4 h-4 mr-2" />
                    Check Out
                </x-button>
            </div>
        </div>
    </x-card>

    {{-- Notes Modal - UNCHANGED --}}
    <x-modal wire size="2xl" center persistent>
        <x-slot:title>
            <div class="flex items-center gap-4 my-3">
                <div class="h-12 w-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                    <x-icon name="clipboard-document-check" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-50">
                        Check Out - {{ $this->isEarlyLeave ? 'Early Leave' : 'Work Summary' }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Complete your work day summary</p>
                </div>
            </div>
        </x-slot:title>

        <form wire:submit="checkOut" class="space-y-6">
            {{-- Work Notes dengan Quill --}}
            <div>
                <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                    Apa yang Anda kerjakan hari ini? *
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Jelaskan tugas dan pencapaian hari ini
                    (minimal 10 karakter)</p>

                <div x-data="{
                    quill: null,
                    init() {
                        this.quill = new Quill(this.$refs.editor, {
                            theme: 'snow',
                            modules: {
                                toolbar: [
                                    ['bold', 'italic', 'underline'],
                                    [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                                ]
                            },
                            placeholder: 'Contoh:\n• Meeting dengan tim marketing untuk campaign Q1\n• Menyelesaikan laporan bulanan\n• Review proposal klien baru'
                        });
                
                        this.quill.on('text-change', () => {
                            $wire.set('notes', this.quill.root.innerHTML);
                        });
                
                        if ($wire.notes) {
                            this.quill.root.innerHTML = $wire.notes;
                        }
                    }
                }" wire:ignore>
                    <div x-ref="editor" style="min-height: 150px;"></div>
                </div>
                @error('notes')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Early Leave Reason dengan Quill --}}
            @if ($this->isEarlyLeave)
                <div
                    class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-orange-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" />
                        </svg>
                        <div>
                            <h4 class="font-medium text-orange-900 dark:text-orange-100">Early Leave Detected</h4>
                            <p class="text-sm text-orange-700 dark:text-orange-300">Anda checkout sebelum jam kerja
                                selesai. Mohon berikan alasan.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                        Alasan Pulang Cepat *
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Jelaskan mengapa Anda perlu pulang lebih
                        awal (minimal 10 karakter)</p>

                    <div x-data="{
                        quill: null,
                        init() {
                            this.quill = new Quill(this.$refs.editor, {
                                theme: 'snow',
                                modules: {
                                    toolbar: [
                                        ['bold', 'italic', 'underline'],
                                        [{ 'list': 'ordered' }, { 'list': 'bullet' }]
                                    ]
                                },
                                placeholder: 'Contoh:\n• Ada keperluan mendesak keluarga\n• Jadwal ke dokter\n• Urusan penting lainnya'
                            });
                    
                            this.quill.on('text-change', () => {
                                $wire.set('early_leave_reason', this.quill.root.innerHTML);
                            });
                    
                            if ($wire.early_leave_reason) {
                                this.quill.root.innerHTML = $wire.early_leave_reason;
                            }
                        }
                    }" wire:ignore>
                        <div x-ref="editor" style="min-height: 120px;"></div>
                    </div>
                    @error('early_leave_reason')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Info --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3">
                <div class="flex items-start space-x-2">
                    <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" />
                    </svg>
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium">Informasi:</p>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>Catatan ini akan tersimpan dan dapat dilihat oleh atasan Anda</li>
                            <li>Jam kerja Anda: {{ $this->todayAttendance?->check_in->format('H:i') ?? '-' }} -
                                {{ now()->format('H:i') }}</li>
                            <li>Total kerja:
                                {{ $this->todayAttendance ? round($this->todayAttendance->check_in->diffInMinutes(now()) / 60, 1) : 0 }}h
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <x-button wire:click="closeNotesModal" color="secondary" outline
                    class="w-full sm:w-auto order-2 sm:order-1">
                    Cancel
                </x-button>
                <x-button type="submit" wire:click="checkOut" color="blue" icon="check" loading="checkOut"
                    class="w-full sm:w-auto order-1 sm:order-2">
                    Confirm Check Out
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
