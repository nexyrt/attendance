<div>
    <x-modal :title="__('Final Rejection - Pengajuan Cuti')" wire size="3xl">
        @if ($leaveRequest)
            <div class="space-y-6">
                {{-- Executive Warning --}}
                <x-card>
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-700">
                        <h4 class="font-bold text-red-900 dark:text-red-100 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Final Rejection Warning
                        </h4>
                        <p class="text-red-700 dark:text-red-300 text-sm">
                            Ini adalah keputusan final. Penolakan dari Direktur tidak dapat diubah dan akan mengakhiri proses persetujuan cuti.
                        </p>
                    </div>
                </x-card>

                {{-- Request Summary --}}
                <x-card>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Karyawan</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->user->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $leaveRequest->user->department?->name }}
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
                                {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Alasan Pengajuan</div>
                        <p class="text-gray-700 dark:text-gray-300">{{ $leaveRequest->reason }}</p>
                    </div>
                </x-card>

                {{-- Previous Approvals --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Persetujuan Sebelumnya</h4>
                    <div class="space-y-2">
                        @if($leaveRequest->manager_approved_at)
                            <div class="flex items-center text-sm">
                                <span class="text-green-600 mr-2">✅</span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    Manager: {{ $leaveRequest->manager?->name }} 
                                    ({{ $leaveRequest->manager_approved_at->format('d M Y H:i') }})
                                </span>
                            </div>
                        @endif

                        @if($leaveRequest->hr_approved_at)
                            <div class="flex items-center text-sm">
                                <span class="text-green-600 mr-2">✅</span>
                                <span class="text-gray-700 dark:text-gray-300">
                                    HR: {{ $leaveRequest->hr?->name }} 
                                    ({{ $leaveRequest->hr_approved_at->format('d M Y H:i') }})
                                </span>
                            </div>
                        @endif
                    </div>
                </x-card>

                {{-- Impact Warning --}}
                <x-alert color="amber" icon="exclamation-triangle">
                    <strong>Dampak Penolakan:</strong>
                    <ul class="mt-2 text-sm list-disc list-inside">
                        <li>Pengajuan cuti akan berakhir dengan status "Ditolak Direktur"</li>
                        @if($leaveRequest->type === 'annual')
                            <li>Saldo cuti tahunan akan dikembalikan ke karyawan</li>
                        @endif
                        <li>Karyawan akan mendapat notifikasi penolakan dengan alasan yang Anda berikan</li>
                        <li>Keputusan ini bersifat final dan tidak dapat diubah</li>
                    </ul>
                </x-alert>

                {{-- Rejection Form --}}
                <form id="reject-leave-{{ $leaveRequest->id }}" wire:submit="reject" class="space-y-4">
                    <div>
                        <x-textarea 
                            label="Alasan Penolakan (Executive Decision) *" 
                            wire:model="rejection_reason"
                            hint="Berikan alasan penolakan yang jelas dari perspektif strategis perusahaan (minimal 10 karakter)"
                            rows="5"
                            placeholder="Contoh: Periode cuti bertabrakan dengan periode strategis perusahaan yang memerlukan kehadiran seluruh tim..."
                            required
                        />
                    </div>
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
                    form="reject-leave-{{ $leaveRequest?->id }}" 
                    color="red" 
                    loading="reject"
                    icon="x-circle"
                    size="lg"
                >
                    FINAL REJECTION
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>