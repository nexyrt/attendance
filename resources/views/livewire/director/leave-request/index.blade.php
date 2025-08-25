<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Final Approval - Cuti Karyawan</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <x-card class="text-center">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ $this->statusStats['total'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Total Pengajuan</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                {{ $this->statusStats['pending_director'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Menunggu Direktur</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ $this->statusStats['approved_by_director'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Disetujui Direktur</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                {{ $this->statusStats['final_approved'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Final Approved</div>
        </x-card>

        <x-card class="text-center">
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                {{ $this->statusStats['rejected'] }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">Ditolak</div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <x-input label="Cari" wire:model.live.debounce.500ms="search"
                    placeholder="Nama karyawan atau alasan..." />
            </div>

            <div>
                <x-select.styled label="Karyawan" wire:model.live="employee_id" 
                    :options="$this->employees" searchable />
            </div>

            <div>
                <x-select.styled label="Departemen" wire:model.live="department_id" 
                    :options="$this->departments" />
            </div>

            <div>
                <x-select.native label="Status" wire:model.live="status" 
                    :options="collect($this->getStatusOptions())
                        ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                        ->values()" />
            </div>

            <div>
                <x-select.native label="Jenis Cuti" wire:model.live="type" 
                    :options="collect($this->getTypeOptions())
                        ->map(fn($label, $value) => ['label' => $label, 'value' => $value])
                        ->values()" />
            </div>

            <div class="flex items-end">
                <x-button color="gray" 
                    wire:click="$set('search', null); $set('employee_id', null); $set('department_id', null); $set('status', null); $set('type', null)"
                    class="w-full">
                    Reset
                </x-button>
            </div>
        </div>
    </x-card>

    {{-- Leave Requests Table --}}
    <x-card>
        <x-table :$headers :$sort :rows="$this->rows" paginate filter loading :quantity="[5, 10, 15, 25]">
            @interact('column_employee', $row)
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $row->user->name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ ucfirst($row->user->role) }}
                    </div>
                </div>
            @endinteract

            @interact('column_department', $row)
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ $row->user->department?->name ?? '-' }}
                </span>
            @endinteract

            @interact('column_type', $row)
                <x-badge :color="match ($row->type) {
                    'sick' => 'red',
                    'annual' => 'blue',
                    'important' => 'orange',
                    'other' => 'gray',
                }" :text="match ($row->type) {
                    'sick' => 'Sakit',
                    'annual' => 'Tahunan',
                    'important' => 'Penting',
                    'other' => 'Lainnya',
                }" />
            @endinteract

            @interact('column_dates', $row)
                <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ $row->start_date->format('d M') }} - {{ $row->end_date->format('d M Y') }}
                    </div>
                </div>
            @endinteract

            @interact('column_duration', $row)
                <span class="font-medium text-gray-900 dark:text-white">
                    {{ $row->getDurationInDays() }} hari
                </span>
            @endinteract

            @interact('column_approvals', $row)
                <div class="flex space-x-1">
                    {{-- Manager --}}
                    @if($row->manager_approved_at)
                        <span title="Manager: {{ $row->manager?->name }}">✅</span>
                    @else
                        <span title="Manager: Belum disetujui">⏳</span>
                    @endif

                    {{-- HR --}}
                    @if($row->hr_approved_at)
                        <span title="HR: {{ $row->hr?->name }}">✅</span>
                    @elseif($row->manager_approved_at)
                        <span title="HR: Menunggu">⏳</span>
                    @else
                        <span title="HR: Belum dapat diproses">⭕</span>
                    @endif

                    {{-- Director --}}
                    @if($row->director_approved_at)
                        <span title="Direktur: {{ $row->director?->name }}">✅</span>
                    @elseif($row->status === 'pending_director')
                        <span title="Direktur: Menunggu persetujuan">⏳</span>
                    @else
                        <span title="Direktur: Belum dapat diproses">⭕</span>
                    @endif
                </div>
            @endinteract

            @interact('column_status', $row)
                <x-badge :color="match ($row->status) {
                    'approved' => 'green',
                    'rejected_manager', 'rejected_hr', 'rejected_director' => 'red',
                    'cancel' => 'gray',
                    'pending_manager' => 'yellow',
                    'pending_hr' => 'blue',
                    'pending_director' => 'purple',
                    default => 'gray',
                }" :text="match ($row->status) {
                    'approved' => 'Final Approved',
                    'rejected_manager' => 'Ditolak Manager',
                    'rejected_hr' => 'Ditolak HR',
                    'rejected_director' => 'Ditolak Direktur',
                    'cancel' => 'Dibatalkan',
                    'pending_manager' => 'Menunggu Manager',
                    'pending_hr' => 'Menunggu HR',
                    'pending_director' => 'Menunggu Direktur',
                    default => 'Unknown',
                }" />
            @endinteract

            @interact('column_created_at', $row)
                <div>
                    <div class="text-gray-900 dark:text-white">
                        {{ $row->created_at->format('d M Y') }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $row->created_at->format('H:i') }}
                    </div>
                </div>
            @endinteract

            @interact('column_action', $row)
                <div class="flex gap-1">
                    <x-button.circle icon="eye" color="blue"
                        wire:click="$dispatch('load::leave-request-detail', { 'leaveRequest' : '{{ $row->id }}'})"
                        title="Lihat Detail" />

                    @if($row->status === App\Models\LeaveRequest::STATUS_PENDING_DIRECTOR)
                        <x-button.circle icon="check" color="green"
                            wire:click="$dispatch('load::approve-leave-request', { 'leaveRequest' : '{{ $row->id }}'})"
                            title="Final Approval" />
                        <x-button.circle icon="x-mark" color="red"
                            wire:click="$dispatch('load::reject-leave-request', { 'leaveRequest' : '{{ $row->id }}'})"
                            title="Final Reject" />
                    @endif
                </div>
            @endinteract
        </x-table>
    </x-card>

    {{-- Modals --}}
    <livewire:director.leave-request.detail @updated="$refresh" />
    <livewire:director.leave-request.approve @approved="$refresh" />
    <livewire:director.leave-request.reject @rejected="$refresh" />
</div>