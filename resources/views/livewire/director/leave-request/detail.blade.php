<div>
    <x-modal :title="__('Executive Review - Detail Pengajuan Cuti')" wire size="5xl">
        @if ($leaveRequest)
            <div class="space-y-6">
                {{-- Executive Dashboard Header --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                        }" size="lg" />
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $leaveRequest->getDurationInDays() }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Hari</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-gray-700 dark:text-gray-300">
                            {{ $leaveRequest->start_date->format('M Y') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Periode</div>
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
                            'approved' => 'Final Approved',
                            'rejected_manager' => 'Ditolak Manager',
                            'rejected_hr' => 'Ditolak HR',
                            'rejected_director' => 'Ditolak Direktur',
                            'cancel' => 'Dibatalkan',
                            'pending_manager' => 'Menunggu Manager',
                            'pending_hr' => 'Menunggu HR',
                            'pending_director' => 'Menunggu Direktur',
                            default => 'Unknown',
                        }" size="lg" />
                    </div>
                </div>

                {{-- Employee Profile --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Profil Karyawan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Nama Lengkap</div>
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
                            <div class="text-sm text-gray-500 dark:text-gray-400">Posisi</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ ucfirst($leaveRequest->user->role) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Pengajuan Ke</div>
                            <div class="text-lg font-medium text-gray-900 dark:text-white">
                                #{{ $leaveRequest->id }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Leave Details --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd" />
                        </svg>
                        Detail Periode Cuti
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-sm text-blue-600 dark:text-blue-400">Tanggal Mulai</div>
                            <div class="text-xl font-bold text-blue-900 dark:text-blue-100">
                                {{ $leaveRequest->start_date->format('d') }}
                            </div>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                {{ $leaveRequest->start_date->format('F Y') }}
                            </div>
                            <div class="text-xs text-blue-600 dark:text-blue-400">
                                {{ $leaveRequest->start_date->format('l') }}
                            </div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="text-sm text-purple-600 dark:text-purple-400">Durasi</div>
                            <div class="text-3xl font-bold text-purple-900 dark:text-purple-100">
                                {{ $leaveRequest->getDurationInDays() }}
                            </div>
                            <div class="text-sm text-purple-700 dark:text-purple-300">
                                Hari Kerja
                            </div>
                        </div>
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-sm text-green-600 dark:text-green-400">Tanggal Berakhir</div>
                            <div class="text-xl font-bold text-green-900 dark:text-green-100">
                                {{ $leaveRequest->end_date->format('d') }}
                            </div>
                            <div class="text-sm text-green-700 dark:text-green-300">
                                {{ $leaveRequest->end_date->format('F Y') }}
                            </div>
                            <div class="text-xs text-green-600 dark:text-green-400">
                                {{ $leaveRequest->end_date->format('l') }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Reason --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z"
                                clip-rule="evenodd" />
                        </svg>
                        Alasan Pengajuan
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $leaveRequest->reason }}</p>
                    </div>
                </x-card>

                {{-- Executive Approval Timeline --}}
                <x-card>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Executive Approval Timeline
                    </h3>
                    <div class="space-y-4">
                        {{-- Manager Level --}}
                        <div
                            class="flex items-start space-x-4 p-4 rounded-lg {{ $leaveRequest->manager_approved_at ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                            <div class="flex-shrink-0 mt-1">
                                @if ($leaveRequest->manager_approved_at)
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_manager')
                                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                                        <div class="w-4 h-4 bg-white rounded-full animate-pulse"></div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Manager Level</h4>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Level 1</div>
                                </div>
                                @if ($leaveRequest->manager_approved_at)
                                    <p class="text-green-700 dark:text-green-300 font-medium">
                                        ✅ Disetujui oleh {{ $leaveRequest->manager?->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $leaveRequest->manager_approved_at->format('l, d F Y - H:i') }} WIB
                                    </p>
                                @elseif($leaveRequest->status === 'rejected_manager')
                                    <p class="text-red-700 dark:text-red-300 font-medium">❌ Ditolak oleh manager</p>
                                @elseif($leaveRequest->status === 'pending_manager')
                                    <p class="text-yellow-700 dark:text-yellow-300 font-medium">⏳ Menunggu persetujuan
                                        manager</p>
                                @else
                                    <p class="text-gray-700 dark:text-gray-300">Selesai</p>
                                @endif
                            </div>
                        </div>

                        {{-- HR Level --}}
                        <div
                            class="flex items-start space-x-4 p-4 rounded-lg {{ $leaveRequest->hr_approved_at ? 'bg-green-50 dark:bg-green-900/20' : ($leaveRequest->status === 'pending_hr' ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-gray-700') }}">
                            <div class="flex-shrink-0 mt-1">
                                @if ($leaveRequest->hr_approved_at)
                                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_hr')
                                    <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                        <div class="w-4 h-4 bg-white rounded-full animate-pulse"></div>
                                    </div>
                                @else
                                    <div class="w-10 h-10 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">Human Resources</h4>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">Level 2</div>
                                </div>
                                @if ($leaveRequest->hr_approved_at)
                                    <p class="text-green-700 dark:text-green-300 font-medium">
                                        ✅ Disetujui oleh {{ $leaveRequest->hr?->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $leaveRequest->hr_approved_at->format('l, d F Y - H:i') }} WIB
                                    </p>
                                @elseif($leaveRequest->status === 'rejected_hr')
                                    <p class="text-red-700 dark:text-red-300 font-medium">❌ Ditolak oleh HR</p>
                                @elseif($leaveRequest->status === 'pending_hr')
                                    <p class="text-blue-700 dark:text-blue-300 font-medium">⏳ Menunggu persetujuan HR
                                    </p>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">Menunggu level sebelumnya</p>
                                @endif
                            </div>
                        </div>

                        {{-- Director Level --}}
                        <div
                            class="flex items-start space-x-4 p-4 rounded-lg {{ $leaveRequest->director_approved_at ? 'bg-green-50 dark:bg-green-900/20' : ($leaveRequest->status === 'pending_director' ? 'bg-purple-50 dark:bg-purple-900/20' : 'bg-gray-50 dark:bg-gray-700') }} border-2 border-purple-200 dark:border-purple-700">
                            <div class="flex-shrink-0 mt-1">
                                @if ($leaveRequest->director_approved_at)
                                    <div
                                        class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center ring-4 ring-green-200 dark:ring-green-700">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'rejected_director')
                                    <div
                                        class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center ring-4 ring-red-200 dark:ring-red-700">
                                        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @elseif($leaveRequest->status === 'pending_director')
                                    <div
                                        class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center ring-4 ring-purple-200 dark:ring-purple-700">
                                        <div class="w-5 h-5 bg-white rounded-full animate-pulse"></div>
                                    </div>
                                @else
                                    <div
                                        class="w-12 h-12 bg-gray-300 dark:bg-gray-600 rounded-full ring-4 ring-gray-200 dark:ring-gray-600">
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1z"
                                                clip-rule="evenodd" />
                                            <path fill-rule="evenodd"
                                                d="M5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L3 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l3-1.75a1 1 0 011.098.132z"
                                                clip-rule="evenodd" />
                                            <path fill-rule="evenodd"
                                                d="M7.16 8.008a.5.5 0 01.832.374l.014.1V13a.5.5 0 01-.212.414l-7 5A.5.5 0 010 18v-5.092a.5.5 0 01.212-.414l7-5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Executive Decision
                                    </h4>
                                    <div class="text-sm text-purple-600 dark:text-purple-400 font-bold">FINAL LEVEL
                                    </div>
                                </div>
                                @if ($leaveRequest->director_approved_at)
                                    <p class="text-green-700 dark:text-green-300 font-bold text-lg">
                                        ✅ APPROVED - Final Decision oleh {{ $leaveRequest->director?->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $leaveRequest->director_approved_at->format('l, d F Y - H:i') }} WIB
                                    </p>
                                @elseif($leaveRequest->status === 'rejected_director')
                                    <p class="text-red-700 dark:text-red-300 font-bold text-lg">❌ REJECTED - Final
                                        Decision</p>
                                    @if ($leaveRequest->rejection_reason)
                                        <p class="text-sm text-red-600 dark:text-red-400 mt-2 italic">
                                            "{{ $leaveRequest->rejection_reason }}"
                                        </p>
                                    @endif
                                @elseif($leaveRequest->status === 'pending_director')
                                    <p class="text-purple-700 dark:text-purple-300 font-bold text-lg">⏳ PENDING -
                                        Menunggu Keputusan Direktur</p>
                                    <p class="text-sm text-purple-600 dark:text-purple-400">Keputusan final diperlukan
                                    </p>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400">Menunggu approval level sebelumnya</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Attachments --}}
                @if ($leaveRequest->attachment_path)
                    <x-card>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z"
                                    clip-rule="evenodd" />
                            </svg>
                            Dokumen Pendukung
                        </h3>
                        <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                            <a href="{{ Storage::url($leaveRequest->attachment_path) }}" target="_blank"
                                class="flex items-center space-x-3 text-blue-600 dark:text-blue-400 hover:underline">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="text-lg">Download Dokumen Pendukung</span>
                            </a>
                        </div>
                    </x-card>
                @endif
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button wire:click="print" color="blue" icon="printer" size="lg">
                    Cetak Dokumen
                </x-button>

                @if ($leaveRequest && $leaveRequest->status === App\Models\LeaveRequest::STATUS_PENDING_DIRECTOR)
                    <div class="flex space-x-3">
                        <x-button
                            wire:click="$dispatch('load::approve-leave-request', { 'leaveRequest' : '{{ $leaveRequest->id }}'})"
                            color="green" icon="check-circle" size="lg">
                            FINAL APPROVAL
                        </x-button>
                        <x-button
                            wire:click="$dispatch('load::reject-leave-request', { 'leaveRequest' : '{{ $leaveRequest->id }}'})"
                            color="red" icon="x-circle" size="lg">
                            FINAL REJECTION
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
            window.open(`/director/leave-requests/${event[0].id}/print`, '_blank');
        });
    });
</script>
