<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $route->name }} - {{ __('Delivery Route') }}</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #route-map { height: 300px; width: 100%; }
        .stop-delivered { opacity: 0.7; border-left: 4px solid #22c55e !important; }
        .heading-to { animation: pulse-blue 2s ease-in-out infinite; border-left: 4px solid #3b82f6 !important; }
        @keyframes pulse-blue { 0%, 100% { border-left-color: #3b82f6; } 50% { border-left-color: #93c5fd; } }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900">

    <!-- Toast -->
    <div id="toast" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 hidden">
        <div class="bg-green-600 text-white px-5 py-2.5 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    @if(session('success'))
        <div class="bg-green-600 text-white px-4 py-2 text-sm text-center">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-linear-to-r from-primary via-primary/90 to-primary/80 text-white">
        <div class="max-w-4xl mx-auto px-4 py-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h1 class="text-xl font-bold tracking-tight">{{ $route->display_name }}</h1>
                    <p class="text-sm text-primary-content">{{ $route->formattedMeta() }}</p>
                    <p class="text-xs text-primary-content/80 mt-0.5" id="heading-to-text"></p>
                </div>
                <div class="flex flex-col items-end text-xs text-primary-content">
                    @php $other = app()->getLocale() === 'es' ? 'en' : 'es'; @endphp
                    <a href="?{{ http_build_query(array_merge(request()->query(), ['lang' => $other])) }}" class="text-primary-content/80 hover:text-white text-xs underline mb-0.5">
                        {{ $other === 'es' ? 'ES' : 'EN' }}
                    </a>
                    <span class="uppercase tracking-wider">{{ __('Driver View') }}</span>
                    <span class="font-semibold">{{ now()->format('M j') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-4 space-y-4">

        <!-- Map -->
        <div class="bg-white rounded-2xl shadow-xs overflow-hidden border border-slate-200">
            <div class="px-4 py-2 border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">{{ __('Route Map') }}</div>
            <div id="route-map"></div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 px-4 py-3" id="progress-section">
        @php
            $delivered = $route->families->filter(fn($f) => $f->delivery_status?->value === 'delivered')->count();
            $total = $route->families->count();
            $pct = $total > 0 ? round(($delivered / $total) * 100) : 0;
        @endphp
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="font-medium text-slate-700">{{ __('Progress') }}</span>
            <span class="text-slate-500" id="progress-text">{{ $delivered }}/{{ $total }} ({{ $pct }}%)</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2">
            <div class="bg-emerald-500 h-2 rounded-full transition-all" id="progress-bar" style="width: {{ $pct }}%"></div>
        </div>
        </div>

        <!-- Location sharing banner -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 px-4 py-3 flex items-center justify-between" id="location-banner">
            <div class="text-xs text-slate-600">
                <div class="font-semibold text-slate-700 mb-0.5">{{ __('Location Sharing') }}</div>
                <div id="location-status">{{ __('Location sharing: tap Start') }}</div>
            </div>
            <button onclick="toggleLocationSharing()" id="location-btn"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold">{{ __('Start') }}</button>
        </div>

        <!-- Stops List -->
        <div class="space-y-3" id="stops-list">
        @foreach($route->families as $family)
            @php
                $status = $family->delivery_status?->value ?? 'pending';
                $isDone = $status === 'delivered';
            @endphp
            <div class="bg-white rounded-2xl shadow-xs p-4 border border-slate-200 {{ $isDone ? 'stop-delivered' : '' }}" data-stop-id="{{ $family->route_order }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center h-8 w-8 rounded-full text-sm font-bold stop-order
                                {{ $isDone ? 'bg-emerald-500 text-white' : 'bg-primary text-white' }}">
                                {!! $isDone ? '&#10003;' : $family->route_order !!}
                            </span>
                            <div>
                                <div class="font-semibold text-slate-900">{{ __('Stop :n', ['n' => $family->route_order]) }} · {{ __('Family #:n', ['n' => $family->family_number]) }}</div>
                                <details class="text-xs text-slate-500 mt-0.5">
                                    <summary class="cursor-pointer text-blue-600 underline list-none">{{ __('Show address') }}</summary>
                                    <span class="mt-0.5 block">{{ $family->address }}</span>
                                </details>
                                @if(($driversCanSeePhone ?? false) && ($family->phone1 || $family->phone2))
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        @if($family->phone1)<a href="tel:{{ $family->phone1 }}" class="text-blue-600 underline">{{ $family->phone1 }}</a>@endif
                                        @if($family->phone2) · <a href="tel:{{ $family->phone2 }}" class="text-blue-600 underline">{{ $family->phone2 }}</a>@endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @php
                        $statusLabel = match($status) {
                            'delivered' => __('Delivered'),
                            'in_transit' => __('In transit'),
                            default => __('Pending'),
                        };
                    @endphp
                    <span class="stop-status-badge inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $status === 'delivered' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $status === 'pending' ? 'bg-slate-100 text-slate-800' : '' }}
                        ">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2 mt-3 ml-9">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($family->address) }}"
                        target="_blank"
                        onclick="markHeading('{{ $route->access_token }}', {{ $family->route_order }})"
                        class="inline-flex items-center px-3 py-2 bg-blue-600 text-white rounded-lg text-xs font-semibold">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        {{ __('Navigate') }}
                    </a>

                    <button type="button" class="deliver-btn inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold {{ $isDone ? 'hidden' : '' }}"
                        onclick="markStopDelivered('{{ $route->access_token }}', {{ $family->route_order }}, this)">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        {{ __('Delivered') }}
                    </button>

                    {{-- Fallback form for no-JS --}}
                    <noscript>
                        <form method="POST" action="{{ route('delivery.completeStop', [$route->access_token, $family->route_order]) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold">
                                {{ __('Delivered') }}
                            </button>
                        </form>
                    </noscript>
                </div>
            </div>
        @endforeach
        </div>

        <!-- Returning button -->
        <div class="bg-white rounded-2xl shadow-xs border border-slate-200 px-4 py-3 text-center">
            <button onclick="markReturning()" id="returning-btn"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-500 transition">
                {{ __('All Done — Heading Back') }}
            </button>
            <p class="text-xs text-slate-400 mt-1">{{ __("Tap when all deliveries are complete and you're returning.") }}</p>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const routeToken = @json($route->access_token);
        const routeDataUrl = @json(route('delivery.routeData', $route->access_token));
        const I18N = {!! json_encode([
            'mark_delivered' => __('Mark as delivered?'),
            'marked_delivered' => __('Marked delivered!'),
            'delivered' => __('Delivered'),
            'in_transit' => __('In transit'),
            'heading_to' => __('Heading to'),
            'route_returning_confirm' => __('Mark this route as returning (all deliveries done)?'),
            'route_marked_returning' => __('Route marked as returning!'),
            'returning' => __('Returning...'),
            'all_done' => __('All Done — Heading Back'),
            'lost_connection' => __('Lost connection — live updates paused.'),
            'sharing_stopped' => __('Location sharing: stopped'),
            'geo_unsupported' => __('Geolocation not supported'),
            'stop' => __('Stop'),
            'start' => __('Start'),
            'sharing' => __('Sharing location...'),
            'sharing_pos' => __('Sharing:'),
            'location_error' => __('Location error:'),
            'could_not_update' => __('Could not update status:'),
            'error_prefix' => __('Error:'),
        ]) !!};

        // ── Toast ───────────────────────────────────────────────
        function showToast(msg) {
            const t = document.getElementById('toast');
            t.querySelector('div').textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        // ── Mark delivered via fetch ────────────────────────────
        function markStopDelivered(token, stopOrder, btn) {
            if (!confirm(I18N.mark_delivered)) return;
            btn.disabled = true;
            btn.textContent = '...';

            fetch(`/delivery/route/${token}/complete/${stopOrder}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
                credentials: 'same-origin',
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error ' + r.status);
                return r.json();
            })
            .then(data => {
                showToast(I18N.marked_delivered);
                applyStopDelivered(stopOrder);
            })
            .catch(err => {
                btn.disabled = false;
                btn.textContent = I18N.delivered;
                showToast(I18N.error_prefix + ' ' + err.message);
            });
        }

        // ── Mark heading (in transit) ───────────────────────────
        function markHeading(token, stopOrder) {
            fetch(`/delivery/route/${token}/heading/${stopOrder}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
                credentials: 'same-origin',
            })
            .then(r => {
                if (!r.ok) throw new Error('Server error ' + r.status);
                return r;
            })
            .then(() => {
                const card = document.querySelector(`[data-stop-id="${stopOrder}"]`);
                // Remove heading-to from all other cards
                document.querySelectorAll('.heading-to').forEach(c => c.classList.remove('heading-to'));
                if (card) {
                    card.classList.add('heading-to');
                    const badge = card.querySelector('.stop-status-badge');
                    if (badge) {
                        badge.className = 'stop-status-badge inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                        badge.textContent = I18N.in_transit;
                    }
                    // Update "heading to" text in header
                    const label = card.querySelector('.font-semibold.text-slate-900')?.textContent || '';
                    document.getElementById('heading-to-text').textContent = I18N.heading_to + ' ' + label;
                }
            })
            .catch(err => {
                console.warn('markHeading failed', err);
                showToast(I18N.could_not_update + ' ' + err.message);
            });
        }

        function applyStopDelivered(stopOrder) {
            const card = document.querySelector(`[data-stop-id="${stopOrder}"]`);
            if (!card) return;
            card.classList.add('stop-delivered');
            const list = document.getElementById('stops-list');
            if (list) list.appendChild(card);
            const order = card.querySelector('.stop-order');
            if (order) { order.classList.remove('bg-primary'); order.classList.add('bg-emerald-500'); order.innerHTML = '&#10003;'; }
            const badge = card.querySelector('.stop-status-badge');
            if (badge) { badge.className = 'stop-status-badge inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800'; badge.textContent = I18N.delivered; }
            const btn = card.querySelector('.deliver-btn');
            if (btn) btn.classList.add('hidden');
            updateProgressFromDom();
        }

        function updateProgressFromDom() {
            const stops = document.querySelectorAll('[data-stop-id]');
            const total = stops.length;
            const done = document.querySelectorAll('[data-stop-id].stop-delivered').length;
            const pct = total > 0 ? Math.round((done / total) * 100) : 0;
            document.getElementById('progress-text').textContent = `${done}/${total} (${pct}%)`;
            document.getElementById('progress-bar').style.width = pct + '%';
        }

        // ── 15s polling for live updates ────────────────────────
        let pollFailureCount = 0;
        function pollRouteData() {
            fetch(routeDataUrl)
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => {
                    pollFailureCount = 0;
                    data.stops.forEach(stop => {
                    const card = document.querySelector(`[data-stop-id="${stop.order}"]`);
                        if (!card) return;
                        const isDone = stop.status === 'delivered';
                        if (isDone && !card.classList.contains('stop-delivered')) {
                            applyStopDelivered(stop.order);
                        }
                    });
                    // Update map markers
                    updateMapMarkers(data.stops);
                })
                .catch(err => {
                    pollFailureCount++;
                    console.warn('pollRouteData failed (' + pollFailureCount + ')', err);
                    if (pollFailureCount === 3) {
                        showToast(I18N.lost_connection);
                    }
                });
        }
        setInterval(pollRouteData, 15000);

        // ── Location sharing ────────────────────────────────────
        let locationWatchId = null;
        function toggleLocationSharing() {
            if (locationWatchId !== null) {
                navigator.geolocation.clearWatch(locationWatchId);
                locationWatchId = null;
                document.getElementById('location-btn').textContent = I18N.start;
                document.getElementById('location-status').textContent = I18N.sharing_stopped;
                return;
            }
            if (!navigator.geolocation) {
                document.getElementById('location-status').textContent = I18N.geo_unsupported;
                return;
            }
            document.getElementById('location-btn').textContent = I18N.stop;
            document.getElementById('location-status').textContent = I18N.sharing;
            locationWatchId = navigator.geolocation.watchPosition(
                pos => {
                    document.getElementById('location-status').textContent =
                        `${I18N.sharing_pos} ${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
                    fetch(`/delivery/route/${routeToken}/location`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
                    }).catch(err => console.warn('location post failed', err));
                },
                err => {
                    document.getElementById('location-status').textContent = I18N.location_error + ' ' + err.message;
                },
                { enableHighAccuracy: true, maximumAge: 10000 }
            );
        }

        // ── Mark Returning ────────────────────────────────────
        function markReturning() {
            if (!confirm(I18N.route_returning_confirm)) return;
            const btn = document.getElementById('returning-btn');
            btn.disabled = true;
            btn.textContent = '...';

            fetch(`/delivery/route/${routeToken}/returning`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({}),
                credentials: 'same-origin',
            })
            .then(r => r.json())
            .then(data => {
                showToast(I18N.route_marked_returning);
                btn.textContent = I18N.returning;
                btn.classList.remove('bg-indigo-600', 'hover:bg-indigo-500');
                btn.classList.add('bg-green-600');
            })
            .catch(() => { btn.disabled = false; btn.textContent = I18N.all_done; });
        }

        // ── Map ─────────────────────────────────────────────────
        const map = L.map('route-map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        @php
            $stopsData = $route->families->map(function($f) {
                return [
                    'lat' => (float) $f->latitude,
                    'lng' => (float) $f->longitude,
                    'order' => $f->route_order,
                    'status' => $f->delivery_status?->value ?? 'pending',
                ];
            })->filter(fn($s) => $s['lat'] && $s['lng'])->values();
        @endphp
        let stopsData = @json($stopsData);
        let mapMarkers = {};
        let routeLine = null;

        function buildMap(stops) {
            Object.values(mapMarkers).forEach(m => map.removeLayer(m));
            mapMarkers = {};
            if (routeLine) {
                map.removeLayer(routeLine);
                routeLine = null;
            }
            const bounds = [];
            let polyline = [];

            @if($route->start_lat && $route->start_lng)
                const startLatLng = [{{ $route->start_lat }}, {{ $route->start_lng }}];
                L.marker(startLatLng, {
                    icon: L.divIcon({
                        className: '',
                        html: '<div style="background:#333;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;">S</div>',
                        iconSize: [24, 24], iconAnchor: [12, 12],
                    })
                }).addTo(map).bindPopup('{{ __('Start/End') }}');
                bounds.push(startLatLng);
                polyline.push(startLatLng);
            @endif

            stops.forEach(s => {
                const isDone = s.status === 'delivered';
                const color = isDone ? '#22c55e' : '#dc2626';
                const marker = L.marker([s.lat, s.lng], {
                    icon: L.divIcon({
                        className: '',
                        html: `<div style="background:${color};color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;${isDone ? 'opacity:0.6;' : ''}">${isDone ? '&#10003;' : s.order}</div>`,
                        iconSize: [24, 24], iconAnchor: [12, 12],
                    })
                }).addTo(map).bindPopup(`${I18N.stop} ${s.order}`);
                mapMarkers[s.order] = marker;
                bounds.push([s.lat, s.lng]);
                polyline.push([s.lat, s.lng]);
            });

            @if($route->start_lat && $route->start_lng)
                polyline.push(startLatLng);
            @endif

            if (window.routePolyline?.length > 1) {
                polyline = window.routePolyline;
            }

            if (polyline.length > 1) {
                routeLine = L.polyline(polyline, {color: '#dc2626', weight: 3, opacity: 0.7}).addTo(map);
            }
            if (bounds.length > 0) {
                map.fitBounds(bounds, {padding: [30, 30]});
            } else {
                map.setView([48.08, -121.97], 13);
            }
        }

        function updateMapMarkers(stops) {
            stops.forEach(s => {
                if (!mapMarkers[s.order]) return;
                const isDone = s.status === 'delivered';
                const color = isDone ? '#22c55e' : '#dc2626';
                mapMarkers[s.order].setIcon(L.divIcon({
                    className: '',
                    html: `<div style="background:${color};color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;${isDone ? 'opacity:0.6;' : ''}">${isDone ? '&#10003;' : s.order}</div>`,
                    iconSize: [24, 24], iconAnchor: [12, 12],
                }));
            });
        }

        window.routePolyline = [];
        fetch(routeDataUrl)
            .then(r => r.json())
            .then(data => {
                window.routePolyline = data.route.polyline || [];
                buildMap(data.stops || stopsData);
            })
            .catch(() => buildMap(stopsData));
    </script>
</body>
</html>
