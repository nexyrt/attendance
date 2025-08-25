<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">New Leave Request</h1>
        <x-button href="{{ route('leave-requests.index') }}" color="gray" wire:navigate>
            Back to List
        </x-button>
    </div>

    {{-- Balance Info --}}
    @if($this->leaveBalance)
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $this->leaveBalance->total_balance }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Balance</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $this->leaveBalance->remaining_balance }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Available</div>
                </div>
                <div>
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                        {{ $this->leaveBalance->used_balance }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Used</div>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Request Preview --}}
    @if($this->requestedDays > 0)
        <x-card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">
                        {{ $this->requestedDays }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Requested Days</div>
                </div>
                <div>
                    <div class="text-xl font-bold {{ $this->hasValidBalance ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $this->balanceAfterRequest }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Balance After</div>
                </div>
                <div>
                    @if($this->hasValidBalance)
                        <x-badge color="green" text="Valid Request" />
                    @else
                        <x-badge color="red" text="Insufficient Balance" />
                    @endif
                </div>
            </div>
        </x-card>
    @endif

    {{-- Form --}}
    <x-card>
        <form wire:submit="save" class="space-y-6">
            {{-- Leave Type --}}
            <div>
                <x-select.native 
                    label="Leave Type *" 
                    wire:model.live="leaveRequest.type"
                    :options="[
                        ['label' => 'Select Type', 'value' => ''],
                        ['label' => 'Sick Leave', 'value' => 'sick'],
                        ['label' => 'Annual Leave', 'value' => 'annual'],
                        ['label' => 'Important Leave', 'value' => 'important'],
                        ['label' => 'Other', 'value' => 'other'],
                    ]"
                    required
                />
            </div>

            {{-- Date Range --}}
            <div>
                <x-date 
                    label="Leave Period *" 
                    wire:model.blur="dateRange"
                    range
                    :min-date="now()->format('Y-m-d')"
                    required
                />
            </div>

            {{-- Reason --}}
            <div>
                <x-textarea 
                    label="Reason *" 
                    wire:model="leaveRequest.reason"
                    hint="Minimum 10 characters, maximum 500 characters"
                    rows="4"
                    required
                />
            </div>

            {{-- Attachment --}}
            <div>
                <x-upload 
                    label="Supporting Document (Optional)"
                    wire:model="attachment"
                    accept="image/*,application/pdf"
                    tip="Upload supporting documents (JPG, PNG, PDF - Max 2MB)"
                />
            </div>

            {{-- Signature --}}
            <div>
                <x-signature 
                    label="Your Signature *" 
                    wire:model="signature"
                    hint="Please sign to confirm your leave request"
                    height="200"
                    clearable
                    required
                />
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end space-x-4">
                <x-button type="button" color="gray" href="{{ route('leave-requests.index') }}" wire:navigate>
                    Cancel
                </x-button>
                <x-button 
                    type="submit" 
                    color="blue" 
                    loading="save"
                    :disabled="!$this->hasValidBalance"
                >
                    Submit Request
                </x-button>
            </div>
        </form>
    </x-card>
</div>