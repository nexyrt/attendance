<div>
    <x-modal :title="__('Update Office: #:id', ['id' => $office?->id])" wire>
        <form id="office-update-{{ $office?->id }}" wire:submit="save" class="space-y-4">
            <div>
                <x-input label="Office Name *" wire:model="office.name" required />
            </div>

            <div>
                <x-input label="Address" wire:model="office.address" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input label="Latitude *" wire:model="office.latitude" type="number" step="any" readonly />
                </div>
                <div>
                    <x-input label="Longitude *" wire:model="office.longitude" type="number" step="any" readonly />
                </div>
            </div>

            <div>
                <x-input label="Radius (meters) *" wire:model="office.radius" type="number" min="1"
                    max="10000" placeholder="e.g., 100" />
            </div>

            <!-- Map Container -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Location on Map *</label>
                <div id="map-update" wire:ignore class="h-64 w-full rounded-lg border border-gray-300"></div>
                <p class="text-xs text-gray-500">Click on the map or drag marker to change office location</p>
            </div>
        </form>

        <x-slot:footer>
            <x-button type="submit" form="office-update-{{ $office?->id }}" loading="save">
                Update Office
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    let updateMap;
    let updateMarker;

    // Listen untuk data office dari Livewire
    document.addEventListener('livewire:init', () => {
        Livewire.on('initUpdateMap', (event) => {
            const {
                lat,
                lng
            } = event[0];

            setTimeout(() => {
                // Hapus map lama jika ada
                if (updateMap) {
                    updateMap.remove();
                }

                // Buat map baru dengan lokasi office
                updateMap = L.map('map-update').setView([lat, lng], 15);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(updateMap);

                // Marker bisa di-drag
                updateMarker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(updateMap);

                // Update koordinat saat marker di-drag atau map di-klik
                updateMarker.on('dragend', (e) => {
                    const pos = updateMarker.getLatLng();
                    @this.set('office.latitude', pos.lat);
                    @this.set('office.longitude', pos.lng);
                });

                updateMap.on('click', (e) => {
                    updateMarker.setLatLng(e.latlng);
                    @this.set('office.latitude', e.latlng.lat);
                    @this.set('office.longitude', e.latlng.lng);
                });

            }, 1000);
        });
    });

    // Cleanup saat modal ditutup
    document.addEventListener('modal-close', () => {
        if (updateMap) {
            updateMap.remove();
            updateMap = null;
            updateMarker = null;
        }
    });
</script>
