<div class="space-y-6" x-data="attendanceLocation()">
    {{-- Header --}}
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Check In / Check Out</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">{{ now()->format('l, F j, Y') }}</p>
    </div>

    {{-- Status Card --}}
    <x-card>
        <div class="text-center">
            @if ($this->todayAttendance)
                @if ($this->todayAttendance->check_in && !$this->todayAttendance->check_out)
                    <div class="text-6xl mb-4">🏢</div>
                    <h2 class="text-xl font-semibold text-green-600 dark:text-green-400">Checked In</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Since {{ $this->todayAttendance->check_in->format('H:i') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Status: {{ ucfirst($this->todayAttendance->status) }}
                    </p>
                @elseif($this->todayAttendance->check_in && $this->todayAttendance->check_out)
                    <div class="text-6xl mb-4">🏠</div>
                    <h2 class="text-xl font-semibold text-blue-600 dark:text-blue-400">Checked Out</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Work completed: {{ $this->todayAttendance->check_in->format('H:i') }} -
                        {{ $this->todayAttendance->check_out->format('H:i') }}
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Working hours: {{ number_format($this->todayAttendance->working_hours, 1) }}h
                    </p>
                @endif
            @else
                <div class="text-6xl mb-4">⏰</div>
                <h2 class="text-xl font-semibold text-gray-600 dark:text-gray-400">Ready to Check In</h2>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Start your workday</p>
            @endif
        </div>
    </x-card>

    {{-- Location Status --}}
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Location Status</h3>

        <div x-show="$wire.locationLoading" class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Getting your location...</p>
        </div>

        <div x-show="$wire.locationError" class="text-center py-4">
            <div class="text-4xl mb-2">📍</div>
            <p class="text-red-600 dark:text-red-400" x-text="$wire.locationError"></p>
            <x-button color="blue" size="sm" class="mt-2" x-on:click="getLocation()">
                Retry Location
            </x-button>
        </div>

        <div x-show="!$wire.locationLoading && !$wire.locationError" class="text-center py-4">
            <div class="text-4xl mb-2">✅</div>
            <p class="text-green-600 dark:text-green-400">Location detected</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Ready for check-in/out
            </p>
        </div>
    </x-card>

    {{-- Action Buttons --}}
    <div class="flex justify-center space-x-4">
        @if (!$this->todayAttendance?->check_in)
            <x-button color="green" size="lg" wire:click="checkIn" :disabled="$locationLoading || $locationError" loading="checkIn">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🚪</span>
                    <span>Check In</span>
                </div>
            </x-button>
        @elseif(!$this->todayAttendance->check_out)
            <x-button color="blue" size="lg" wire:click="checkOut" :disabled="$locationLoading || $locationError" loading="checkOut">
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🚪</span>
                    <span>Check Out</span>
                </div>
            </x-button>
        @else
            <div class="text-center">
                <p class="text-gray-600 dark:text-gray-400">Work day completed!</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">See you tomorrow 👋</p>
            </div>
        @endif
    </div>

    {{-- Office Locations Info --}}
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Office Locations</h3>
        <div class="space-y-3">
            @foreach ($this->officeLocations as $office)
                <div
                    class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $office->name }}</p>
                        @if ($office->address)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $office->address }}</p>
                        @endif
                    </div>
                    <x-badge color="blue" text="{{ $office->radius }}m radius" />
                </div>
            @endforeach
        </div>
    </x-card>
</div>

<script>
    function attendanceLocation() {
        return {
            init() {
                this.getLocation();
            },

            getLocation() {
                if (!navigator.geolocation) {
                    this.$wire.locationError('Geolocation is not supported by this browser');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.$wire.updateLocation(
                            position.coords.latitude,
                            position.coords.longitude
                        );
                    },
                    (error) => {
                        let message = 'Location access denied';
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                message = 'Location access denied. Please enable location access.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                message = 'Location information unavailable.';
                                break;
                            case error.TIMEOUT:
                                message = 'Location request timed out.';
                                break;
                        }
                        this.$wire.locationError(message);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            }
        }
    }
</script>
