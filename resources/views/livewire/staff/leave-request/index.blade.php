<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Leave Requests</h1>
        <x-button href="{{ route('staff.leave-requests.create') }}" color="blue" wire:navigate>
            New Request
        </x-button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-card class="text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ $this->statusStats['total'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Requests</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                {{ $this->statusStats['pending'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Pending</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ $this->statusStats['approved'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Approved</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                {{ $this->statusStats['rejected'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Rejected</div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-input label="Search Reason" wire:model.live.debounce.500ms="search"
                    placeholder="Search by reason..." />
            </div>

            <div>
                <x-select.native label="Status" wire:model.live="status" :options="collect($this->getStatusOptions())
                    ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                    ->values()" />
            </div>

            <div>
                <x-select.native label="Type" wire:model.live="type" :options="collect($this->getTypeOptions())
                    ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                    ->values()" />
            </div>

            <div class="flex items-end">
                <x-button color="gray" wire:click="$set('search', null); $set('status', null); $set('type', null)"
                    class="w-full">
                    Reset Filters
                </x-button>
            </div>
        </div>
    </x-card>

    {{-- Leave Requests Table --}}
    <x-card>
        <x-table :$headers :$sort :rows="$this->rows" paginate filter loading :quantity="[5, 10, 15, 25]">
            @interact('column_type', $row)
                <x-badge :color="match ($row->type) {
                    'sick' => 'red',
                    'annual' => 'blue',
                    'important' => 'orange',
                    'other' => 'gray',
                }" :text="ucfirst($row->type)" />
            @endinteract

            @interact('column_dates', $row)
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $row->start_date->format('M j') }} - {{ $row->end_date->format('M j, Y') }}
                    </div>
                    @if ($row->start_date->year !== $row->end_date->year)
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $row->start_date->format('Y') }} - {{ $row->end_date->format('Y') }}
                        </div>
                    @endif
                </div>
            @endinteract

            @interact('column_duration', $row)
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ $row->getDurationInDays() }} day{{ $row->getDurationInDays() > 1 ? 's' : '' }}
                </span>
            @endinteract

            @interact('column_reason', $row)
                <div class="max-w-xs">
                    <p class="text-sm text-gray-900 dark:text-white truncate" title="{{ $row->reason }}">
                        {{ $row->reason }}
                    </p>
                </div>
            @endinteract

            @interact('column_status', $row)
                <x-badge :color="match ($row->status) {
                    'approved' => 'green',
                    'rejected_manager', 'rejected_hr', 'rejected_director' => 'red',
                    'cancel' => 'gray',
                    default => 'yellow',
                }" :text="ucfirst(str_replace(['_', 'pending_'], [' ', ''], $row->status))" />
            @endinteract

            @interact('column_created_at', $row)
                <div>
                    <div class="text-gray-900 dark:text-white">
                        {{ $row->created_at->format('M j, Y') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $row->created_at->format('H:i') }}
                    </div>
                </div>
            @endinteract

            @interact('column_action', $row)
                <div class="flex gap-1">
                    <x-button.circle icon="eye" color="blue"
                        wire:click="$dispatch('load::leave-request', { 'leaveRequest' : '{{ $row->id }}'})"
                        title="View Details" />

                    <x-button.circle icon="printer" color="green"
                        onclick="window.open('/leave-requests/{{ $row->id }}/print', '_blank')" title="Print" />

                    @if ($row->canBeCancelled())
                        <livewire:staff.leave-request.cancel :leaveRequest="$row" :key="uniqid('', true)"
                            @cancelled="$refresh" /> @endif </div>
                        @endinteract
        </x-table>
    </x-card>

    {{-- Show Modal --}}
    <livewire:staff.leave-request.show />
</div>
