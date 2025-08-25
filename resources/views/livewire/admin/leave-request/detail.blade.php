<div>
    <x-modal :title="__('Detail Pengajuan Cuti (HR View)')" wire size="4xl">
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
                        }" :text="match ($leaveRequest->type) {
                            'sick' => 'Cuti Sakit',
                            'annual' => 'Cuti Tahunan',
                            'important' => 'Cuti Penting',
                            'other' => 'Lainnya',
                        }" />
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $leaveRequest->getDurationInDays() }} hari
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Durasi</div>
                    </div>
                    <div class="text-right">
                        <x-badge :color="match ($leaveRequest->status) {
                            'approved' => 'green',
                            'rejected_manager', 'rejected_hr', 'rejected_director' => 'red',
                            'cancel' => 'gray',
                            'pending_manager' => 'yellow',
                            'pending_hr' => 'blue',
                            'pending_director' => 'purple',
                            default => 'gray',
                        }" :text="match ($leaveRequest->status) {
                            'approved' => 'Disetujui',
                            'rejected_manager' => 'Ditolak Manager',
                            'rejected_hr' => 'Ditolak HR',
                            'rejected_director' => 'Ditolak Direktur',
                            'cancel' => 'Dibatalkan',
                            'pending_manager' => 'Menunggu Manager',
                            'pending_hr' => 'Menunggu HR',
                            'pending_director' => 'Menunggu Direktur',
                            default => 'Unknown',
                        }" />
                    </div>
                </div>

                {{-- Employee Info --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Karyawan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Nama</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->user->name }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Departemen</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->user->department?->name ?? '-' }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Role</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ ucfirst($leaveRequest->user->role) }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Leave Period --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Periode Cuti</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Mulai</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->start_date->format('l, d F Y') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal Berakhir</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->end_date->format('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Reason --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Alasan Pengajuan</h3>
                    <p class="text-gray-700 dark:text-gray-300">{{ $leaveRequest->reason }}</p>
                </x-card>

                {{-- Approval Timeline --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Timeline Persetujuan</h3>
                    <div class="space-y-4">
                        {{-- Manager Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->manager_approved_at)
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_manager')
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_manager')
                                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-full flex items-center justify-center">
                                        <div class="w-3 h-3 bg-yellow-600 dark:bg-yellow-400 rounded-full animate-pulse"></div>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Persetujuan Manager</div>
                                @if ($leaveRequest->manager_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        ✅ Disetujui oleh {{ $leaveRequest->manager?->name }} pada
                                        {{ $leaveRequest->manager_approved_at->format('d M Y H:i') }}
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_manager')
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        ❌ Ditolak oleh manager
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_manager')
                                    <div class="text-sm text-yellow-600 dark:text-yellow-400">⏳ Menunggu persetujuan manager</div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Belum diproses</div>
                                @endif
                            </div>
                        </div>

                        {{-- HR Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->hr_approved_at)
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_hr')
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                                        <div class="w-3 h-3 bg-blue-600 dark:bg-blue-400 rounded-full animate-pulse"></div>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Persetujuan HR</div>
                                @if ($leaveRequest->hr_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        ✅ Disetujui oleh {{ $leaveRequest->hr?->name }} pada
                                        {{ $leaveRequest->hr_approved_at->format('d M Y H:i') }}
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_hr')
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        ❌ Ditolak oleh HR
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <div class="text-sm text-blue-600 dark:text-blue-400">⏳ Menunggu persetujuan HR</div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu approval manager</div>
                                @endif
                            </div>
                        </div>

                        {{-- Director Approval --}}
                        <div class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700">
                            <div class="flex-shrink-0">
                                @if ($leaveRequest->director_approved_at)
                                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_director')
                                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_director')
                                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center">
                                        <div class="w-3 h-3 bg-purple-600 dark:bg-purple-400 rounded-full animate-pulse"></div>
                                    </div>
                                @else
                                    <div class="w-8 h-8 bg-gray-100 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">Persetujuan Direktur</div>
                                @if ($leaveRequest->director_approved_at)
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        ✅ Disetujui oleh {{ $leaveRequest->director?->name }} pada
                                        {{ $leaveRequest->director_approved_at->format('d M Y H:i') }}
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_director')
                                    <div class="text-sm text-red-600 dark:text-red-400">
                                        ❌ Ditolak oleh direktur
                                        @if ($leaveRequest->rejection_reason)
                                            - {{ $leaveRequest->rejection_reason }}
                                        @endif
                                    </div>
                                @elseif($leaveRequest->status === 'pending_director')
                                    <div class="text-sm text-purple-600 dark:text-purple-400">⏳ Menunggu persetujuan direktur</div>
                                @else
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu approval HR</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Attachments --}}
                @if ($leaveRequest->attachment_path)
                    <x-card>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Lampiran</h3>
                        <div>
                            <a href="{{ Storage::url($leaveRequest->attachment_path) }}" target="_blank"
                                class="flex items-center space-x-2 text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                <span>Dokumen Pendukung</span>
                            </a>
                        </div>
                    </x-card>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button wire:click="print" color="blue" icon="printer">
                    Cetak
                </x-button>
                
                @if ($leaveRequest && $leaveRequest->status === App\Models\LeaveRequest::STATUS_PENDING_HR)
                    <div class="flex space-x-2">
                        <x-button 
                            wire:click="$dispatch('load::approve-leave-request', { 'leaveRequest' : '{{ $leaveRequest->id }}'})"
                            color="green" 
                            icon="check"
                        >
                            Setujui
                        </x-button>
                        <x-button 
                            wire:click="$dispatch('load::reject-leave-request', { 'leaveRequest' : '{{ $leaveRequest->id }}'})"
                            color="red" 
                            icon="x-mark"
                        >
                            Tolak
                        </x-button>
                    </div>
                @endif
            </div>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('print-leave-request', (event) => {
            window.open(`/admin/leave-requests/${event[0].id}/print`, '_blank');
        });
    });
</script>