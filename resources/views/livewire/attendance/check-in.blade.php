<div class="space-y-4" x-data="{
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
}" x-init="getLocation()">

    {{-- Header --}}
    <div class="text-center py-2">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Attendance</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">{{ now()->format('l, M j • H:i') }}</p>
    </div>

    {{-- Schedule + Status Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Today Schedule --}}
        @if ($this->todaySchedule)
            <x-card class="p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $this->todaySchedule['title'] }}
                            </h3>
                            @if ($this->todaySchedule['status'] !== 'holiday')
                                <p class="text-xs text-gray-600">{{ $this->todaySchedule['start_time'] }} -
                                    {{ $this->todaySchedule['end_time'] }}</p>
                            @endif
                        </div>
                    </div>
                    <x-badge :color="$this->todaySchedule['status'] === 'holiday' ? 'red' : 'green'" :text="ucfirst($this->todaySchedule['status'])" xs />
                </div>
            </x-card>
        @endif

        {{-- Current Status --}}
        <x-card class="p-4">
            @if ($this->todayAttendance)
                @if ($this->todayAttendance->check_in && !$this->todayAttendance->check_out)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-green-600">Checked In</h3>
                                <p class="text-xs text-gray-600">{{ $this->todayAttendance->check_in->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <x-badge color="green" :text="ucfirst($this->todayAttendance->status)" xs />
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $this->todayAttendance->check_in->diffForHumans(null, true) }}</p>
                        </div>
                    </div>
                @elseif($this->todayAttendance->check_in && $this->todayAttendance->check_out)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-blue-600">Completed</h3>
                                <p class="text-xs text-gray-600">{{ $this->todayAttendance->check_in->format('H:i') }} -
                                    {{ $this->todayAttendance->check_out->format('H:i') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <x-badge color="blue" :text="ucfirst($this->todayAttendance->status)" xs />
                            @if ($this->todayAttendance->working_hours)
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ number_format($this->todayAttendance->working_hours, 1) }}h</p>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-600">Ready</h3>
                            <p class="text-xs text-gray-500">Start your workday</p>
                        </div>
                    </div>
                    <x-badge color="gray" text="Not started" xs />
                </div>
            @endif
        </x-card>
    </div>

    {{-- Location & Action Row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Location Status --}}
        <x-card class="p-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900 dark:text-white">Location</h4>
                <button x-on:click="getLocation()" class="text-blue-500 text-xs hover:text-blue-700">
                    Refresh
                </button>
            </div>

            <div x-show="$wire.locationLoading" class="text-center py-4">
                <div class="w-6 h-6 border-2 border-blue-200 border-t-blue-600 rounded-full animate-spin mx-auto mb-2">
                </div>
                <p class="text-xs text-gray-600">Getting location...</p>
            </div>

            <div x-show="$wire.locationError" class="text-center py-4">
                <svg class="w-6 h-6 text-red-500 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
                </svg>
                <p class="text-xs text-red-600 mb-2" x-text="$wire.locationError"></p>
                <x-button color="red" size="sm" x-on:click="getLocation()">Retry</x-button>
            </div>

            <div x-show="!$wire.locationLoading && !$wire.locationError">
                @php
                    $validOffice = null;
                    if ($this->latitude && $this->longitude) {
                        foreach ($this->officeLocations as $office) {
                            $distance = $this->calculateDistance(
                                $this->latitude,
                                $this->longitude,
                                $office->latitude,
                                $office->longitude,
                            );
                            if ($distance <= $office->radius) {
                                $validOffice = $office;
                                break;
                            }
                        }
                    }
                @endphp

                @if ($validOffice)
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-green-600">{{ $validOffice->name }}</p>
                            <p class="text-xs text-gray-500">Within range</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-red-600">Outside area</p>
                            <p class="text-xs text-gray-500">Move closer to office</p>
                        </div>
                    </div>
                @endif
            </div>
        </x-card>

        {{-- Action Button --}}
        <x-card class="p-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Action</h4>

            @if ($this->canCheckIn)
                <x-button color="green" class="w-full" wire:click="checkIn" loading="checkIn">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg>
                        <span>Check In</span>
                    </div>
                </x-button>
            @elseif($this->canCheckOut)
                <x-button color="blue" class="w-full" wire:click="openNotesModal">
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3z" />
                        </svg>
                        <span>Check Out</span>
                    </div>
                </x-button>
            @else
                <x-button color="gray" class="w-full" disabled>
                    <div class="flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm">
                            @if ($this->locationLoading)
                                Getting Location...
                            @elseif($this->locationError)
                                Location Error
                            @elseif(!$this->todaySchedule)
                                No Schedule
                            @elseif($this->todaySchedule['status'] === 'holiday')
                                Holiday
                            @elseif($this->todayAttendance?->check_in && $this->todayAttendance?->check_out)
                                Completed
                            @else
                                Not Available
                            @endif
                        </span>
                    </div>
                </x-button>
            @endif

            <p class="text-xs text-gray-500 text-center mt-2">
                @if ($this->canCheckIn)
                    Ensure you're at office location
                @elseif($this->canCheckOut)
                    Add notes about your work today
                @else
                    Check requirements above
                @endif
            </p>
        </x-card>
    </div>

    {{-- Office Locations --}}
    <x-card class="p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="font-medium text-gray-900 dark:text-white">Office Locations</h4>
            <x-badge color="blue" :text="count($this->officeLocations)" xs />
        </div>

        <div class="space-y-2">
            @foreach ($this->officeLocations as $office)
                @php
                    $isInRange = false;
                    if ($this->latitude && $this->longitude) {
                        $distance = $this->calculateDistance(
                            $this->latitude,
                            $this->longitude,
                            $office->latitude,
                            $office->longitude,
                        );
                        $isInRange = $distance <= $office->radius;
                    }
                @endphp

                <div
                    class="flex items-center justify-between py-2 px-3 border border-gray-200 dark:border-gray-700 rounded-lg {{ $isInRange ? 'bg-green-50 dark:bg-green-900/10 border-green-200' : '' }}">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-6 h-6 {{ $isInRange ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }} rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $office->name }}</p>
                            @if ($office->address)
                                <p class="text-xs text-gray-500 truncate">{{ Str::limit($office->address, 30) }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-badge :color="$isInRange ? 'green' : 'gray'" :text="$office->radius . 'm'" xs />
                        @if ($isInRange)
                            <span class="text-xs text-green-600 font-medium">✓</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    {{-- Notes Modal --}}
    <x-modal wire title="Check Out - {{ $this->isEarlyLeave ? 'Early Leave' : 'Work Summary' }}" size="2xl"
        center>
        <form wire:submit="checkOut" class="space-y-4">
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
            <div class="flex justify-end gap-2">
                <x-button color="gray" wire:click="closeNotesModal">
                    Batal
                </x-button>
                <x-button color="blue" wire:click="checkOut" loading="checkOut">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>
                        <span>Confirm Check Out</span>
                    </div>
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
