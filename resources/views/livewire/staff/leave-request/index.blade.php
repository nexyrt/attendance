<div class="space-y-6">
    {{-- Page Title (Desktop only) --}}
    <div class="hidden lg:block">
        <h1 class="text-2xl font-bold text-foreground">Cuti & Izin</h1>
        <p class="text-muted-foreground">Kelola pengajuan cuti dan izin Anda</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ($this->stats as $stat)
            <div class="rounded-xl border border-border bg-card p-6">
                <div class="flex items-center gap-3">
                    <div
                        class="rounded-lg p-2 @if ($stat['color'] === 'primary') bg-primary/10 @elseif($stat['color'] === 'amber') bg-amber-500/10 @elseif($stat['color'] === 'green') bg-emerald-500/10 @elseif($stat['color'] === 'red') bg-red-500/10 @endif">
                        <x-icon name="{{ $stat['icon'] }}"
                            class="h-5 w-5 @if ($stat['color'] === 'primary') text-primary @elseif($stat['color'] === 'amber') text-amber-600 @elseif($stat['color'] === 'green') text-emerald-600 @elseif($stat['color'] === 'red') text-red-600 @endif" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-foreground">{{ $stat['value'] }}</p>
                        <p class="text-xs text-muted-foreground">{{ $stat['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Main Content Grid --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left Column - Form and Balance --}}
        <div class="space-y-6 order-2 lg:order-1">
            {{-- Leave Request Form --}}
            <livewire:staff.leave-request.create @created="$refresh" />

            {{-- Leave Balance Card --}}
            @if ($this->leaveBalance)
                <div class="rounded-2xl bg-card border border-border overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-blue-500/10 p-2.5">
                                    <x-icon name="calendar" class="h-5 w-5 text-blue-600" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-foreground">Sisa Cuti</h3>
                                    <p class="text-xs text-muted-foreground">Tahun {{ $this->leaveBalance->year }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Main balance display --}}
                        <div class="text-center mb-6">
                            <div class="inline-flex items-baseline">
                                <span
                                    class="text-5xl font-bold text-foreground">{{ $this->leaveBalance->remaining_balance }}</span>
                                <span class="text-lg text-muted-foreground ml-1">hari</span>
                            </div>
                            <p class="text-sm text-muted-foreground mt-1">tersisa dari
                                {{ $this->leaveBalance->total_balance }} hari</p>
                        </div>

                        {{-- Progress bar --}}
                        <div class="space-y-2 mb-6">
                            <div class="flex justify-between text-xs">
                                <span class="text-muted-foreground">Terpakai</span>
                                <span class="font-medium text-foreground">{{ $this->leaveBalance->used_balance }}
                                    hari</span>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all"
                                    style="width: {{ ($this->leaveBalance->used_balance / $this->leaveBalance->total_balance) * 100 }}%">
                                </div>
                            </div>
                        </div>

                        {{-- Stats grid --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-muted/50 p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-icon name="arrow-trending-up" class="h-3.5 w-3.5 text-emerald-600" />
                                    <span class="text-xs text-muted-foreground">Jatah Tahunan</span>
                                </div>
                                <p class="text-lg font-semibold text-foreground">
                                    {{ $this->leaveBalance->total_balance }} hari</p>
                            </div>
                            <div class="rounded-lg bg-muted/50 p-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <x-icon name="clock" class="h-3.5 w-3.5 text-amber-600" />
                                    <span class="text-xs text-muted-foreground">Terpakai</span>
                                </div>
                                <p class="text-lg font-semibold text-foreground">{{ $this->leaveBalance->used_balance }}
                                    hari</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Column - History Table --}}
        <div class="lg:col-span-2 order-1 lg:order-2">
            <div class="rounded-xl border border-border bg-card">
                {{-- Card Header --}}
                <div class="p-6 border-b border-border">
                    <h3 class="text-lg font-semibold text-foreground">Riwayat Pengajuan Cuti</h3>
                </div>

                {{-- Tabs --}}
                <div class="border-b border-border">
                    <div class="flex overflow-x-auto px-6">
                        <button wire:click="setActiveTab('all')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'all' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground' }}">
                            Semua ({{ $this->tabCounts['all'] }})
                        </button>
                        <button wire:click="setActiveTab('pending')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'pending' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground' }}">
                            Menunggu ({{ $this->tabCounts['pending'] }})
                        </button>
                        <button wire:click="setActiveTab('approved')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'approved' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground' }}">
                            Disetujui ({{ $this->tabCounts['approved'] }})
                        </button>
                        <button wire:click="setActiveTab('rejected')"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ $activeTab === 'rejected' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground' }}">
                            Ditolak ({{ $this->tabCounts['rejected'] }})
                        </button>
                    </div>
                </div>

                {{-- Table Content --}}
                <div class="p-6">
                    <div class="rounded-md border border-border overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th
                                        class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-sm">
                                        Jenis</th>
                                    <th
                                        class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-sm">
                                        Tanggal</th>
                                    <th
                                        class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-sm">
                                        Durasi</th>
                                    <th
                                        class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-sm">
                                        Alasan</th>
                                    <th
                                        class="h-12 px-4 text-left align-middle font-medium text-muted-foreground text-sm">
                                        Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->rows as $row)
                                    <tr class="border-b border-border transition-colors hover:bg-muted/50">
                                        <td class="p-4 align-middle">
                                            <span class="font-medium text-foreground">
                                                {{ match ($row->type) {
                                                    'sick' => 'Cuti Sakit',
                                                    'annual' => 'Cuti Tahunan',
                                                    'important' => 'Cuti Penting',
                                                    'other' => 'Lainnya',
                                                    default => ucfirst($row->type),
                                                } }}
                                            </span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <div class="text-sm text-foreground">
                                                {{ $row->start_date->format('d M') }} -
                                                {{ $row->end_date->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span class="text-sm text-foreground">{{ $row->getDurationInDays() }}
                                                hari</span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span
                                                class="text-sm text-foreground max-w-[200px] truncate inline-block">{{ $row->reason }}</span>
                                        </td>
                                        <td class="p-4 align-middle">
                                            <span
                                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset 
                                                {{ match (true) {
                                                    $row->isPendingManager(),
                                                    $row->isPendingHR(),
                                                    $row->isPendingDirector()
                                                        => 'bg-amber-500/20 text-amber-600 ring-amber-500/30',
                                                    $row->isApproved() => 'bg-emerald-500/20 text-emerald-600 ring-emerald-500/30',
                                                    $row->isRejected() => 'bg-red-500/20 text-red-600 ring-red-500/30',
                                                    $row->isCancelled() => 'bg-muted text-muted-foreground ring-border',
                                                    default => 'bg-muted text-muted-foreground ring-border',
                                                } }}">
                                                {{ match ($row->status) {
                                                    'pending_manager' => 'Menunggu Manager',
                                                    'pending_hr' => 'Menunggu HR',
                                                    'pending_director' => 'Menunggu Direktur',
                                                    'approved' => 'Disetujui',
                                                    'rejected_manager' => 'Ditolak Manager',
                                                    'rejected_hr' => 'Ditolak HR',
                                                    'rejected_director' => 'Ditolak Direktur',
                                                    'cancel' => 'Dibatalkan',
                                                    default => $row->status,
                                                } }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-muted-foreground">
                                            Belum ada pengajuan cuti
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    @if ($this->rows->hasPages())
                        <div class="mt-4">
                            {{ $this->rows->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Show Modal --}}
    <livewire:staff.leave-request.show />
</div>
