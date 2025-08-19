<div class="max-w-2xl mx-auto space-y-6" x-data="{
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
    <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Check In / Check Out</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ now()->format('l, F j, Y • H:i') }}</p>
    </div>

    {{-- Today Schedule --}}
    @if ($this->todaySchedule)
        <x-card class="p-4">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-white">{{ $this->todaySchedule['title'] }}</h3>
                    @if ($this->todaySchedule['status'] !== 'holiday')
                        <p class="text-sm text-gray-600">
                            {{ $this->todaySchedule['start_time'] }} - {{ $this->todaySchedule['end_time'] }}
                        </p>
                    @endif
                </div>
                <x-badge :color="$this->todaySchedule['status'] === 'holiday' ? 'red' : 'green'" :text="ucfirst($this->todaySchedule['status'])" />
            </div>
        </x-card>
    @endif

    {{-- Current Status --}}
    <x-card class="p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @if ($this->todayAttendance)
                    @if ($this->todayAttendance->check_in && !$this->todayAttendance->check_out)
                        <div class="text-4xl">🏢</div>
                        <div>
                            <h3 class="text-lg font-semibold text-green-600">Checked In</h3>
                            <p class="text-sm text-gray-600">Since {{ $this->todayAttendance->check_in->format('H:i') }}
                            </p>
                            @if ($this->todayAttendance->checkInOffice)
                                <p class="text-xs text-gray-500">📍 {{ $this->todayAttendance->checkInOffice->name }}
                                </p>
                            @endif
                        </div>
                    @elseif($this->todayAttendance->check_in && $this->todayAttendance->check_out)
                        <div class="text-4xl">✅</div>
                        <div>
                            <h3 class="text-lg font-semibold text-blue-600">Work Completed</h3>
                            <p class="text-sm text-gray-600">
                                {{ $this->todayAttendance->check_in->format('H:i') }} -
                                {{ $this->todayAttendance->check_out->format('H:i') }}
                            </p>
                        </div>
                    @endif
                @else
                    <div class="text-4xl">⏰</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-600">Ready to Check In</h3>
                        <p class="text-sm text-gray-600">Start your workday</p>
                    </div>
                @endif
            </div>

            {{-- Status Badges --}}
            <div class="flex flex-col items-end space-y-2">
                @if ($this->todayAttendance)
                    <x-badge :color="match ($this->todayAttendance->status) {
                        'present' => 'green',
                        'late' => 'red',
                        default => 'gray',
                    }" :text="ucfirst($this->todayAttendance->status)" />
                    @if ($this->todayAttendance->working_hours)
                        <x-badge color="blue" text="{{ number_format($this->todayAttendance->working_hours, 1) }}h" />
                    @endif
                @endif
            </div>
        </div>
    </x-card>

    {{-- Location & Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Location Status --}}
        <x-card class="p-4">
            <h4 class="font-medium text-gray-900 dark:text-white mb-3">Location Status</h4>

            <div x-show="$wire.locationLoading" class="text-center py-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto mb-2"></div>
                <p class="text-sm text-gray-600">Getting location...</p>
            </div>

            <div x-show="$wire.locationError" class="text-center py-4">
                <div class="text-2xl mb-2">❌</div>
                <p class="text-sm text-red-600 mb-2" x-text="$wire.locationError"></p>
                <x-button color="blue" size="sm" x-on:click="getLocation()">Retry</x-button>
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
                    <div class="text-center py-2">
                        <div class="text-2xl mb-2">📍</div>
                        <p class="font-medium text-green-600">{{ $validOffice->name }}</p>
                        <p class="text-xs text-gray-500">Within office radius</p>
                    </div>
                @else
                    <div class="text-center py-2">
                        <div class="text-2xl mb-2">🚫</div>
                        <p class="text-sm text-red-600">Outside office area</p>
                        <p class="text-xs text-gray-500">Move closer to an office</p>
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
                        <span class="text-lg">🚪</span>
                        <span>Check In</span>
                    </div>
                </x-button>
            @elseif($this->canCheckOut)
                <x-button color="blue" class="w-full" wire:click="checkOut" loading="checkOut">
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-lg">🚪</span>
                        <span>Check Out</span>
                    </div>
                </x-button>
            @else
                <x-button color="gray" class="w-full" disabled>
                    <div class="flex items-center justify-center space-x-2">
                        <span class="text-lg">🔒</span>
                        <span>
                            @if ($this->locationLoading)
                                Getting Location...
                            @elseif($this->locationError)
                                Location Error
                            @elseif(!$this->todaySchedule)
                                No Schedule
                            @elseif($this->todaySchedule['status'] === 'holiday')
                                Holiday
                            @elseif($this->todayAttendance?->check_in && $this->todayAttendance?->check_out)
                                Work Completed
                            @else
                                Not Available
                            @endif
                        </span>
                    </div>
                </x-button>
            @endif
        </x-card>
    </div>

    {{-- Office Locations --}}
    <x-card class="p-4">
        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Office Locations</h4>
        <div class="space-y-2">
            @foreach ($this->officeLocations as $office)
                <div
                    class="flex justify-between items-center text-sm py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $office->name }}</p>
                        @if ($office->address)
                            <p class="text-xs text-gray-500">{{ Str::limit($office->address, 40) }}</p>
                        @endif
                    </div>
                    <x-badge :color="$this->latitude &&
                    $this->longitude &&
                    $this->calculateDistance(
                        $this->latitude,
                        $this->longitude,
                        $office->latitude,
                        $office->longitude,
                    ) <= $office->radius
                        ? 'green'
                        : 'gray'" :text="$office->radius . 'm'" size="sm" />
                </div>
            @endforeach
        </div>
    </x-card>
</div>
