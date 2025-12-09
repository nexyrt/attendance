<div>
    {{-- Form Card Trigger --}}
    <div class="rounded-xl border border-border bg-card">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-foreground mb-2">Ajukan Cuti / Izin</h3>
            <p class="text-sm text-muted-foreground mb-4">Buat pengajuan cuti atau izin baru</p>
            <x-button wire:click="$toggle('modal')" color="primary" class="w-full">
                <x-icon name="paper-airplane" class="w-4 h-4 mr-2" />
                Kirim Pengajuan
            </x-button>
        </div>
    </div>

    <x-modal wire="modal" size="xl" center persistent>
        {{-- Custom Title --}}
        <x-slot:title>
            <div class="flex items-center gap-4 my-3">
                <div class="h-12 w-12 bg-primary-50 dark:bg-primary-900/20 rounded-xl flex items-center justify-center">
                    <x-icon name="document-plus" class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ajukan Cuti</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Isi form pengajuan cuti</p>
                </div>
            </div>
        </x-slot:title>

        {{-- Form --}}
        <form id="leave-create" wire:submit="save" class="space-y-6">
            {{-- Section: Informasi Cuti --}}
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-gray-600 pb-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Informasi Cuti</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Detail pengajuan cuti Anda</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Type --}}
                    <x-select.native wire:model="type" label="Jenis Cuti *" :options="[
                        ['label' => 'Sakit', 'value' => 'sick'],
                        ['label' => 'Cuti Tahunan', 'value' => 'annual'],
                        ['label' => 'Kepentingan Penting', 'value' => 'important'],
                        ['label' => 'Lainnya', 'value' => 'other'],
                    ]" placeholder="Pilih jenis cuti..." />

                    {{-- Attachment --}}
                    <x-upload wire:model="attachment" label="Lampiran" 
                        hint="PDF, JPG, PNG (Max 2MB)" 
                        accept="application/pdf,image/jpeg,image/png"
                        delete />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Start Date --}}
                    <x-date wire:model="start_date" label="Tanggal Mulai *" 
                        placeholder="Pilih tanggal mulai"
                        :min-date="now()->format('Y-m-d')" />

                    {{-- End Date --}}
                    <x-date wire:model="end_date" label="Tanggal Selesai *" 
                        placeholder="Pilih tanggal selesai"
                        :min-date="now()->format('Y-m-d')" />
                </div>

                {{-- Reason --}}
                <x-textarea wire:model="reason" label="Alasan *" 
                    placeholder="Jelaskan alasan pengajuan cuti Anda..."
                    rows="4"
                    hint="Minimal 10 karakter" />
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex gap-3">
                    <x-icon name="information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-semibold mb-1">Catatan Penting:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pengajuan cuti akan melalui proses approval</li>
                            <li>Pastikan saldo cuti Anda mencukupi</li>
                            <li>Upload lampiran jika diperlukan (surat dokter, undangan, dll)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </form>

        {{-- Footer --}}
        <x-slot:footer>
            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <x-button wire:click="$set('modal', false)" color="secondary" outline
                    class="w-full sm:w-auto order-2 sm:order-1">
                    Batal
                </x-button>
                <x-button type="submit" form="leave-create" color="primary" icon="paper-airplane" loading="save"
                    class="w-full sm:w-auto order-1 sm:order-2">
                    Ajukan Cuti
                </x-button>
            </div>
        </x-slot:footer>
    </x-modal>
</div>