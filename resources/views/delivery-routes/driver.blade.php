<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $route->name }} - Delivery Route</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #route-map { height: 300px; width: 100%; }
        .stop-delivered { opacity: 0.5; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    @if(session('success'))
        <div class="bg-green-500 text-white px-4 py-2 text-sm text-center">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-red-700 text-white px-4 py-3">
        <h1 class="text-lg font-bold">{{ $route->name }}</h1>
        <p class="text-sm text-red-200">
            {{ $route->stop_count }} stops &middot; {{ $route->formattedDistance() }} &middot; {{ $route->formattedDuration() }}
        </p>
    </div>

    <!-- Map -->
    <div id="route-map"></div>

    <!-- Progress Bar -->
    @php
        $delivered = $route->families->filter(fn($f) => $f->delivery_status?->value === 'delivered' || $f->delivery_status?->value === 'picked_up')->count();
        $total = $route->families->count();
        $pct = $total > 0 ? round(($delivered / $total) * 100) : 0;
    @endphp
    <div class="bg-white px-4 py-3 shadow-sm">
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="font-medium text-gray-700">Progress</span>
            <span class="text-gray-500">{{ $delivered }}/{{ $total }} ({{ $pct }}%)</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    <!-- Stops List -->
    <div class="px-4 py-3 space-y-3">
        @foreach($route->families as $family)
            @php
                $status = $family->delivery_status?->value ?? 'pending';
                $isDone = in_array($status, ['delivered', 'picked_up']);
            @endphp
            <div class="bg-white rounded-lg shadow-sm p-4 {{ $isDone ? 'stop-delivered' : '' }}">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full text-sm font-bold
                                {{ $isDone ? 'bg-green-500 text-white' : 'bg-red-700 text-white' }}">
                                {{ $isDone ? '&#10003;' : $family->route_order }}
                            </span>
                            <span class="font-medium text-gray-900">#{{ $family->family_number }} {{ $family->family_name }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-9">{{ $family->address }}</p>
                        @if($family->phone1)
                            <p class="text-sm text-gray-500 ml-9">
                                <a href="tel:{{ $family->phone1 }}" class="text-blue-600">{{ $family->phone1 }}</a>
                            </p>
                        @endif
                        @if($family->delivery_reason)
                            <p class="text-xs text-yellow-600 mt-1 ml-9">Note: {{ $family->delivery_reason }}</p>
                        @endif
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $status === 'in_transit' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $status === 'picked_up' ? 'bg-purple-100 text-purple-800' : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                    </span>
                </div>

                <div class="flex items-center space-x-2 mt-3 ml-9">
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($family->address) }}"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs font-medium">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        Navigate
                    </a>

                    @if(! $isDone)
                        <form method="POST" action="{{ route('delivery.completeStop', [$route->access_token, $family]) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium"
                                onclick="return confirm('Mark #{{ $family->family_number }} as delivered?')">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Delivered
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
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
                    'number' => $f->family_number,
                    'name' => $f->family_name,
                    'status' => $f->delivery_status?->value ?? 'pending',
                ];
            })->filter(function($s) {
                return $s['lat'] && $s['lng'];
            })->values();
        @endphp
        const stops = @json($stopsData);

        const bounds = [];
        const polyline = [];

        @if($route->start_lat && $route->start_lng)
            const startLatLng = [{{ $route->start_lat }}, {{ $route->start_lng }}];
            L.marker(startLatLng, {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#333;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;">S</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                })
            }).addTo(map).bindPopup('Start/End');
            bounds.push(startLatLng);
            polyline.push(startLatLng);
        @endif

        stops.forEach(s => {
            const isDone = s.status === 'delivered' || s.status === 'picked_up';
            const color = isDone ? '#22c55e' : '#dc2626';
            const marker = L.marker([s.lat, s.lng], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="background:${color};color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;${isDone ? 'opacity:0.6;' : ''}">${isDone ? '&#10003;' : s.order}</div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                })
            }).addTo(map).bindPopup(`#${s.number} ${s.name}`);
            bounds.push([s.lat, s.lng]);
            polyline.push([s.lat, s.lng]);
        });

        @if($route->start_lat && $route->start_lng)
            polyline.push(startLatLng);
        @endif

        if (polyline.length > 1) {
            L.polyline(polyline, {color: '#dc2626', weight: 2, opacity: 0.5, dashArray: '5,10'}).addTo(map);
        }

        if (bounds.length > 0) {
            map.fitBounds(bounds, {padding: [30, 30]});
        } else {
            map.setView([48.08, -121.97], 13);
        }
    </script>
    @include('partials.grinch-overscroll')
</body>
</html>
