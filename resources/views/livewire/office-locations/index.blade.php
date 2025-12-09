<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Office Management</h1>
        <livewire:office-locations.create @created="$refresh" />
    </div>

    <x-table :headers="$this->getHeaders()" :sort="$sort" :rows="$this->getRows()" paginate filter loading>
        @interact('column_coordinates', $row)
            <div class="text-xs text-gray-500 dark:text-gray-400">
                <div>{{ number_format($row->latitude, 6) }}</div>
                <div>{{ number_format($row->longitude, 6) }}</div>
            </div>
        @endinteract

        @interact('column_action', $row)
            <div class="flex gap-1">
                <x-button.circle icon="pencil" wire:click="$dispatch('load::office', { office: '{{ $row->id }}'})" />
                <livewire:office-locations.delete :office="$row" :key="uniqid('', true)" @deleted="$refresh" />
            </div>
        @endinteract
    </x-table>

    <livewire:office-locations.update @updated="$refresh" />
</div>
