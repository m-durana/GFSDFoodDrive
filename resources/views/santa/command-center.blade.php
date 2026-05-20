<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - GFSD Food Drive</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        body { background: #111827; }
        /* REL-37: only lock the viewport on tablet+ — phones need to scroll. */
        @media (min-width: 1024px) { body { overflow: hidden; } }
        #map { height: 100%; width: 100%; min-height: 280px; border-radius: 0.5rem; }
        .pulse { animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .progress-ring { transition: stroke-dashoffset 0.5s ease; }
    </style>
</head>
<body class="text-white min-h-screen lg:h-screen flex flex-col">

    <!-- Top Bar -->
    {{-- REL-37: stack vertically on mobile so brand + live + mode toggle + clock
         don't collide; lay out as a single row on tablet+. --}}
    <div class="flex flex-col gap-2 lg:flex-row lg:items-center lg:justify-between px-3 lg:px-6 py-2 lg:py-3 bg-gray-900 border-b border-gray-800 shrink-0">
        <div class="flex items-center flex-wrap gap-x-3 gap-y-1">
            <h1 class="text-base lg:text-xl font-bold text-primary">GFSD Food Drive</h1>
            <span class="hidden lg:inline text-gray-500">|</span>
            <span class="text-xs lg:text-sm text-gray-400">Command Center</span>
            <span class="inline-flex items-center gap-1.5 text-xs lg:ml-2">
                <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-primary"></span></span>
                <span class="font-semibold text-primary uppercase tracking-wider">Live</span>
                <span id="live-age" class="text-gray-500 ml-0.5">just now</span>
            </span>
        </div>
        <div class="flex items-center justify-between lg:justify-end gap-3 lg:gap-4">
            <!-- Mode Toggle -->
            <div class="flex bg-gray-800 rounded-lg p-0.5 text-xs">
                <button onclick="setMode('delivery')" id="btn-delivery"
                    class="px-2.5 lg:px-3 py-1.5 rounded-md font-medium transition">Delivery</button>
                <button onclick="setMode('overview')" id="btn-overview"
                    class="px-2.5 lg:px-3 py-1.5 rounded-md font-medium transition">Overview</button>
                <button onclick="setMode('shopping')" id="btn-shopping"
                    class="px-2.5 lg:px-3 py-1.5 rounded-md font-medium transition">Stock</button>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <span id="clock" class="hidden sm:inline text-xs lg:text-sm text-gray-400 font-mono"></span>
                <a href="{{ route('santa.index') }}" class="text-xs text-gray-500 hover:text-gray-300 whitespace-nowrap">Exit</a>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    {{-- REL-37: on mobile we allow vertical scroll; tablet+ keeps a single viewport. --}}
    <div class="flex-1 lg:overflow-hidden p-3 lg:p-4">

        <!-- OVERVIEW MODE -->
        <div id="mode-overview" class="hidden lg:h-full grid grid-cols-2 lg:grid-cols-6 lg:grid-rows-3 gap-3">
            <!-- Top stats row - 6 key metrics -->
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-white" id="stat-families">—</div>
                <div class="text-xs text-gray-400 mt-1">Families</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-white" id="stat-children">—</div>
                <div class="text-xs text-gray-400 mt-1">Children</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-white" id="stat-members">—</div>
                <div class="text-xs text-gray-400 mt-1">Total People</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold" id="stat-gifts-pct">—</div>
                <div class="text-xs text-gray-400 mt-1">Gifts Covered</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-pink-400" id="stat-adoption-pct">—</div>
                <div class="text-xs text-gray-400 mt-1">Adopted</div>
                <div class="text-[10px] text-gray-600"><span id="stat-adopted-count">0</span> children</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-primary" id="stat-severe">—</div>
                <div class="text-xs text-gray-400 mt-1">Severe Need</div>
                <div class="text-[10px] text-gray-600"><span id="stat-pickup">0</span> pickups</div>
            </div>

            <!-- Row 2: Charts + Delivery summary -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-2 lg:row-span-2 overflow-hidden flex flex-col h-64 lg:h-auto">
                <h3 class="text-sm font-medium text-gray-400 mb-2 shrink-0">Gift Level Distribution</h3>
                <div class="flex-1 min-h-0 relative"><canvas id="gift-chart"></canvas></div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 col-span-2 lg:row-span-2 overflow-hidden flex flex-col h-64 lg:h-auto">
                <h3 class="text-sm font-medium text-gray-400 mb-2 shrink-0">Delivery Progress</h3>
                <div class="flex-1 min-h-0 relative"><canvas id="delivery-chart"></canvas></div>
            </div>

            <!-- Row 2-3 right: Delivery + Operations at-a-glance -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-2 lg:row-span-2 overflow-hidden flex flex-col">
                <h3 class="text-sm font-medium text-gray-400 mb-3 shrink-0">Operations Snapshot</h3>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    <!-- Delivery metrics -->
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                            <div class="text-lg font-bold text-green-400" id="ov-delivered">0</div>
                            <div class="text-[10px] text-gray-500">Delivered</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                            <div class="text-lg font-bold text-blue-400" id="ov-in-transit">0</div>
                            <div class="text-[10px] text-gray-500">In Transit</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                            <div class="text-lg font-bold text-amber-400" id="ov-per-hour">0</div>
                            <div class="text-[10px] text-gray-500">Delivered/hr</div>
                        </div>
                        <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                            <div class="text-lg font-bold text-purple-400" id="ov-active-drivers">0</div>
                            <div class="text-[10px] text-gray-500">Active Drivers</div>
                        </div>
                    </div>
                    <!-- Shopping summary -->
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-400">Shopping</span>
                            <span class="text-gray-500" id="ov-shopping-label">0/0</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all" id="ov-shopping-bar" style="width:0%"></div>
                        </div>
                    </div>
                    <!-- Packing summary -->
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-400">Packing</span>
                            <span class="text-gray-500" id="ov-packing-label">0%</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all" id="ov-packing-bar" style="width:0%"></div>
                        </div>
                    </div>
                    <!-- Gifts intake -->
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-400">Gifts Received</span>
                            <span class="text-gray-500" id="ov-gifts-label">0/0</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full transition-all" id="ov-gifts-bar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STOCK MODE -->
        <div id="mode-shopping" class="hidden lg:h-full grid grid-cols-2 lg:grid-cols-6 lg:grid-rows-3 gap-3">
            <!-- Row 1: Key stats -->
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="relative">
                    <svg viewBox="0 0 80 80" class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="34" stroke="#374151" stroke-width="6" fill="none"/>
                        <circle id="shopping-ring" cx="40" cy="40" r="34" stroke="#22c55e" stroke-width="6" fill="none"
                            stroke-dasharray="213.63" stroke-dashoffset="213.63" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="shopping-pct" class="text-lg font-bold">0%</span>
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-1">Shopping</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-green-400" id="shopping-checked">0</div>
                <div class="text-xs text-gray-400 mt-1">Checked</div>
                <div class="text-xs text-gray-600"><span id="shopping-remaining">0</span> left</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-amber-400" id="stock-on-hand">0</div>
                <div class="text-xs text-gray-400 mt-1">Items On Hand</div>
                <div class="text-xs text-gray-600"><span id="stock-today">0</span> today</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="relative">
                    <svg viewBox="0 0 80 80" class="w-20 h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="34" stroke="#374151" stroke-width="6" fill="none"/>
                        <circle id="packing-ring" cx="40" cy="40" r="34" stroke="#3b82f6" stroke-width="6" fill="none"
                            stroke-dasharray="213.63" stroke-dashoffset="213.63" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="packing-pct" class="text-lg font-bold">0%</span>
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-1">Packing</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-purple-400" id="gifts-received">0</div>
                <div class="text-xs text-gray-400 mt-1">Gifts In</div>
                <div class="text-xs text-gray-600">of <span id="gifts-total-children">0</span></div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-gray-300" id="shopping-total">0</div>
                <div class="text-xs text-gray-400 mt-1">Shopping Items</div>
            </div>

            <!-- Row 2: Warehouse categories + Packing breakdown -->
            <div class="bg-gray-800 rounded-lg p-3 col-span-2 lg:col-span-3 overflow-y-auto max-h-64 lg:max-h-none">
                <h3 class="text-xs font-medium text-gray-400 mb-2">Warehouse Inventory</h3>
                <div id="stock-categories" class="space-y-2">
                    <div class="text-gray-500 text-xs">Loading...</div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 col-span-2 lg:col-span-3 overflow-y-auto max-h-72 lg:max-h-none">
                <h3 class="text-xs font-medium text-gray-400 mb-2">Packing Status</h3>
                <div class="grid grid-cols-4 gap-2 mb-3" id="packing-status-cards">
                    <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                        <div class="text-lg font-bold text-gray-400" id="pack-pending">0</div>
                        <div class="text-[10px] text-gray-500">Pending</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                        <div class="text-lg font-bold text-yellow-400" id="pack-progress">0</div>
                        <div class="text-[10px] text-gray-500">In Progress</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                        <div class="text-lg font-bold text-blue-400" id="pack-complete">0</div>
                        <div class="text-[10px] text-gray-500">Complete</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-sm p-2 text-center">
                        <div class="text-lg font-bold text-green-400" id="pack-verified">0</div>
                        <div class="text-[10px] text-gray-500">Verified</div>
                    </div>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden flex" id="packing-bar">
                    <div class="bg-green-500 h-3 transition-all" id="pbar-verified" style="width:0"></div>
                    <div class="bg-blue-500 h-3 transition-all" id="pbar-complete" style="width:0"></div>
                    <div class="bg-yellow-500 h-3 transition-all" id="pbar-progress" style="width:0"></div>
                </div>
            </div>

            <!-- Row 3: NINJA progress bars -->
            <div class="bg-gray-800 rounded-lg p-3 col-span-2 lg:col-span-6 overflow-y-auto max-h-64 lg:max-h-none">
                <h3 class="text-xs font-medium text-gray-400 mb-2">Volunteer Shopping Progress</h3>
                <div id="ninja-bars" class="space-y-2">
                    <div class="text-gray-500 text-xs">Loading...</div>
                </div>
            </div>
        </div>

        <!-- DELIVERY MODE -->
        {{-- REL-37: 2-col mobile grid for stats; 12-col tablet+. Map + side panels stack on mobile. --}}
        <div id="mode-delivery" class="hidden lg:h-full grid grid-cols-2 lg:grid-cols-12 lg:grid-rows-[auto_1fr] gap-3 lg:gap-4">
            <!-- Top stats row -->
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="relative">
                    <svg viewBox="0 0 80 80" class="w-16 h-16 lg:w-20 lg:h-20 transform -rotate-90">
                        <circle cx="40" cy="40" r="34" stroke="#374151" stroke-width="6" fill="none"/>
                        <circle id="delivery-ring" cx="40" cy="40" r="34" stroke="#3b82f6" stroke-width="6" fill="none"
                            stroke-dasharray="213.63" stroke-dashoffset="213.63" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="delivery-pct" class="text-base lg:text-lg font-bold">0%</span>
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-1">Delivered</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="text-2xl lg:text-3xl font-bold text-blue-400 pulse" id="delivery-in-transit">0</div>
                <div class="text-xs text-gray-400 mt-1">In Transit</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="text-2xl lg:text-3xl font-bold text-gray-400" id="delivery-pending">0</div>
                <div class="text-xs text-gray-400 mt-1">Pending</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="text-2xl lg:text-3xl font-bold text-green-400" id="delivery-done">0</div>
                <div class="text-xs text-gray-400 mt-1">Complete</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="text-2xl lg:text-3xl font-bold text-amber-400" id="delivery-per-hour">0</div>
                <div class="text-xs text-gray-400 mt-1">Delivered/hr</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center lg:col-span-2">
                <div class="text-2xl lg:text-3xl font-bold text-purple-400" id="delivery-active-drivers">0</div>
                <div class="text-xs text-gray-400 mt-1">Active Drivers</div>
            </div>

            <div class="col-span-2 lg:col-span-8 grid lg:grid-rows-[1fr_auto] gap-3 lg:gap-4 lg:min-h-0">
                <div class="bg-gray-800 rounded-lg overflow-hidden lg:min-h-0 h-72 lg:h-auto flex flex-col">
                    <div class="px-4 py-3 border-b border-gray-700 flex items-center justify-between shrink-0">
                        <div>
                            <h3 class="text-sm font-medium text-gray-300">Delivery Map</h3>
                            <p class="text-xs text-gray-500 hidden sm:block">Routes, families, and live vehicle positions</p>
                        </div>
                        <a href="{{ route('delivery.index') }}" class="text-xs text-blue-300 hover:text-blue-200 whitespace-nowrap">Open Dispatch</a>
                    </div>
                    <div id="map" class="flex-1 min-h-0"></div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto lg:min-h-0 max-h-64 lg:max-h-none">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">Recent Activity</h3>
                    <div id="activity-feed" class="space-y-2">
                        <div class="text-gray-500 text-sm">Loading...</div>
                    </div>
                </div>
            </div>

            <div class="col-span-2 lg:col-span-4 flex flex-col gap-3 lg:gap-4 lg:min-h-0">
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto lg:min-h-0 max-h-96 lg:max-h-none">
                    <div class="flex items-center justify-between mb-3 gap-2 flex-wrap">
                        <h3 class="text-sm font-medium text-gray-400">Active Routes</h3>
                        <div class="flex gap-1">
                            <button onclick="setRouteSort('name')" id="sort-name" class="text-[10px] px-1.5 py-0.5 rounded-sm text-gray-500 hover:text-gray-300">Name</button>
                            <button onclick="setRouteSort('progress')" id="sort-progress" class="text-[10px] px-1.5 py-0.5 rounded-sm text-gray-500 hover:text-gray-300">Progress</button>
                            <button onclick="setRouteSort('stops')" id="sort-stops" class="text-[10px] px-1.5 py-0.5 rounded-sm text-gray-500 hover:text-gray-300">Stops</button>
                        </div>
                    </div>
                    <div id="route-bars" class="space-y-3">
                        <div class="text-gray-500 text-sm">Loading...</div>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto lg:min-h-0 max-h-64 lg:max-h-none">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">Dispatch Queue</h3>
                    <div id="dispatch-queue" class="space-y-2">
                        <div class="text-gray-500 text-sm">Loading...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const DATA_URL = @json(route('santa.commandCenter.data'));
        let currentMode = '{{ $mode === "auto" ? "delivery" : $mode }}';
        let map = null;
        let mapMarkers = [];
        let routePolylines = {}; // routeId → L.polyline
        let mapBoundsSet = false;
        let routeVisibility = {}; // routeId → bool, default true
        let giftChart = null;
        let deliveryChart = null;
        let routeSort = localStorage.getItem('cc_route_sort') || 'name';
        let lastFetchTime = Date.now();
        let cachedData = null; // Cache for instant re-sorts

        // Relative time updater for LIVE indicator
        function updateLiveAge() {
            const secs = Math.round((Date.now() - lastFetchTime) / 1000);
            let label;
            if (secs < 5) label = 'just now';
            else if (secs < 60) label = secs + 's ago';
            else { const mins = Math.floor(secs / 60); label = mins + 'm ago'; }
            const el = document.getElementById('live-age');
            if (el) el.textContent = label;
        }
        setInterval(updateLiveAge, 5000);

        function setRouteSort(sort) {
            routeSort = sort;
            localStorage.setItem('cc_route_sort', sort);
            updateSortButtons();
            if (cachedData) rerenderRoutes(cachedData);
            else refresh();
        }
        function updateSortButtons() {
            ['name', 'progress', 'stops'].forEach(s => {
                const btn = document.getElementById('sort-' + s);
                if (!btn) return;
                btn.className = s === routeSort
                    ? 'text-[10px] px-1.5 py-0.5 rounded-sm bg-gray-700 text-white'
                    : 'text-[10px] px-1.5 py-0.5 rounded-sm text-gray-500 hover:text-gray-300';
            });
        }
        updateSortButtons();

        // Clock
        function updateClock() {
            document.getElementById('clock').textContent = new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Mode switching
        function setMode(mode) {
            currentMode = mode;
            document.querySelectorAll('[id^="mode-"]').forEach(el => el.classList.add('hidden'));
            document.getElementById('mode-' + mode).classList.remove('hidden');

            document.querySelectorAll('[id^="btn-"]').forEach(el => {
                el.classList.remove('bg-primary', 'text-white');
                el.classList.add('text-gray-400');
            });
            document.getElementById('btn-' + mode).classList.add('bg-primary', 'text-white');
            document.getElementById('btn-' + mode).classList.remove('text-gray-400');

            if (mode === 'delivery' && !map) {
                setTimeout(initMap, 100);
            }
        }

        function initMap() {
            map = L.map('map').setView([48.08, -121.97], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: ''
            }).addTo(map);
        }

        // 3D-style car icon (top-down perspective with shadow)
        const carSvg = `<svg viewBox="0 0 40 40" width="20" height="20" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="20" cy="22" rx="12" ry="6" fill="rgba(0,0,0,0.25)"/>
            <rect x="10" y="6" width="20" height="26" rx="8" fill="white"/>
            <rect x="12" y="8" width="16" height="10" rx="4" fill="rgba(255,255,255,0.3)"/>
            <rect x="12" y="24" width="16" height="5" rx="2" fill="rgba(255,255,255,0.2)"/>
            <circle cx="13" cy="9" r="2" fill="#facc15"/>
            <circle cx="27" cy="9" r="2" fill="#facc15"/>
            <circle cx="13" cy="29" r="2" fill="#ef4444"/>
            <circle cx="27" cy="29" r="2" fill="#ef4444"/>
        </svg>`;

        function updateMap(mapData) {
            if (!map) return;
            mapMarkers.forEach(m => map.removeLayer(m));
            mapMarkers = [];
            // Remove old route polylines
            Object.values(routePolylines).forEach(l => map.removeLayer(l));
            routePolylines = {};
            const bounds = [];

            const statusColors = { pending: '#6b7280', in_transit: '#f97316', delivered: '#22c55e' };

            (mapData.families || []).forEach(f => {
                const color = statusColors[f.status] || '#6b7280';
                const marker = L.marker([f.lat, f.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:${color};width:10px;height:10px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                        iconSize: [10, 10],
                        iconAnchor: [5, 5],
                    })
                }).addTo(map).bindPopup(`<b>#${f.number} ${f.name}</b><br>${f.address}`);
                mapMarkers.push(marker);
                bounds.push([f.lat, f.lng]);
            });

            (mapData.routes || []).forEach(r => {
                if (!r.polyline || r.polyline.length < 2) return;
                const line = L.polyline(r.polyline, {
                    color: r.color || '#dc2626', weight: 3, opacity: 0.7
                });
                routePolylines[r.id] = line;
                // Only add if visibility is enabled (default true)
                if (routeVisibility[r.id] !== false) {
                    line.addTo(map);
                }
                r.polyline.forEach(p => bounds.push(p));
            });

            (mapData.drivers || []).forEach(d => {
                const isRecent = d.updated && !d.updated.includes('awaiting');
                const pulseStyle = isRecent ? 'animation:pulse 2s ease-in-out infinite;' : '';
                const marker = L.marker([d.lat, d.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:${d.color || '#3b82f6'};border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 8px rgba(0,0,0,0.4);${pulseStyle}">${carSvg}</div>`,
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                    })
                }).addTo(map).bindPopup(`<b>${d.name}</b><br><small>${d.updated}</small>`);
                mapMarkers.push(marker);
                bounds.push([d.lat, d.lng]);
            });

            if (bounds.length && !mapBoundsSet) {
                map.fitBounds(bounds, { padding: [20, 20] });
                mapBoundsSet = true;
            }
        }

        function toggleRouteVisibility(routeId, visible) {
            routeVisibility[routeId] = visible;
            const line = routePolylines[routeId];
            if (!line) return;
            if (visible) line.addTo(map);
            else map.removeLayer(line);
        }

        function highlightRoute(routeId) {
            const line = routePolylines[routeId];
            if (line && routeVisibility[routeId] !== false) {
                line.setStyle({ weight: 6, opacity: 1 });
                line.bringToFront();
            }
        }

        function unhighlightRoute(routeId) {
            const line = routePolylines[routeId];
            if (line) line.setStyle({ weight: 3, opacity: 0.7 });
        }

        function markRouteReturning(routeId) {
            if (!confirm('Mark this route as returning?')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(`/delivery-day/routes/${routeId}/mark-returning`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => { if (r.ok) refresh(); });
        }

        function copyRouteLink(token) {
            if (!token) return;
            const url = window.location.origin + '/delivery/route/' + token;
            navigator.clipboard.writeText(url).then(() => {
                // Brief visual feedback could be added here
            });
        }

        function recalcRoute(routeId, btn) {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const svg = btn.querySelector('svg');
            if (svg) svg.classList.add('animate-spin');
            fetch(`/santa/delivery-routes/${routeId}/recalculate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => {
                if (svg) svg.classList.remove('animate-spin');
                if (r.ok) refresh();
            }).catch(() => { if (svg) svg.classList.remove('animate-spin'); });
        }

        function deleteRoute(routeId) {
            if (!confirm('Delete this route? Families will be unassigned.')) return;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            fetch(`/santa/delivery-routes/${routeId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => { if (r.ok) refresh(); });
        }

        // Progress ring helper
        function setRing(id, pct) {
            const ring = document.getElementById(id);
            if (!ring) return;
            const r = parseFloat(ring.getAttribute('r')) || 48;
            const circumference = 2 * Math.PI * r;
            ring.style.strokeDashoffset = circumference - (pct / 100) * circumference;
        }

        // Build a progress bar HTML
        function progressBar(label, pct, checked, total, color = 'green') {
            const colors = {green: 'bg-green-500', blue: 'bg-blue-500', red: 'bg-primary', yellow: 'bg-yellow-500'};
            const barStyle = color.startsWith('#') ? `style="width:${pct}%;background:${color}"` : `class="${colors[color] || 'bg-green-500'} h-2.5 rounded-full transition-all" style="width:${pct}%"`;
            return `<div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-300 truncate">${label}</span>
                    <span class="text-gray-500 text-xs ml-2">${checked}/${total} (${pct}%)</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div ${barStyle}></div>
                </div>
            </div>`;
        }

        // Fetch and render data
        async function refresh() {
            try {
                const res = await fetch(DATA_URL);
                const data = await res.json();

                lastFetchTime = Date.now();
                cachedData = data;
                updateLiveAge();

                // Overview
                document.getElementById('stat-families').textContent = data.overview.total_families;
                document.getElementById('stat-children').textContent = data.overview.total_children;
                document.getElementById('stat-members').textContent = data.overview.total_members;
                document.getElementById('stat-gifts-pct').textContent = data.gifts.pct_covered + '%';
                document.getElementById('stat-gifts-pct').className = 'text-3xl font-bold ' +
                    (data.gifts.pct_covered >= 80 ? 'text-green-400' : data.gifts.pct_covered >= 50 ? 'text-yellow-400' : 'text-primary');
                document.getElementById('stat-adoption-pct').textContent = data.overview.adoption_pct + '%';
                document.getElementById('stat-adopted-count').textContent = data.overview.adopted;
                document.getElementById('stat-severe').textContent = data.overview.severe_need;
                document.getElementById('stat-pickup').textContent = data.overview.pickup;

                // Overview — Operations snapshot
                document.getElementById('ov-delivered').textContent = data.delivery.delivered;
                document.getElementById('ov-in-transit').textContent = data.delivery.in_transit;
                document.getElementById('ov-per-hour').textContent = data.delivery.delivered_last_hour;
                document.getElementById('ov-active-drivers').textContent = data.delivery.active_drivers;

                const shopPct = data.shopping.pct || 0;
                document.getElementById('ov-shopping-label').textContent = data.shopping.checked_items + '/' + data.shopping.total_items;
                document.getElementById('ov-shopping-bar').style.width = shopPct + '%';

                if (data.stock) {
                    document.getElementById('ov-packing-label').textContent = data.stock.packing.pct + '%';
                    document.getElementById('ov-packing-bar').style.width = data.stock.packing.pct + '%';
                    const gTotal = data.stock.gifts.total_children || 1;
                    const gPct = Math.round((data.stock.gifts.received / gTotal) * 100);
                    document.getElementById('ov-gifts-label').textContent = data.stock.gifts.received + '/' + data.stock.gifts.total_children;
                    document.getElementById('ov-gifts-bar').style.width = gPct + '%';
                }

                // Gift chart
                updateGiftChart(data.gifts);

                // Delivery chart
                updateDeliveryDoughnut(data.delivery);

                // Stock mode — Shopping
                document.getElementById('shopping-pct').textContent = data.shopping.pct + '%';
                setRing('shopping-ring', data.shopping.pct);
                document.getElementById('shopping-checked').textContent = data.shopping.checked_items;
                document.getElementById('shopping-total').textContent = data.shopping.total_items;
                document.getElementById('shopping-remaining').textContent = data.shopping.total_items - data.shopping.checked_items;

                let ninjaBars = '';
                data.shopping.ninjas.forEach(n => {
                    ninjaBars += progressBar(n.name + ' — ' + n.description, n.pct, n.checked_items, n.total_items, 'green');
                });
                document.getElementById('ninja-bars').innerHTML = ninjaBars || '<div class="text-gray-500 text-xs">No volunteer assignments yet.</div>';

                // Stock mode — Warehouse & Packing
                if (data.stock) {
                    const s = data.stock;
                    document.getElementById('stock-on-hand').textContent = s.warehouse.total_on_hand;
                    document.getElementById('stock-today').textContent = s.warehouse.receipts_today;
                    document.getElementById('packing-pct').textContent = s.packing.pct + '%';
                    setRing('packing-ring', s.packing.pct);
                    document.getElementById('gifts-received').textContent = s.gifts.received;
                    document.getElementById('gifts-total-children').textContent = s.gifts.total_children;

                    // Packing status cards
                    document.getElementById('pack-pending').textContent = s.packing.pending;
                    document.getElementById('pack-progress').textContent = s.packing.in_progress;
                    document.getElementById('pack-complete').textContent = s.packing.complete;
                    document.getElementById('pack-verified').textContent = s.packing.verified;

                    // Packing stacked bar
                    const pt = s.packing.total || 1;
                    document.getElementById('pbar-verified').style.width = (s.packing.verified / pt * 100) + '%';
                    document.getElementById('pbar-complete').style.width = (s.packing.complete / pt * 100) + '%';
                    document.getElementById('pbar-progress').style.width = (s.packing.in_progress / pt * 100) + '%';

                    // Warehouse categories
                    const typeColors = {food: '#f59e0b', gift: '#a855f7', baby: '#ec4899', supply: '#3b82f6'};
                    let catHtml = '';
                    s.warehouse.categories.forEach(c => {
                        const color = typeColors[c.type] || '#6b7280';
                        catHtml += `<div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background:${color}"></span>
                                <span class="text-gray-300">${c.name}</span>
                            </div>
                            <span class="text-gray-500 font-mono">${c.count}</span>
                        </div>`;
                    });
                    document.getElementById('stock-categories').innerHTML = catHtml || '<div class="text-gray-500 text-xs">No categories.</div>';
                }

                // Delivery mode
                document.getElementById('delivery-pct').textContent = data.delivery.pct + '%';
                setRing('delivery-ring', data.delivery.pct);
                document.getElementById('delivery-in-transit').textContent = data.delivery.in_transit;
                document.getElementById('delivery-pending').textContent = data.delivery.pending;
                document.getElementById('delivery-done').textContent = data.delivery.done;
                document.getElementById('delivery-per-hour').textContent = data.delivery.delivered_last_hour;
                document.getElementById('delivery-active-drivers').textContent = data.delivery.active_drivers;

                rerenderRoutes(data);

                const queue = (data.delivery.dispatch_queue || []).map(f => `
                    <div class="border border-gray-700 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-200">#${f.number} ${f.name}</div>
                        <div class="text-xs text-gray-500 mt-1">${f.address}</div>
                        <div class="text-xs text-gray-400 mt-1">${f.distance_hint}</div>
                    </div>
                `).join('');
                document.getElementById('dispatch-queue').innerHTML = queue || '<div class="text-gray-500 text-sm">No unrouted delivery families.</div>';

                // Activity feed
                let activityHtml = '';
                (data.recent_activity || []).forEach(a => {
                    const statusColors = {
                        'Delivered': 'text-green-400', 'In transit': 'text-blue-400',
                        'Attempted': 'text-yellow-400', 'Left at door': 'text-green-300',
                        'No answer': 'text-primary',
                    };
                    const color = statusColors[a.status] || 'text-gray-400';
                    activityHtml += `<div class="text-xs border-b border-gray-700 pb-2">
                        <div class="flex justify-between">
                            <span class="${color} font-medium">${a.status}</span>
                            <span class="text-gray-600">${a.time}</span>
                        </div>
                        <div class="text-gray-400">${a.family}</div>
                        ${a.notes ? `<div class="text-gray-600 italic">${a.notes}</div>` : ''}
                    </div>`;
                });
                document.getElementById('activity-feed').innerHTML = activityHtml || '<div class="text-gray-500 text-sm">No activity yet.</div>';

                // Map
                updateMap(data.delivery_map || {});

            } catch (e) {
                console.error('Refresh failed:', e);
            }
        }

        function rerenderRoutes(data) {
                let routeBars = '';
                const sortedRoutes = [...data.delivery.routes].sort((a, b) => {
                    if (routeSort === 'progress') return b.pct - a.pct;
                    if (routeSort === 'stops') return b.total - a.total;
                    return a.name.localeCompare(b.name);
                });
                sortedRoutes.forEach(r => {
                    const headingHtml = r.heading_to
                        ? `<div class="text-xs text-blue-400 mt-1.5 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg> Heading to ${r.heading_to}</div>`
                        : '';
                    const checked = routeVisibility[r.id] !== false;
                    routeBars += `
                        <div class="border border-gray-700 rounded-lg p-3 route-card" data-route-id="${r.id}"
                            onmouseenter="highlightRoute(${r.id})" onmouseleave="unhighlightRoute(${r.id})">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <input type="checkbox" ${checked ? 'checked' : ''}
                                        onchange="toggleRouteVisibility(${r.id}, this.checked)"
                                        class="rounded-sm w-3.5 h-3.5 cursor-pointer" style="accent-color:${r.color}">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-gray-200 truncate" style="border-left:3px solid ${r.color};padding-left:6px">${r.name}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">${r.driver} · ${r.meta}</div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-400 whitespace-nowrap">${r.completed}/${r.total}</div>
                            </div>
                            <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                                <div class="h-2 rounded-full transition-all" style="width:${r.pct}%;background:${r.color || '#3b82f6'}"></div>
                            </div>
                            ${headingHtml}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-[11px]">
                                ${r.access_token ? `<a href="/delivery/route/${r.access_token}" target="_blank"
                                    class="text-green-500 hover:text-green-300 transition py-1">Driver</a>` : ''}
                                <button onclick="copyRouteLink('${r.access_token || ''}')"
                                    class="text-gray-500 hover:text-gray-300 transition py-1">Copy</button>
                                <button onclick="markRouteReturning(${r.id})"
                                    class="text-indigo-400 hover:text-indigo-300 transition py-1">Return</button>
                                <button onclick="recalcRoute(${r.id}, this)"
                                    class="text-yellow-500 hover:text-yellow-300 transition py-1">Recalc</button>
                                <button onclick="deleteRoute(${r.id})"
                                    class="text-primary hover:text-primary transition py-1">Delete</button>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('route-bars').innerHTML = routeBars || '<div class="text-gray-500 text-sm">No routes created.</div>';
        }

        function updateGiftChart(gifts) {
            const ctx = document.getElementById('gift-chart');
            if (!ctx) return;

            const chartData = {
                labels: ['No Gifts', 'Partial', 'Moderate', 'Full'],
                datasets: [{
                    data: [gifts.level_0, gifts.level_1, gifts.level_2, gifts.level_3],
                    backgroundColor: ['#6b7280', '#f59e0b', '#3b82f6', '#22c55e'],
                    borderWidth: 0,
                }]
            };

            if (giftChart) {
                giftChart.data = chartData;
                giftChart.update('none');
            } else {
                giftChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: chartData,
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        layout: { padding: { bottom: 5 } },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#9ca3af', font: { size: 11 }, padding: 12, boxWidth: 12 } }
                        },
                        cutout: '60%',
                    }
                });
            }
        }

        function updateDeliveryDoughnut(delivery) {
            const ctx = document.getElementById('delivery-chart');
            if (!ctx) return;

            const chartData = {
                labels: ['Delivered', 'In Transit', 'Pending'],
                datasets: [{
                    data: [delivery.delivered, delivery.in_transit, delivery.pending],
                    backgroundColor: ['#22c55e', '#3b82f6', '#6b7280'],
                    borderWidth: 0,
                }]
            };

            if (deliveryChart) {
                deliveryChart.data = chartData;
                deliveryChart.update('none');
            } else {
                deliveryChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: chartData,
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        layout: { padding: { bottom: 5 } },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#9ca3af', font: { size: 11 }, padding: 12, boxWidth: 12 } }
                        },
                        cutout: '60%',
                    }
                });
            }
        }

        // Init
        setMode(currentMode);
        refresh();
        setInterval(refresh, 15000); // Refresh every 15 seconds
    </script>
</body>
</html>
