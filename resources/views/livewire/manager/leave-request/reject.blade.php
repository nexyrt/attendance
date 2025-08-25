<div>
    <x-modal :title="__('Tolak Pengajuan Cuti')" wire center size="2xl">
        @if ($leaveRequest)
            <div class="space-y-6">
                {{-- Request Summary --}}
                <x-card>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Karyawan</div>
                            <div class="font-medium text-gray-900 dark:text-white">
                                {{ $leaveRequest->user->name }}
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
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Periode</div>
                        <div class="text-gray-900 dark:text-white">
                            {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Alasan</div>
                        <p class="text-gray-700 dark:text-gray-300 text-sm">{{ $leaveRequest->reason }}</p>
                    </div>
                </x-card>

                {{-- Warning Alert --}}
                <x-alert color="red" icon="exclamation-triangle">
                    <strong>Perhatian!</strong> Penolakan pengajuan cuti tidak dapat dibatalkan. 
                    @if($leaveRequest->type === 'annual')
                        Saldo cuti tahunan akan dikembalikan ke karyawan.
                    @endif
                </x-alert>

                {{-- Rejection Form --}}
                <form id="reject-leave-{{ $leaveRequest->id }}" wire:submit="reject" class="space-y-4">
                    <div>
                        <x-textarea 
                            label="Alasan Penolakan *" 
                            wire:model="rejection_reason"
                            hint="Jelaskan alasan penolakan (minimal 10 karakter, maksimal 500 karakter)"
                            rows="4"
                            placeholder="Contoh: Jadwal cuti bertabrakan dengan proyek penting..."
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
                    icon="x-mark"
                >
                    Tolak Pengajuan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>