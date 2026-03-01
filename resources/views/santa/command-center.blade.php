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
        body { background: #111827; overflow: hidden; }
        #map { height: 100%; width: 100%; border-radius: 0.5rem; }
        .pulse { animation: pulse 2s ease-in-out infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .progress-ring { transition: stroke-dashoffset 0.5s ease; }
    </style>
</head>
<body class="text-white h-screen flex flex-col">

    <!-- Top Bar -->
    <div class="flex items-center justify-between px-6 py-3 bg-gray-900 border-b border-gray-800 shrink-0">
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-bold text-red-500">GFSD Food Drive</h1>
            <span class="text-gray-500">|</span>
            <span class="text-sm text-gray-400">Command Center</span>
            <span class="inline-flex items-center gap-1.5 text-xs ml-2">
                <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span></span>
                <span class="font-semibold text-red-500 uppercase tracking-wider">Live</span>
            </span>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Mode Toggle -->
            <div class="flex bg-gray-800 rounded-lg p-0.5 text-xs">
                <button onclick="setMode('delivery')" id="btn-delivery"
                    class="px-3 py-1.5 rounded-md font-medium transition">Delivery</button>
                <button onclick="setMode('overview')" id="btn-overview"
                    class="px-3 py-1.5 rounded-md font-medium transition">Overview</button>
                <button onclick="setMode('shopping')" id="btn-shopping"
                    class="px-3 py-1.5 rounded-md font-medium transition">Shopping</button>
            </div>
            <span id="clock" class="text-sm text-gray-400 font-mono"></span>
            <span id="last-update" class="text-xs text-gray-600"></span>
            <a href="{{ route('santa.index') }}" class="text-xs text-gray-500 hover:text-gray-300">Exit</a>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 overflow-hidden p-4">

        <!-- OVERVIEW MODE -->
        <div id="mode-overview" class="hidden h-full grid grid-cols-4 grid-rows-3 gap-4">
            <!-- Top stats row -->
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-white" id="stat-families">—</div>
                <div class="text-sm text-gray-400 mt-1">Families</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-white" id="stat-children">—</div>
                <div class="text-sm text-gray-400 mt-1">Children</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold text-white" id="stat-members">—</div>
                <div class="text-sm text-gray-400 mt-1">Total People</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center">
                <div class="text-2xl font-bold" id="stat-gifts-pct">—</div>
                <div class="text-sm text-gray-400 mt-1">Gifts Covered</div>
            </div>

            <!-- Gift levels chart -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-2 row-span-2 overflow-hidden flex flex-col">
                <h3 class="text-sm font-medium text-gray-400 mb-2 shrink-0">Gift Level Distribution</h3>
                <div class="flex-1 min-h-0 relative"><canvas id="gift-chart"></canvas></div>
            </div>

            <!-- Delivery progress -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-2 row-span-2 overflow-hidden flex flex-col">
                <h3 class="text-sm font-medium text-gray-400 mb-2 shrink-0">Delivery Progress</h3>
                <div class="flex-1 min-h-0 relative"><canvas id="delivery-chart"></canvas></div>
            </div>
        </div>

        <!-- SHOPPING MODE -->
        <div id="mode-shopping" class="hidden h-full grid grid-cols-4 grid-rows-3 gap-4">
            <!-- Overall progress -->
            <div class="bg-gray-800 rounded-lg p-3 flex flex-col justify-center items-center col-span-1 row-span-1">
                <div class="relative">
                    <svg class="w-28 h-28 transform -rotate-90">
                        <circle cx="56" cy="56" r="48" stroke="#374151" stroke-width="8" fill="none"/>
                        <circle id="shopping-ring" cx="56" cy="56" r="48" stroke="#22c55e" stroke-width="8" fill="none"
                            stroke-dasharray="301.59" stroke-dashoffset="301.59" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="shopping-pct" class="text-2xl font-bold">0%</span>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mt-2">Shopping Complete</div>
            </div>

            <!-- Item counts -->
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-green-400" id="shopping-checked">0</div>
                <div class="text-sm text-gray-400 mt-1">Items Checked</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-gray-300" id="shopping-total">0</div>
                <div class="text-sm text-gray-400 mt-1">Total Items</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-yellow-400" id="shopping-remaining">0</div>
                <div class="text-sm text-gray-400 mt-1">Remaining</div>
            </div>

            <!-- NINJA progress bars -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-4 row-span-2 overflow-y-auto">
                <h3 class="text-sm font-medium text-gray-400 mb-3">NINJA Progress</h3>
                <div id="ninja-bars" class="space-y-3">
                    <div class="text-gray-500 text-sm">Loading...</div>
                </div>
            </div>
        </div>

        <!-- DELIVERY MODE -->
        <div id="mode-delivery" class="hidden h-full grid grid-cols-12 grid-rows-[auto_1fr] gap-4">
            <!-- Top stats row -->
            <div class="bg-gray-800 rounded-lg p-4 flex flex-col justify-center items-center col-span-3">
                <div class="relative">
                    <svg class="w-24 h-24 transform -rotate-90">
                        <circle cx="48" cy="48" r="40" stroke="#374151" stroke-width="7" fill="none"/>
                        <circle id="delivery-ring" cx="48" cy="48" r="40" stroke="#3b82f6" stroke-width="7" fill="none"
                            stroke-dasharray="251.33" stroke-dashoffset="251.33" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="delivery-pct" class="text-xl font-bold">0%</span>
                    </div>
                </div>
                <div class="text-xs text-gray-400 mt-1">Delivered</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 flex flex-col justify-center items-center col-span-3">
                <div class="text-3xl font-bold text-blue-400 pulse" id="delivery-in-transit">0</div>
                <div class="text-sm text-gray-400 mt-1">In Transit</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 flex flex-col justify-center items-center col-span-3">
                <div class="text-3xl font-bold text-gray-400" id="delivery-pending">0</div>
                <div class="text-sm text-gray-400 mt-1">Pending</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 flex flex-col justify-center items-center col-span-3">
                <div class="text-3xl font-bold text-green-400" id="delivery-done">0</div>
                <div class="text-sm text-gray-400 mt-1">Complete</div>
            </div>

            <div class="col-span-8 grid grid-rows-[1fr_auto] gap-4 min-h-0">
                <div class="bg-gray-800 rounded-lg overflow-hidden min-h-0">
                    <div class="px-4 py-3 border-b border-gray-700 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-medium text-gray-300">Delivery Map</h3>
                            <p class="text-xs text-gray-500">Routes, families, and live vehicle positions</p>
                        </div>
                        <a href="{{ route('delivery.index') }}" class="text-xs text-blue-300 hover:text-blue-200">Open Dispatch</a>
                    </div>
                    <div id="map"></div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto min-h-0">
                    <h3 class="text-sm font-medium text-gray-400 mb-3">Recent Activity</h3>
                    <div id="activity-feed" class="space-y-2">
                        <div class="text-gray-500 text-sm">Loading...</div>
                    </div>
                </div>
            </div>

            <div class="col-span-4 flex flex-col gap-4 min-h-0">
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto min-h-0">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-medium text-gray-400">Active Routes</h3>
                        <div class="flex gap-1">
                            <button onclick="setRouteSort('name')" id="sort-name" class="text-[10px] px-1.5 py-0.5 rounded text-gray-500 hover:text-gray-300">Name</button>
                            <button onclick="setRouteSort('progress')" id="sort-progress" class="text-[10px] px-1.5 py-0.5 rounded text-gray-500 hover:text-gray-300">Progress</button>
                            <button onclick="setRouteSort('stops')" id="sort-stops" class="text-[10px] px-1.5 py-0.5 rounded text-gray-500 hover:text-gray-300">Stops</button>
                        </div>
                    </div>
                    <div id="route-bars" class="space-y-3">
                        <div class="text-gray-500 text-sm">Loading...</div>
                    </div>
                </div>
                <div class="bg-gray-800 rounded-lg p-4 overflow-y-auto min-h-0">
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

        function setRouteSort(sort) {
            routeSort = sort;
            localStorage.setItem('cc_route_sort', sort);
            updateSortButtons();
            refresh();
        }
        function updateSortButtons() {
            ['name', 'progress', 'stops'].forEach(s => {
                const btn = document.getElementById('sort-' + s);
                if (!btn) return;
                btn.className = s === routeSort
                    ? 'text-[10px] px-1.5 py-0.5 rounded bg-gray-700 text-white'
                    : 'text-[10px] px-1.5 py-0.5 rounded text-gray-500 hover:text-gray-300';
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
                el.classList.remove('bg-red-700', 'text-white');
                el.classList.add('text-gray-400');
            });
            document.getElementById('btn-' + mode).classList.add('bg-red-700', 'text-white');
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

        const carSvg = `<svg viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>`;

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
            const colors = {green: 'bg-green-500', blue: 'bg-blue-500', red: 'bg-red-500', yellow: 'bg-yellow-500'};
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

                document.getElementById('last-update').textContent = 'Updated ' + data.timestamp;

                // Overview
                document.getElementById('stat-families').textContent = data.overview.total_families;
                document.getElementById('stat-children').textContent = data.overview.total_children;
                document.getElementById('stat-members').textContent = data.overview.total_members;
                document.getElementById('stat-gifts-pct').textContent = data.gifts.pct_covered + '%';
                document.getElementById('stat-gifts-pct').className = 'text-4xl font-bold ' +
                    (data.gifts.pct_covered >= 80 ? 'text-green-400' : data.gifts.pct_covered >= 50 ? 'text-yellow-400' : 'text-red-400');

                // Gift chart
                updateGiftChart(data.gifts);

                // Delivery chart
                updateDeliveryDoughnut(data.delivery);

                // Shopping mode
                document.getElementById('shopping-pct').textContent = data.shopping.pct + '%';
                setRing('shopping-ring', data.shopping.pct);
                document.getElementById('shopping-checked').textContent = data.shopping.checked_items;
                document.getElementById('shopping-total').textContent = data.shopping.total_items;
                document.getElementById('shopping-remaining').textContent = data.shopping.total_items - data.shopping.checked_items;

                let ninjaBars = '';
                data.shopping.ninjas.forEach(n => {
                    ninjaBars += progressBar(n.name + ' — ' + n.description, n.pct, n.checked_items, n.total_items, 'green');
                });
                document.getElementById('ninja-bars').innerHTML = ninjaBars || '<div class="text-gray-500 text-sm">No NINJA assignments yet.</div>';

                // Delivery mode
                document.getElementById('delivery-pct').textContent = data.delivery.pct + '%';
                setRing('delivery-ring', data.delivery.pct);
                document.getElementById('delivery-in-transit').textContent = data.delivery.in_transit;
                document.getElementById('delivery-pending').textContent = data.delivery.pending;
                document.getElementById('delivery-done').textContent = data.delivery.done;

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
                                        class="rounded w-3.5 h-3.5 cursor-pointer" style="accent-color:${r.color}">
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
                            <div class="flex items-center gap-2 mt-2">
                                <button onclick="markRouteReturning(${r.id})"
                                    class="text-xs text-indigo-400 hover:text-indigo-300 transition">Mark Returning</button>
                            </div>
                        </div>
                    `;
                });
                document.getElementById('route-bars').innerHTML = routeBars || '<div class="text-gray-500 text-sm">No routes created.</div>';

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
                data.recent_activity.forEach(a => {
                    const statusColors = {
                        'Delivered': 'text-green-400', 'In transit': 'text-blue-400',
                        'Attempted': 'text-yellow-400', 'Left at door': 'text-green-300',
                        'No answer': 'text-red-400',
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
