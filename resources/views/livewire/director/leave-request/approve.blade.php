<div>
    <x-modal :title="__('Final Approval - Pengajuan Cuti')" wire size="4xl">
        @if ($leaveRequest)
            <div class="space-y-6">
                {{-- Executive Summary --}}
                <x-card>
                    <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                        <h4 class="font-bold text-purple-900 dark:text-purple-100 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Final Approval Required
                        </h4>
                        <p class="text-purple-700 dark:text-purple-300 text-sm">
                            Pengajuan cuti ini telah melewati persetujuan Manager dan HR. Persetujuan Direktur akan menjadi keputusan final.
                        </p>
                    </div>
                </x-card>

                {{-- Employee & Request Summary --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Ringkasan Pengajuan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Karyawan</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->user->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $leaveRequest->user->department?->name }} - {{ ucfirst($leaveRequest->user->role) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Jenis Cuti</div>
                            <x-badge :color="match ($leaveRequest->type) {
                                'sick' => 'red',
                                'annual' => 'blue',
                                'important' => 'orange',
                                'other' => 'gray',
                            }" :text="match ($leaveRequest->type) {
                                'sick' => 'Sakit',
                                'annual' => 'Tahunan',
                                'important' => 'Penting',
                                'other' => 'Lainnya',
                            }" />
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Durasi</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->getDurationInDays() }} hari
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Periode</div>
                            <div class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ $leaveRequest->start_date->format('d M') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Reason --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Alasan Pengajuan</h4>
                    <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
                        <p class="text-gray-700 dark:text-gray-300">{{ $leaveRequest->reason }}</p>
                    </div>
                </x-card>

                {{-- Previous Approvals --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-4">Riwayat Persetujuan</h4>
                    <div class="space-y-3">
                        {{-- Manager Approval --}}
                        @if($leaveRequest->manager_approved_at)
                            <div class="flex items-center space-x-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">Manager: {{ $leaveRequest->manager?->name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Disetujui pada {{ $leaveRequest->manager_approved_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- HR Approval --}}
                        @if($leaveRequest->hr_approved_at)
                            <div class="flex items-center space-x-3 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">HR: {{ $leaveRequest->hr?->name }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Disetujui pada {{ $leaveRequest->hr_approved_at->format('d M Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- Final Approval Form --}}
                <form id="approve-leave-{{ $leaveRequest->id }}" wire:submit="approve" class="space-y-6">
                    <x-card>
                        <h4 class="font-medium text-gray-900 dark:text-white mb-4">Tanda Tangan Final Approval</h4>
                        <div>
                            <x-signature 
                                label="Tanda Tangan Direktur *" 
                                wire:model="signature"
                                hint="Tanda tangan Anda sebagai persetujuan final pengajuan cuti ini"
                                height="200"
                                clearable
                                required
                            />
                        </div>
                    </x-card>
                </form>
            </div>
        @endif

        <x-slot:footer>
            <div class="flex justify-between w-full">
                <x-button color="gray" wire:click="$set('modal', false)">
                    Batal
                </x-button>
                <x-button 
                    type="submit" 
                    form="approve-leave-{{ $leaveRequest?->id }}" 
                    color="green" 
                    loading="approve"
                    icon="check-circle"
                    size="lg"
                >
                    FINAL APPROVAL
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>