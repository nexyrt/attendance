<div>
    <x-modal :title="__('Leave Request Details')" wire size="4xl">
        @if ($leaveRequest)
            <div class="space-y-6">
                {{-- Header Info --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-badge :color="match ($leaveRequest->type) {
                            'sick' => 'red',
                            'annual' => 'blue',
                            'important' => 'orange',
                            'other' => 'gray',
                        }" :text="ucfirst($leaveRequest->type) . ' Leave'" />
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $leaveRequest->getDurationInDays() }}
                            Day{{ $leaveRequest->getDurationInDays() > 1 ? 's' : '' }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Duration</div>
                    </div>
                    <div class="text-right">
                        <x-badge :color="match ($leaveRequest->status) {
                            'approved' => 'green',
                            'rejected_manager', 'rejected_hr', 'rejected_director' => 'red',
                            'cancel' => 'gray',
                            default => 'yellow',
                        }" :text="ucfirst(str_replace(['_', 'pending_'], [' ', ''], $leaveRequest->status))" />
                    </div>
                </div>

                {{-- Dates --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Leave Period</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Start Date</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->start_date->format('l, F j, Y') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">End Date</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->end_date->format('l, F j, Y') }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Reason --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Reason</h3>
                    <p class="text-gray-700 dark:text-gray-300">{{ $leaveRequest->reason }}</p>
                </x-card>

                {{-- Approval Timeline --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Approval Timeline</h3>
                    <div class="space-y-4">
                        {{-- Manager Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->manager_approved_at)
                                    <div
                                        class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_manager'))
                                    <div
                                        class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_manager')
                                    <div
                                        class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Manager Approval</div>
                                @if ($leaveRequest->manager_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Approved by {{ $leaveRequest->manager?->name }} on
                                        {{ $leaveRequest->manager_approved_at->format('M j, Y H:i') }}
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_manager'))
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        Rejected by manager
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_manager')
                                    <div class="text-sm text-yellow-600 dark:text-yellow-400">Pending manager approval
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Awaiting manager assignment
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- HR Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->hr_approved_at)
                                    <div
                                        class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_hr'))
                                    <div
                                        class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <div
                                        class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">HR Approval</div>
                                @if ($leaveRequest->hr_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Approved by {{ $leaveRequest->hr?->name }} on
                                        {{ $leaveRequest->hr_approved_at->format('M j, Y H:i') }}
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_hr'))
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        Rejected by HR
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <div class="text-sm text-yellow-600 dark:text-yellow-400">Pending HR approval</div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Awaiting HR review</div>
                                @endif
                            </div>
                        </div>

                        {{-- Director Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->director_approved_at)
                                    <div
                                        class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_director'))
                                    <div
                                        class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_director')
                                    <div
                                        class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Director Approval</div>
                                @if ($leaveRequest->director_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Approved by {{ $leaveRequest->director?->name }} on
                                        {{ $leaveRequest->director_approved_at->format('M j, Y H:i') }}
                                    </div>
                                @elseif(str_starts_with($leaveRequest->status, 'rejected_director'))
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        Rejected by director
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_director')
                                    <div class="text-sm text-yellow-600 dark:text-yellow-400">Pending director approval
                                    </div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Awaiting director review</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Attachments --}}
                @if ($leaveRequest->attachment_path || $leaveRequest->staff_signature)
                    <x-card>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Attachments</h3>
                        <div class="space-y-3">
                            @if ($leaveRequest->attachment_path)
                                <div>
                                    <a href="{{ Storage::url($leaveRequest->attachment_path) }}" target="_blank"
                                        class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 hover:underline">
                                        <x-icon name="paper-clip" class="w-4 h-4" />
                                        <span>Supporting Document</span>
                                    </a>
                                </div>
                            @endif
                            @if ($leaveRequest->staff_signature)
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Staff Signature:</div>
                                    <img src="{{ Storage::url($leaveRequest->staff_signature) }}"
                                        alt="Staff Signature"
                                        class="border border-gray-200 dark:border-gray-700 rounded max-w-xs">
                                </div>
                            @endif
                        </div>
                    </x-card>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <div class="flex space-x-2">
                <x-button wire:click="print" color="blue">
                    Print
                </x-button>
                @if ($leaveRequest?->canBeCancelled())
                    <livewire:staff.leave-request.cancel :leaveRequest="$leaveRequest" :key="'cancel-' . $leaveRequest->id" />
                @endif
            </div>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-leave-request', (event) => {
            window.open(`/leave-requests/${event[0].id}/print`, '_blank');
        });
    });
</script>
