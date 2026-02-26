<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - GFSD Food Drive</title>
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
        </div>
        <div class="flex items-center space-x-4">
            <!-- Mode Toggle -->
            <div class="flex bg-gray-800 rounded-lg p-0.5 text-xs">
                <button onclick="setMode('shopping')" id="btn-shopping"
                    class="px-3 py-1.5 rounded-md font-medium transition">Shopping</button>
                <button onclick="setMode('delivery')" id="btn-delivery"
                    class="px-3 py-1.5 rounded-md font-medium transition">Delivery</button>
                <button onclick="setMode('overview')" id="btn-overview"
                    class="px-3 py-1.5 rounded-md font-medium transition">Overview</button>
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
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-4xl font-bold text-white" id="stat-families">—</div>
                <div class="text-sm text-gray-400 mt-1">Families</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-4xl font-bold text-white" id="stat-children">—</div>
                <div class="text-sm text-gray-400 mt-1">Children</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-4xl font-bold text-white" id="stat-members">—</div>
                <div class="text-sm text-gray-400 mt-1">Total People</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-4xl font-bold" id="stat-gifts-pct">—</div>
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
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center col-span-1 row-span-1">
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
        <div id="mode-delivery" class="hidden h-full grid grid-cols-4 grid-rows-3 gap-4">
            <!-- Top stats -->
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="relative">
                    <svg class="w-28 h-28 transform -rotate-90">
                        <circle cx="56" cy="56" r="48" stroke="#374151" stroke-width="8" fill="none"/>
                        <circle id="delivery-ring" cx="56" cy="56" r="48" stroke="#3b82f6" stroke-width="8" fill="none"
                            stroke-dasharray="301.59" stroke-dashoffset="301.59" class="progress-ring" stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span id="delivery-pct" class="text-2xl font-bold">0%</span>
                    </div>
                </div>
                <div class="text-sm text-gray-400 mt-2">Delivered</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-blue-400 pulse" id="delivery-in-transit">0</div>
                <div class="text-sm text-gray-400 mt-1">In Transit</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-gray-400" id="delivery-pending">0</div>
                <div class="text-sm text-gray-400 mt-1">Pending</div>
            </div>
            <div class="bg-gray-800 rounded-lg p-5 flex flex-col justify-center items-center">
                <div class="text-3xl font-bold text-green-400" id="delivery-done">0</div>
                <div class="text-sm text-gray-400 mt-1">Complete</div>
            </div>

            <!-- Map -->
            <div class="bg-gray-800 rounded-lg overflow-hidden col-span-2 row-span-2">
                <div id="map"></div>
            </div>

            <!-- Route progress + Activity feed -->
            <div class="bg-gray-800 rounded-lg p-4 col-span-1 row-span-2 overflow-y-auto">
                <h3 class="text-sm font-medium text-gray-400 mb-3">Routes</h3>
                <div id="route-bars" class="space-y-3">
                    <div class="text-gray-500 text-sm">Loading...</div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg p-4 col-span-1 row-span-2 overflow-y-auto">
                <h3 class="text-sm font-medium text-gray-400 mb-3">Recent Activity</h3>
                <div id="activity-feed" class="space-y-2">
                    <div class="text-gray-500 text-sm">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const DATA_URL = @json(route('santa.commandCenter.data'));
        let currentMode = '{{ $mode === "auto" ? "overview" : $mode }}';
        let map = null;
        let mapMarkers = [];
        let giftChart = null;
        let deliveryChart = null;

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

        function updateMap(drivers) {
            if (!map) return;
            mapMarkers.forEach(m => map.removeLayer(m));
            mapMarkers = [];

            drivers.forEach(d => {
                const marker = L.marker([d.lat, d.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:#3b82f6;color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:bold;border:2px solid #fff;box-shadow:0 0 6px rgba(59,130,246,0.5);">${d.name.charAt(0)}</div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    })
                }).addTo(map).bindPopup(`${d.name}<br><small>${d.updated}</small>`);
                mapMarkers.push(marker);
            });
        }

        // Progress ring helper
        function setRing(id, pct) {
            const ring = document.getElementById(id);
            if (!ring) return;
            const circumference = 2 * Math.PI * 48;
            ring.style.strokeDashoffset = circumference - (pct / 100) * circumference;
        }

        // Build a progress bar HTML
        function progressBar(label, pct, checked, total, color = 'green') {
            const colors = {green: 'bg-green-500', blue: 'bg-blue-500', red: 'bg-red-500', yellow: 'bg-yellow-500'};
            return `<div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-300 truncate">${label}</span>
                    <span class="text-gray-500 text-xs ml-2">${checked}/${total} (${pct}%)</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div class="${colors[color] || 'bg-green-500'} h-2.5 rounded-full transition-all" style="width:${pct}%"></div>
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
                data.delivery.routes.forEach(r => {
                    routeBars += progressBar(r.name + ' (' + r.driver + ')', r.pct, r.completed, r.total, 'blue');
                });
                document.getElementById('route-bars').innerHTML = routeBars || '<div class="text-gray-500 text-sm">No routes created.</div>';

                // Activity feed
                let activityHtml = '';
                data.recent_activity.forEach(a => {
                    const statusColors = {
                        'Delivered': 'text-green-400', 'Picked up': 'text-purple-400',
                        'In transit': 'text-blue-400', 'Attempted': 'text-yellow-400',
                        'Left at door': 'text-green-300', 'No answer': 'text-red-400',
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
                updateMap(data.drivers);

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
                giftChart.update();
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
                labels: ['Delivered', 'Picked Up', 'In Transit', 'Pending'],
                datasets: [{
                    data: [delivery.delivered, delivery.picked_up, delivery.in_transit, delivery.pending],
                    backgroundColor: ['#22c55e', '#a855f7', '#3b82f6', '#6b7280'],
                    borderWidth: 0,
                }]
            };

            if (deliveryChart) {
                deliveryChart.data = chartData;
                deliveryChart.update();
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
