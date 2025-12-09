<div>
    <x-modal :title="__('Setujui Pengajuan Cuti')" wire center size="3xl">
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
                    </div>
                </x-card>

                {{-- Leave Dates --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Periode Cuti</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Mulai</div>
                            <div class="text-gray-900 dark:text-white">
                                {{ $leaveRequest->start_date->format('l, d F Y') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Berakhir</div>
                            <div class="text-gray-900 dark:text-white">
                                {{ $leaveRequest->end_date->format('l, d F Y') }}
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Reason --}}
                <x-card>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-3">Alasan</h4>
                    <p class="text-gray-700 dark:text-gray-300">{{ $leaveRequest->reason }}</p>
                </x-card>

                {{-- Approval Form --}}
                <form id="approve-leave-{{ $leaveRequest->id }}" wire:submit="approve" class="space-y-6">
                    {{-- Signature --}}
                    <div>
                        <x-signature 
                            label="Tanda Tangan Manager *" 
                            wire:model="signature"
                            hint="Silakan tanda tangan untuk menyetujui pengajuan cuti ini"
                            height="200"
                            clearable
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
                    form="approve-leave-{{ $leaveRequest?->id }}" 
                    color="green" 
                    loading="approve"
                    icon="check"
                >
                    Setujui Pengajuan
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>