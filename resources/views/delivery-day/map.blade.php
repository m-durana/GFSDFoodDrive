<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Live Delivery Map
            </h2>
            <a href="{{ route('delivery.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                Back to Delivery Day
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Legend -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-3 mb-4">
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Legend:</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span> Pending</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-orange-500 inline-block"></span> In Transit</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span> Delivered</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-500 inline-block"></span> Picked Up</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-purple-600 inline-block border-2 border-white shadow"></span> Volunteer</span>
                    <span class="ml-auto text-gray-400" id="last-update">Updating...</span>
                </div>
            </div>

            <!-- Map container -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden relative" style="height: 70vh;">
                <div id="map" style="width: 100%; height: 100%;"></div>
                <div id="no-data-overlay" class="hidden absolute inset-0 flex items-center justify-center bg-gray-100/80 dark:bg-gray-800/80 z-[1000]">
                    <div class="text-center p-8">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <p class="text-lg font-medium text-gray-600 dark:text-gray-300">No geocoded families</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Families need addresses first, then geocode them from<br><a href="{{ route('santa.settings') }}" class="text-blue-500 hover:underline">Settings &rarr; Address Geocoding</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaflet CSS/JS from CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // Initialize map centered on Granite Falls, WA
        const map = L.map('map').setView([48.0849, -121.9683], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        const statusColors = {
            pending: '#EAB308',
            in_transit: '#F97316',
            delivered: '#22C55E',
            picked_up: '#3B82F6',
        };

        let familyMarkers = [];
        let volunteerMarkers = [];

        function createFamilyIcon(status) {
            const color = statusColors[status] || '#6B7280';
            return L.divIcon({
                className: '',
                html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7],
            });
        }

        function createVolunteerIcon() {
            return L.divIcon({
                className: '',
                html: '<div style="background:#9333EA;width:18px;height:18px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);"></div>',
                iconSize: [18, 18],
                iconAnchor: [9, 9],
            });
        }

        function updateMap() {
            fetch('{{ route("delivery.mapData") }}')
                .then(r => r.json())
                .then(data => {
                    // Clear existing
                    familyMarkers.forEach(m => map.removeLayer(m));
                    volunteerMarkers.forEach(m => map.removeLayer(m));
                    familyMarkers = [];
                    volunteerMarkers = [];

                    // Add family markers
                    data.families.forEach(f => {
                        const marker = L.marker([f.lat, f.lng], { icon: createFamilyIcon(f.status) })
                            .bindPopup(`<strong>#${f.number} ${f.name}</strong><br>${f.address}<br>Status: ${f.status}<br>Team: ${f.team || 'Unassigned'}`)
                            .addTo(map);
                        familyMarkers.push(marker);
                    });

                    // Add volunteer markers
                    data.volunteers.forEach(v => {
                        const marker = L.marker([v.lat, v.lng], { icon: createVolunteerIcon() })
                            .bindPopup(`<strong>${v.name}</strong><br>Updated ${v.updated}`)
                            .addTo(map);
                        volunteerMarkers.push(marker);
                    });

                    // Fit bounds if we have data
                    const allPoints = [...data.families.map(f => [f.lat, f.lng]), ...data.volunteers.map(v => [v.lat, v.lng])];
                    if (allPoints.length > 0 && !window._boundsSet) {
                        map.fitBounds(allPoints, { padding: [30, 30] });
                        window._boundsSet = true;
                    }

                    // Show empty state if no data
                    const overlay = document.getElementById('no-data-overlay');
                    if (data.families.length === 0 && data.volunteers.length === 0) {
                        overlay.classList.remove('hidden');
                    } else {
                        overlay.classList.add('hidden');
                    }

                    document.getElementById('last-update').textContent = 'Updated ' + new Date().toLocaleTimeString();
                })
                .catch(err => {
                    document.getElementById('last-update').textContent = 'Update failed';
                });
        }

        // Initial load + poll every 10 seconds
        updateMap();
        setInterval(updateMap, 10000);
    </script>
</x-app-layout>
