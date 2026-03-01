<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Live Delivery Map
            </h2>
            <a href="{{ route('delivery.index') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                Back to Dispatch
            </a>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-4">
                <!-- Filter sidebar -->
                <div class="w-56 shrink-0 space-y-3" id="sidebar">
                    <!-- Legend -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</h4>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-yellow-500" value="pending" checked>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span> Pending
                            </label>
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-orange-500" value="in_transit" checked>
                                <span class="w-3 h-3 rounded-full bg-orange-500"></span> In Transit
                            </label>
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-green-500" value="delivered" checked>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span> Delivered
                            </label>
                        </div>
                    </div>

                    <!-- Team filters -->
                    @if($teams->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Teams</h4>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" class="team-filter rounded" value="" checked>
                                    <span class="w-3 h-3 rounded-full bg-gray-400"></span> All / Unassigned
                                </label>
                                @foreach($teams as $team)
                                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" class="team-filter rounded" value="{{ $team->id }}" checked>
                                        <span class="w-3 h-3 rounded-full" style="background: {{ $team->color ?? '#6b7280' }}"></span>
                                        {{ $team->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Route filters -->
                    @if($routes->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Routes</h4>
                            <div class="space-y-1.5">
                                @foreach($routes as $route)
                                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" class="route-filter rounded" value="{{ $route->id }}" checked>
                                        {{ $route->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                        <label class="flex items-center gap-2 text-xs cursor-pointer">
                            <input type="checkbox" id="showRouteLines" class="rounded" checked>
                            Show route lines
                        </label>
                        <label class="flex items-center gap-2 text-xs cursor-pointer mt-1.5">
                            <input type="checkbox" id="showDrivers" class="rounded" checked>
                            Show drivers
                        </label>
                    </div>

                    <div class="text-xs text-gray-400 dark:text-gray-500 px-1" id="last-update">Updating...</div>
                </div>

                <!-- Map -->
                <div class="flex-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden relative" style="height: 75vh;">
                        <div id="map" style="width: 100%; height: 100%;"></div>
                        <div id="no-data-overlay" class="hidden absolute inset-0 flex items-center justify-center bg-gray-100/80 dark:bg-gray-800/80 z-[1000]">
                            <div class="text-center p-8">
                                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">No geocoded families</p>
                                <p class="text-sm text-gray-400 mt-2">
                                    <a href="{{ route('santa.settings') }}" class="text-blue-500 hover:underline">Settings &rarr; Geocoding</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 z-[2000] hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const map = L.map('map').setView([48.0849, -121.9683], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors', maxZoom: 19,
        }).addTo(map);

        const statusColors = { pending: '#EAB308', in_transit: '#F97316', delivered: '#22C55E' };
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        let familyMarkers = [], driverMarkers = [], volunteerMarkers = [], routeLines = [];
        let boundsSet = false;

        function getActiveStatuses() {
            return [...document.querySelectorAll('.status-filter:checked')].map(el => el.value);
        }
        function getActiveTeams() {
            return [...document.querySelectorAll('.team-filter:checked')].map(el => el.value);
        }
        function getActiveRoutes() {
            return [...document.querySelectorAll('.route-filter:checked')].map(el => el.value);
        }

        function createFamilyIcon(status) {
            const color = statusColors[status] || '#6B7280';
            return L.divIcon({
                className: '',
                html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                iconSize: [14, 14], iconAnchor: [7, 7],
            });
        }

        function createVolunteerIcon(initial) {
            return L.divIcon({
                className: '',
                html: `<div style="background:#9333EA;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);color:white;font-size:11px;font-weight:bold;display:flex;align-items:center;justify-content:center;">${initial}</div>`,
                iconSize: [24, 24], iconAnchor: [12, 12],
            });
        }

        const carSvg = `<svg viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>`;
        function createDriverIcon(color = '#2563eb') {
            return L.divIcon({
                className: '',
                html: `<div style="background:${color};border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 8px rgba(0,0,0,0.4);animation:pulse 2s ease-in-out infinite;">${carSvg}</div>`,
                iconSize: [32, 32], iconAnchor: [16, 16],
            });
        }

        function showToast(msg, color = 'green') {
            const t = document.getElementById('toast');
            const inner = t.querySelector('div');
            inner.className = `bg-${color}-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium`;
            inner.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        function familyPopup(f) {
            const phone = f.phone ? `<a href="tel:${f.phone}" class="text-blue-600">${f.phone}</a><br>` : '';
            const statusLabel = f.status.replace('_', ' ');
            const markBtn = (f.status !== 'delivered')
                ? `<button onclick="markDelivered(${f.id}, this)" style="margin-top:6px;padding:3px 10px;background:#16a34a;color:white;border:none;border-radius:4px;font-size:12px;cursor:pointer;">Mark Delivered</button>`
                : '';
            return `<strong>#${f.number} ${f.name}</strong><br>${f.address}<br>${phone}Status: <em>${statusLabel}</em>${markBtn}`;
        }

        window.markDelivered = function(familyId, btn) {
            btn.disabled = true;
            btn.textContent = '...';
            fetch(`/delivery-day/${familyId}/status-ajax`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ delivery_status: 'delivered' }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    showToast('Marked delivered!');
                    updateMap();
                }
            })
            .catch(() => { btn.disabled = false; btn.textContent = 'Mark Delivered'; });
        };

        function updateMap() {
            const statuses = getActiveStatuses();
            const teamIds = getActiveTeams();
            const routeIds = getActiveRoutes();
            const showLines = document.getElementById('showRouteLines')?.checked ?? true;
            const showDrivers = document.getElementById('showDrivers')?.checked ?? true;

            fetch('{{ route("delivery.mapData") }}')
                .then(r => r.json())
                .then(data => {
                    // Clear
                    familyMarkers.forEach(m => map.removeLayer(m));
                    driverMarkers.forEach(m => map.removeLayer(m));
                    volunteerMarkers.forEach(m => map.removeLayer(m));
                    routeLines.forEach(l => map.removeLayer(l));
                    familyMarkers = []; driverMarkers = []; volunteerMarkers = []; routeLines = [];

                    const bounds = [];

                    // Families
                    data.families.forEach(f => {
                        if (!statuses.includes(f.status)) return;
                        const fTeam = f.team_id ? String(f.team_id) : '';
                        if (!teamIds.includes(fTeam) && !(fTeam === '' && teamIds.includes(''))) return;

                        const marker = L.marker([f.lat, f.lng], { icon: createFamilyIcon(f.status) })
                            .bindPopup(familyPopup(f))
                            .addTo(map);
                        familyMarkers.push(marker);
                        bounds.push([f.lat, f.lng]);
                    });

                    // Drivers
                    if (showDrivers && data.drivers) {
                        data.drivers.forEach(v => {
                            const routeColor = data.routes?.find(r => r.id === v.route_id)?.color || '#2563eb';
                            const marker = L.marker([v.lat, v.lng], { icon: createDriverIcon(routeColor) })
                                .bindPopup(`<strong>${v.name}</strong><br>${v.updated}`)
                                .addTo(map);
                            driverMarkers.push(marker);
                            bounds.push([v.lat, v.lng]);
                        });
                    }

                    // Legacy volunteer markers
                    if (showDrivers && data.volunteers) {
                        data.volunteers.forEach(v => {
                            const marker = L.marker([v.lat, v.lng], { icon: createVolunteerIcon(v.initial) })
                                .bindPopup(`<strong>${v.name}</strong><br>Updated ${v.updated}`)
                                .addTo(map);
                            volunteerMarkers.push(marker);
                            bounds.push([v.lat, v.lng]);
                        });
                    }

                    // Route polylines
                    if (showLines && data.routes) {
                        data.routes.forEach(r => {
                            if (!routeIds.includes(String(r.id))) return;
                            if (r.polyline.length < 2) return;
                            const line = L.polyline(r.polyline, {
                                color: r.color || '#dc2626',
                                weight: 2, opacity: 0.6, dashArray: '6,8',
                            }).addTo(map);
                            routeLines.push(line);
                        });
                    }

                    // Fit bounds
                    if (bounds.length > 0 && !boundsSet) {
                        map.fitBounds(bounds, { padding: [30, 30] });
                        boundsSet = true;
                    }

                    // Empty state
                    const overlay = document.getElementById('no-data-overlay');
                    overlay.classList.toggle('hidden', data.families.length > 0 || data.drivers.length > 0 || data.volunteers.length > 0);

                    document.getElementById('last-update').textContent = 'Updated ' + new Date().toLocaleTimeString();
                })
                .catch(() => {
                    document.getElementById('last-update').textContent = 'Update failed';
                });
        }

        // Bind filter changes
        document.querySelectorAll('.status-filter, .team-filter, .route-filter, #showRouteLines, #showDrivers')
            .forEach(el => el.addEventListener('change', () => { boundsSet = true; updateMap(); }));

        updateMap();
        setInterval(updateMap, 10000);
    </script>
</x-app-layout>
