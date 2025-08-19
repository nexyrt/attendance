<div>
    <x-button text="Create New Office" wire:click="$toggle('modal')" sm x-on:click="initMapOnClick()" />

    <x-modal title="Create New Office" wire x-on:open="setTimeout(() => $refs.firstField.focus(), 250)"
        x-on:close="resetModalState()">
        <form id="office-create" wire:submit="save" class="space-y-4">
            <div>
                <x-input label="Office Name *" x-ref="firstField" wire:model="office.name" required />
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
                <div id="map-create" wire:ignore class="h-64 w-full rounded-lg border border-gray-300"></div>
                <p class="text-xs text-gray-500">Click on the map to select office location</p>
            </div>
        </form>

        <x-slot:footer>
            <x-button type="submit" form="office-create" loading="save">
                Save Office
            </x-button>
        </x-slot:footer>
    </x-modal>
</div>

<script>
    let mapCreate;
    let marker;
    let isMapInitialized = false;

    function initMapOnClick() {
        if (isMapInitialized) return;

        setTimeout(() => {
            const mapElement = document.getElementById('map-create');
            if (mapElement && typeof L !== 'undefined') {
                try {
                    // Default location (Samarinda)
                    const defaultLat = -0.5;
                    const defaultLng = 117.15;

                    mapCreate = L.map('map-create').setView([defaultLat, defaultLng], 13);

                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(mapCreate);

                    // Try to get user location
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const userLat = position.coords.latitude;
                                const userLng = position.coords.longitude;

                                // Update map view to user location
                                mapCreate.setView([userLat, userLng], 15);

                                console.log('Map centered on user location:', userLat, userLng);
                            },
                            function(error) {
                                console.log('Geolocation error:', error.message);
                                console.log('Using default location (Samarinda)');
                            }, {
                                enableHighAccuracy: true,
                                timeout: 10000,
                                maximumAge: 300000
                            }
                        );
                    } else {
                        console.log('Geolocation not supported, using default location');
                    }

                    mapCreate.on('click', function(e) {
                        const {
                            lat,
                            lng
                        } = e.latlng;

                        if (marker) {
                            mapCreate.removeLayer(marker);
                        }

                        marker = L.marker([lat, lng]).addTo(mapCreate);

                        @this.set('office.latitude', lat);
                        @this.set('office.longitude', lng);
                    });

                    isMapInitialized = true;
                    console.log('Map initialized on button click');

                } catch (error) {
                    console.error('Error initializing map:', error);
                }
            }
        }, 1000);
    }

    function resetModalState() {
        console.log('Resetting modal state...');

        // Reset map only
        if (mapCreate) {
            mapCreate.remove();
            mapCreate = null;
        }

        if (marker) {
            marker = null;
        }

        isMapInitialized = false;

        // Don't call Livewire reset here to avoid modal conflict
        console.log('Map reset complete');
    }

    // Listen for Livewire reset event
    document.addEventListener('livewire:init', () => {
        // Remove this listener since we handle reset manually
    });
</script>
