<div>
    <x-button icon="arrow-down-tray" wire:click="$toggle('modal')" color="green" outline>
        Export Report
    </x-button>

    <x-modal title="Export Team Attendance Report" wire>
        <form wire:submit="export" class="space-y-6">
            {{-- Export Format --}}
            <div>
                <x-select.styled label="Export Format *" :options="$this->getFormatOptions()" wire:model="format" required />
            </div>

            {{-- Period Selection --}}
            <div>
                <x-select.styled label="Period *" :options="$this->getPeriodOptions()" wire:model.live="period" required />
            </div>

            {{-- Date Range based on period --}}
            @if ($period === 'month')
                <div>
                    <x-input type="month" label="Month *" wire:model.live="month" required />
                </div>
            @elseif($period === 'custom')
                <div class="grid grid-cols-2 gap-4">
                    <x-input type="date" label="Start Date *" wire:model.live="startDate" required />
                    <x-input type="date" label="End Date *" wire:model.live="endDate" required />
                </div>
            @endif

            {{-- Team Member Selection --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Team Members (Leave empty for all)
                </label>
                <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3">
                    @foreach ($this->teamMembers as $member)
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="selectedMembers" value="{{ $member->id }}"
                                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">{{ $member->name }}</span>
                        </label>
                    @endforeach
                </div>
                @if (count($selectedMembers) > 0)
                    <p class="text-xs text-gray-500 mt-1">{{ count($selectedMembers) }} member(s) selected</p>
                @endif
            </div>

            {{-- Additional Options --}}
            <div>
                <label class="flex items-center">
                    <input type="checkbox" wire:model="includeStats"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">Include summary statistics</span>
                </label>
            </div>

            {{-- Preview Information --}}
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Export Preview</h4>
                <div class="text-sm text-gray-600 space-y-1">
                    <div>Format: <span
                            class="font-medium">{{ $this->getFormatOptions()[$format] ?? 'Not selected' }}</span></div>
                    <div>Period: <span class="font-medium">
                            @if ($period === 'month' && $month)
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}
                            @elseif($period === 'custom' && $startDate && $endDate)
                                {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} -
                                {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                            @else
                                Not selected
                            @endif
                        </span></div>
                    <div>Members: <span class="font-medium">
                            @if (count($selectedMembers) === 0)
                                All team members ({{ $this->teamMembers->count() }})
                            @else
                                {{ count($selectedMembers) }} selected
                            @endif
                        </span></div>
                    <div>Include Stats: <span class="font-medium">{{ $includeStats ? 'Yes' : 'No' }}</span></div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <div class="flex justify-end gap-3">
                <x-button color="gray" wire:click="$toggle('modal')">
                    Cancel
                </x-button>
                <x-button wire:click="export" loading="export" color="green" icon="arrow-down-tray">
                    Generate Export
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>
