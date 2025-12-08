<div>
    <x-modal wire="modal" size="2xl" center>
        @if ($this->leaveRequest)
            {{-- Custom Title --}}
            <x-slot:title>
                <div class="flex items-center gap-4 my-3">
                    <div class="h-12 w-12 bg-blue-50 dark:bg-blue-900/20 rounded-xl flex items-center justify-center">
                        <x-icon name="document-text" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Detail Pengajuan Cuti</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            #{{ $this->leaveRequest->id }} - {{ $this->leaveRequest->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>
            </x-slot:title>

            <div class="space-y-6">
                {{-- Status Badge --}}
                <div class="flex justify-center">
                    <x-badge :color="match (true) {
                        $this->leaveRequest->isPendingManager() => 'yellow',
                        $this->leaveRequest->isPendingHR() => 'amber',
                        $this->leaveRequest->isPendingDirector() => 'orange',
                        $this->leaveRequest->isApproved() => 'green',
                        $this->leaveRequest->isRejected() => 'red',
                        $this->leaveRequest->isCancelled() => 'gray',
                        default => 'gray',
                    }" :text="match ($this->leaveRequest->status) {
                        'pending_manager' => 'Menunggu Persetujuan Manager',
                        'pending_hr' => 'Menunggu Persetujuan HR',
                        'pending_director' => 'Menunggu Persetujuan Director',
                        'approved' => 'Disetujui',
                        'rejected_manager' => 'Ditolak oleh Manager',
                        'rejected_hr' => 'Ditolak oleh HR',
                        'rejected_director' => 'Ditolak oleh Director',
                        'cancel' => 'Dibatalkan',
                        default => $this->leaveRequest->status,
                    }" lg />
                </div>

                {{-- Information Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Type --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Cuti</label>
                        <div class="mt-1">
                            <x-badge :color="match ($this->leaveRequest->type) {
                                'sick' => 'red',
                                'annual' => 'blue',
                                'important' => 'purple',
                                'other' => 'gray',
                                default => 'gray',
                            }" :text="match ($this->leaveRequest->type) {
                                'sick' => 'Sakit',
                                'annual' => 'Tahunan',
                                'important' => 'Penting',
                                'other' => 'Lainnya',
                                default => ucfirst($this->leaveRequest->type),
                            }" />
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Durasi</label>
                        <p class="mt-1 text-base font-semibold text-gray-900 dark:text-white">
                            {{ $this->leaveRequest->getDurationInDays() }} hari kerja
                        </p>
                    </div>

                    {{-- Start Date --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                        <p class="mt-1 text-base text-gray-900 dark:text-white">
                            {{ $this->leaveRequest->start_date->format('d F Y') }}
                        </p>
                    </div>

                    {{-- End Date --}}
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Selesai</label>
                        <p class="mt-1 text-base text-gray-900 dark:text-white">
                            {{ $this->leaveRequest->end_date->format('d F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Alasan</label>
                    <p class="mt-1 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">
                        {{ $this->leaveRequest->reason }}</p>
                </div>

                {{-- Attachment --}}
                @if ($this->leaveRequest->attachment_path)
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Lampiran</label>
                        <div class="mt-1">
                            <a href="{{ Storage::url($this->leaveRequest->attachment_path) }}" target="_blank"
                                class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400">
                                <x-icon name="document-arrow-down" class="w-4 h-4" />
                                Download Lampiran
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Rejection Reason --}}
                @if ($this->leaveRequest->isRejected() && $this->leaveRequest->rejection_reason)
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                        <label class="text-sm font-semibold text-red-900 dark:text-red-100">Alasan Penolakan</label>
                        <p class="mt-1 text-sm text-red-800 dark:text-red-200">
                            {{ $this->leaveRequest->rejection_reason }}</p>
                    </div>
                @endif

                {{-- Approval Timeline --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Timeline Persetujuan</h4>
                    <div class="space-y-4">
                        {{-- Manager --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                @if ($this->leaveRequest->manager_approved_at)
                                    <div
                                        class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                        <x-icon name="check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                @else
                                    <div
                                        class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <x-icon name="clock" class="w-4 h-4 text-gray-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Manager</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($this->leaveRequest->manager_approved_at)
                                        Disetujui pada
                                        {{ $this->leaveRequest->manager_approved_at->format('d M Y H:i') }}
                                    @else
                                        Menunggu persetujuan
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- HR --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                @if ($this->leaveRequest->hr_approved_at)
                                    <div
                                        class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                        <x-icon name="check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                @else
                                    <div
                                        class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <x-icon name="clock" class="w-4 h-4 text-gray-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">HR</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($this->leaveRequest->hr_approved_at)
                                        Disetujui pada {{ $this->leaveRequest->hr_approved_at->format('d M Y H:i') }}
                                    @else
                                        Menunggu persetujuan
                                    @endif
                                </p>
                            </div>
                        </div>

                        {{-- Director --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                @if ($this->leaveRequest->director_approved_at)
                                    <div
                                        class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/20 flex items-center justify-center">
                                        <x-icon name="check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                                    </div>
                                @else
                                    <div
                                        class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                        <x-icon name="clock" class="w-4 h-4 text-gray-400" />
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Director</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($this->leaveRequest->director_approved_at)
                                        Disetujui pada
                                        {{ $this->leaveRequest->director_approved_at->format('d M Y H:i') }}
                                    @else
                                        Menunggu persetujuan
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-slot:footer>
                <div class="flex justify-end">
                    <x-button wire:click="$set('modal', false)" color="secondary">
                        Tutup
                    </x-button>
                </div>
            </x-slot:footer>
        @endif
    </x-modal>
</div>
