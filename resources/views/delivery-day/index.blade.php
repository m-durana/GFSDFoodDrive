<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Delivery Day
                <x-hint key="delivery-dispatch" text="Manage deliveries in real-time. Assign drivers to auto-create optimized routes, track progress on the live map, and update delivery statuses." />
            </h2>
            <div class="flex items-center gap-2">
                <button onclick="openQuickAssign()"
                   class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                    + Assign Driver
                </button>
                <button @click="showMap = !showMap; if(showMap) $nextTick(() => initInlineMap())"
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition"
                   :class="showMap && 'ring-2 ring-blue-300'">
                    <span x-text="showMap ? 'Hide Map' : 'Live Map'"></span>
                </button>
                <a href="{{ route('delivery.map') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-500 text-white rounded-md hover:bg-gray-400 text-xs font-medium transition"
                   title="Open full-screen map">
                    Full Map
                </a>
                <a href="{{ route('delivery.track') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                    Share Location
                </a>
                <a href="{{ route('delivery.logs') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ showMap: false }" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Inline Map Panel --}}
            <div x-show="showMap" x-transition x-cloak class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden" style="display:none;">
                <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Live Map</h3>
                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                        <span id="inline-map-update"></span>
                        <button @click="showMap = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times; Close</button>
                    </div>
                </div>
                <div id="inline-map" style="height: 400px; width: 100%;"></div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats cards -->
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['needs_delivery'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Need Delivery</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['in_transit'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">In Transit</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['delivered'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Delivered</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['picked_up'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Picked Up</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                <form method="GET" action="{{ route('delivery.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All</option>
                            <option value="needs_delivery" {{ request('status') == 'needs_delivery' ? 'selected' : '' }}>Needs Delivery</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="picked_up" {{ request('status') == 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">Filter</button>
                    <a href="{{ route('delivery.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700">Reset</a>
                </form>
            </div>

            <!-- Main content: Routes sidebar + family cards -->
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Left sidebar: Active Routes -->
                <div class="w-full lg:w-64 shrink-0 space-y-2">
                    <div class="flex items-center justify-between px-1">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Routes</h4>
                        <button onclick="openQuickAssign()" class="text-xs text-red-600 dark:text-red-400 hover:underline">+ New</button>
                    </div>
                    @forelse($routes as $route)
                        @php
                            $routeDone = $route->families->filter(fn($f) => in_array($f->delivery_status?->value, ['delivered', 'picked_up']))->count();
                            $routeTotal = $route->families->count();
                        @endphp
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                            <div class="min-w-0">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $route->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $route->driver ? $route->driver->first_name : ($route->driver_name ?? 'No driver') }}
                                    &middot; {{ $routeDone }}/{{ $routeTotal }} delivered
                                </div>
                            </div>
                            @if($routeTotal > 0)
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                                    <div class="bg-green-500 h-1.5 rounded-full transition-all" style="width: {{ round(($routeDone/$routeTotal)*100) }}%"></div>
                                </div>
                            @endif
                            <div class="flex items-center gap-1 mt-2">
                                <a href="{{ route('delivery.driverView', $route->access_token) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Driver View</a>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ url(route('delivery.driverView', $route->access_token, false)) }}').then(() => {this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 1500)})"
                                    class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700">Copy Link</button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <form method="POST" action="{{ route('santa.deliveryRoutes.destroy', $route) }}" class="inline" onsubmit="return confirm('Delete this route?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 dark:text-red-400 hover:underline">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-xs text-gray-400 dark:text-gray-500 p-2">No routes yet. Click "+ Assign Driver" to create one.</div>
                    @endforelse
                </div>

                <!-- Main panel: family cards grouped by route -->
                <div class="flex-1 space-y-4">
                    @foreach($routes as $route)
                        @if($route->families->count() > 0)
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    {{ $route->name }}
                                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400">{{ $route->formattedDistance() }} &middot; {{ $route->formattedDuration() }}</span>
                                </h4>
                                <div class="space-y-2">
                                    @foreach($route->families as $family)
                                        @include('delivery-day._family-card', ['family' => $family])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Unrouted families --}}
                    @php
                        $unroutedWithNumbers = $families->filter(fn($f) => !$f->delivery_route_id);
                    @endphp
                    @if($unroutedWithNumbers->count() > 0)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">
                                Unrouted Families
                                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">({{ $unroutedWithNumbers->count() }})</span>
                            </h4>
                            <div class="space-y-2">
                                @foreach($unroutedWithNumbers as $family)
                                    @include('delivery-day._family-card', ['family' => $family])
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($routes->every(fn($r) => $r->families->isEmpty()) && $unroutedWithNumbers->isEmpty())
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-8 text-center text-gray-400 dark:text-gray-500">
                            No families match the selected filters.
                        </div>
                    @endif
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Assign Driver Modal --}}
    <div id="quick-assign-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeQuickAssign()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6 relative">
                <button onclick="closeQuickAssign()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl">&times;</button>

                {{-- Step 1: Enter driver info --}}
                <div id="qa-step-form">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Assign Driver</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        The system will auto-select the next batch of nearby undelivered families and create an optimized route.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Driver Name</label>
                            <input type="text" id="qa-driver-name" placeholder="e.g. John Smith" required
                                class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Families per route</label>
                            <select id="qa-batch-size" class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="3">3 families</option>
                                <option value="5" selected>5 families</option>
                                <option value="8">8 families</option>
                                <option value="10">10 families</option>
                                <option value="15">15 families</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="qa-use-location" class="rounded border-gray-300 text-red-600 shadow-sm">
                            <label for="qa-use-location" class="text-sm text-gray-700 dark:text-gray-300">Use my current location as starting point</label>
                        </div>

                        <button onclick="submitQuickAssign()" id="qa-submit-btn"
                            class="w-full px-4 py-2.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-semibold transition">
                            Create Route
                        </button>
                    </div>
                </div>

                {{-- Step 2: Loading --}}
                <div id="qa-step-loading" class="hidden text-center py-8">
                    <div class="inline-block w-8 h-8 border-4 border-red-200 border-t-red-600 rounded-full animate-spin mb-3"></div>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Creating optimized route...</p>
                </div>

                {{-- Step 3: Result --}}
                <div id="qa-step-result" class="hidden">
                    <div class="text-center mb-4">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 mb-2">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Route Created!</h3>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Route</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100" id="qa-result-name"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Families</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100" id="qa-result-stops"></span>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Share this link with the driver:</p>
                    <div class="flex gap-2 mb-4">
                        <input type="text" id="qa-result-url" readonly
                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm bg-gray-50">
                        <button onclick="copyDriverLink()" id="qa-copy-btn"
                            class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                            Copy
                        </button>
                    </div>

                    <div class="flex gap-2">
                        <a id="qa-result-link" href="#" target="_blank"
                            class="flex-1 text-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 text-sm font-medium transition">
                            Open Driver View
                        </a>
                        <button onclick="resetQuickAssign()"
                            class="flex-1 px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium transition">
                            Assign Another
                        </button>
                    </div>
                </div>

                {{-- Error --}}
                <div id="qa-step-error" class="hidden text-center py-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-2">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Could not create route</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4" id="qa-error-message"></p>
                    <button onclick="resetQuickAssign()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        let inlineMap = null;
        let inlineMapMarkers = [];
        let inlineMapLines = [];
        let inlineMapBoundsSet = false;
        let inlineMapInterval = null;

        window.initInlineMap = function() {
            if (inlineMap) { inlineMap.invalidateSize(); return; }
            inlineMap = L.map('inline-map').setView([48.0849, -121.9683], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM', maxZoom: 19,
            }).addTo(inlineMap);
            refreshInlineMap();
            inlineMapInterval = setInterval(refreshInlineMap, 10000);
        };

        function refreshInlineMap() {
            if (!inlineMap) return;
            const statusColors = { pending: '#EAB308', in_transit: '#F97316', delivered: '#22C55E', picked_up: '#3B82F6' };

            fetch('{{ route("delivery.mapData") }}')
                .then(r => r.json())
                .then(data => {
                    inlineMapMarkers.forEach(m => inlineMap.removeLayer(m));
                    inlineMapLines.forEach(l => inlineMap.removeLayer(l));
                    inlineMapMarkers = []; inlineMapLines = [];
                    const bounds = [];

                    data.families.forEach(f => {
                        const color = statusColors[f.status] || '#6B7280';
                        const marker = L.marker([f.lat, f.lng], {
                            icon: L.divIcon({
                                className: '',
                                html: `<div style="background:${color};width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                                iconSize: [12, 12], iconAnchor: [6, 6],
                            })
                        }).addTo(inlineMap).bindPopup(`<b>#${f.number} ${f.name}</b><br>${f.address}`);
                        inlineMapMarkers.push(marker);
                        bounds.push([f.lat, f.lng]);
                    });

                    data.volunteers.forEach(v => {
                        const marker = L.marker([v.lat, v.lng], {
                            icon: L.divIcon({
                                className: '',
                                html: `<div style="background:#9333EA;width:22px;height:22px;border-radius:50%;border:2px solid white;color:white;font-size:10px;font-weight:bold;display:flex;align-items:center;justify-content:center;">${v.initial}</div>`,
                                iconSize: [22, 22], iconAnchor: [11, 11],
                            })
                        }).addTo(inlineMap).bindPopup(`${v.name} — ${v.updated}`);
                        inlineMapMarkers.push(marker);
                        bounds.push([v.lat, v.lng]);
                    });

                    if (data.routes) {
                        data.routes.forEach(r => {
                            if (r.polyline.length < 2) return;
                            const line = L.polyline(r.polyline, { color: r.color || '#dc2626', weight: 2, opacity: 0.5, dashArray: '6,8' }).addTo(inlineMap);
                            inlineMapLines.push(line);
                        });
                    }

                    if (bounds.length > 0 && !inlineMapBoundsSet) {
                        inlineMap.fitBounds(bounds, { padding: [20, 20] });
                        inlineMapBoundsSet = true;
                    }

                    const el = document.getElementById('inline-map-update');
                    if (el) el.textContent = 'Updated ' + new Date().toLocaleTimeString();
                })
                .catch(() => {});
        }

        function showToast(msg, color = 'green') {
            const t = document.getElementById('toast');
            const inner = t.querySelector('div');
            inner.className = `bg-${color}-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium`;
            inner.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        function updateStatusAjax(familyId, selectEl) {
            const status = selectEl.value;
            const card = selectEl.closest('[data-family-id]');
            const badge = card?.querySelector('.status-badge');

            fetch(`/delivery-day/${familyId}/status-ajax`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ delivery_status: status }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    showToast(`Status updated: ${data.label}`);
                    if (badge) {
                        badge.textContent = data.label;
                        const colors = {
                            pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                            in_transit: 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                            delivered: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                            picked_up: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                        };
                        badge.className = 'status-badge inline-flex px-2 py-0.5 text-xs font-medium rounded-full ' + (colors[data.status] || '');
                    }
                }
            })
            .catch(() => showToast('Update failed', 'red'));
        }

        // ─── Quick Assign Modal ───
        function openQuickAssign() {
            document.getElementById('quick-assign-modal').classList.remove('hidden');
            document.getElementById('qa-driver-name').focus();
        }

        function closeQuickAssign() {
            document.getElementById('quick-assign-modal').classList.add('hidden');
            resetQuickAssign();
        }

        function resetQuickAssign() {
            document.getElementById('qa-step-form').classList.remove('hidden');
            document.getElementById('qa-step-loading').classList.add('hidden');
            document.getElementById('qa-step-result').classList.add('hidden');
            document.getElementById('qa-step-error').classList.add('hidden');
            document.getElementById('qa-submit-btn').disabled = false;
        }

        function submitQuickAssign() {
            const name = document.getElementById('qa-driver-name').value.trim();
            if (!name) { document.getElementById('qa-driver-name').focus(); return; }

            const batchSize = document.getElementById('qa-batch-size').value;
            const useLocation = document.getElementById('qa-use-location').checked;

            document.getElementById('qa-submit-btn').disabled = true;
            document.getElementById('qa-step-form').classList.add('hidden');
            document.getElementById('qa-step-loading').classList.remove('hidden');

            const doRequest = (lat, lng) => {
                const body = { driver_name: name, batch_size: parseInt(batchSize) };
                if (lat && lng) { body.start_lat = lat; body.start_lng = lng; }

                fetch('{{ route("delivery.quickAssign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                })
                .then(r => r.json().then(data => ({ ok: r.ok, data })))
                .then(({ ok, data }) => {
                    document.getElementById('qa-step-loading').classList.add('hidden');
                    if (ok && data.ok) {
                        document.getElementById('qa-result-name').textContent = data.route.name;
                        document.getElementById('qa-result-stops').textContent = data.route.stop_count + ' families';
                        document.getElementById('qa-result-url').value = data.route.driver_url;
                        document.getElementById('qa-result-link').href = data.route.driver_url;
                        document.getElementById('qa-step-result').classList.remove('hidden');
                    } else {
                        document.getElementById('qa-error-message').textContent = data.message || 'An error occurred.';
                        document.getElementById('qa-step-error').classList.remove('hidden');
                    }
                })
                .catch(() => {
                    document.getElementById('qa-step-loading').classList.add('hidden');
                    document.getElementById('qa-error-message').textContent = 'Network error. Please try again.';
                    document.getElementById('qa-step-error').classList.remove('hidden');
                });
            };

            if (useLocation && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    pos => doRequest(pos.coords.latitude, pos.coords.longitude),
                    () => doRequest(null, null),
                    { timeout: 5000 }
                );
            } else {
                doRequest(null, null);
            }
        }

        function copyDriverLink() {
            const url = document.getElementById('qa-result-url').value;
            navigator.clipboard.writeText(url).then(() => {
                const btn = document.getElementById('qa-copy-btn');
                btn.textContent = 'Copied!';
                setTimeout(() => btn.textContent = 'Copy', 1500);
            });
        }
    </script>
</x-app-layout>
