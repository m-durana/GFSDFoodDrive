<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                Delivery Day
                <x-hint key="delivery-dispatch" text="Manage deliveries in real-time. Assign drivers to auto-create optimized routes, track progress on the live map, and update delivery statuses." />
                <x-live-indicator class="ml-3" />
            </h2>
            <div class="flex items-center gap-2">
                <button onclick="openQuickAssign()"
                   class="inline-flex items-center px-3 py-1.5 bg-primary text-white rounded-md hover:opacity-90 text-xs font-medium transition">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Assign Driver
                    @if($routingStats['eligible'] > 0)
                        <span class="ml-1.5 bg-primary/30 text-primary-content/80 text-[10px] px-1.5 py-0.5 rounded-full">{{ $routingStats['eligible'] }}</span>
                    @endif
                </button>
                {{-- Location sharing toggle --}}
                <button onclick="toggleLocationSharing()" id="location-toggle"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition"
                   title="Share your location with the live map">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span id="location-label">Share Location</span>
                </button>
                <button onclick="openLogDrawer()"
                   class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Logs
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4" x-data x-cloak>
        <div class="max-w-[1600px] mx-auto sm:px-4 lg:px-6 space-y-3">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-primary/5 dark:bg-primary/20 border border-primary/30 dark:border-primary text-primary dark:text-primary px-4 py-3 rounded-sm text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Stats cards -->
            <div class="grid grid-cols-5 gap-2">
                <div class="bg-base-100 shadow-xs rounded-lg p-2.5 text-center">
                    <div class="text-xl font-bold text-base-content">{{ $stats['total'] }}</div>
                    <div class="text-[10px] text-base-content/60 uppercase tracking-wide">Total</div>
                </div>
                <div class="bg-base-100 shadow-xs rounded-lg p-2.5 text-center">
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['needs_delivery'] }}</div>
                    <div class="text-[10px] text-base-content/60 uppercase tracking-wide">Need Delivery</div>
                </div>
                <div class="bg-base-100 shadow-xs rounded-lg p-2.5 text-center">
                    <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</div>
                    <div class="text-[10px] text-base-content/60 uppercase tracking-wide">Pending</div>
                </div>
                <div class="bg-base-100 shadow-xs rounded-lg p-2.5 text-center">
                    <div class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['in_transit'] }}</div>
                    <div class="text-[10px] text-base-content/60 uppercase tracking-wide">In Transit</div>
                </div>
                <div class="bg-base-100 shadow-xs rounded-lg p-2.5 text-center">
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $stats['delivered'] }}</div>
                    <div class="text-[10px] text-base-content/60 uppercase tracking-wide">Delivered</div>
                </div>
            </div>

            <!-- Main layout: Map + Routes/Families -->
            <div class="flex flex-col lg:flex-row gap-3" style="height: calc(100vh - 220px);">

                <!-- Left: Live Map -->
                <div class="w-full lg:w-1/2 xl:w-7/12 flex flex-col min-h-0">
                    <div class="bg-base-100 shadow-xs rounded-lg overflow-hidden flex-1 flex flex-col min-h-0">
                        <div class="px-3 py-2 border-b border-base-300 flex items-center justify-between shrink-0">
                            <span class="text-xs font-medium text-base-content/80">Live Map</span>
                            <div class="flex items-center gap-3 text-[10px] text-gray-400">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" id="showRouteLines" class="rounded-sm w-3 h-3" checked> Routes
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" id="showDrivers" class="rounded-sm w-3 h-3" checked> Drivers
                                </label>
                                <span id="map-last-update" class="text-gray-500">Loading...</span>
                            </div>
                        </div>
                        <div id="delivery-map" class="flex-1 min-h-0"></div>
                    </div>
                </div>

                <!-- Right: Routes + Families -->
                <div class="w-full lg:w-1/2 xl:w-5/12 flex flex-col min-h-0 overflow-y-auto space-y-3">

                    <!-- Filter bar (compact) -->
                    <div class="bg-base-100 shadow-xs rounded-lg px-3 py-2 shrink-0">
                        <form method="GET" action="{{ route('delivery.index') }}" class="flex items-center gap-2">
                            <select name="status" class="rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-xs py-1.5">
                                <option value="">All Statuses</option>
                                <option value="needs_delivery" {{ request('status') == 'needs_delivery' ? 'selected' : '' }}>Needs Delivery</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                            <button type="submit" class="px-3 py-1.5 bg-primary text-white rounded-md hover:opacity-90 text-xs font-medium transition">Filter</button>
                            @if(request('status'))
                                <a href="{{ route('delivery.index') }}" class="text-xs text-gray-400 hover:text-gray-600">Clear</a>
                            @endif
                        </form>
                    </div>

                    <!-- Routes with their families -->
                    @forelse($routes as $route)
                        @php
                            $routeDone = $route->families->filter(fn($f) => $f->delivery_status?->value === 'delivered')->count();
                            $routeTotal = $route->families->count();
                            $routePct = $routeTotal > 0 ? round(($routeDone / $routeTotal) * 100) : 0;
                        @endphp
                        <div class="bg-base-100 shadow-xs rounded-lg overflow-hidden">
                            <!-- Route header -->
                            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-base-content truncate">{{ $route->display_name }}</div>
                                        <div class="text-xs text-base-content/60">
                                            {{ $route->driver ? $route->driver->first_name : ($route->driver_name ?? 'No driver') }}
                                            &middot; {{ $route->formattedMeta() }}
                                            &middot; {{ $routeDone }}/{{ $routeTotal }}
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <a href="{{ route('delivery.driverView', $route->access_token) }}" target="_blank"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline px-1" title="Open driver view">Driver</a>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ url(route('delivery.driverView', $route->access_token, false)) }}').then(() => showToast('Link copied!'))"
                                            class="text-xs text-gray-400 hover:text-gray-600 px-1" title="Copy driver link">Copy</button>
                                        <span class="text-xs font-mono text-base-content/60 px-1" title="Driver PIN">PIN {{ $route->driver_pin }}</span>
                                        <form method="POST" action="{{ route('delivery.markRouteReturning', $route) }}" class="inline" onsubmit="return confirm('Mark as returning?')">
                                            @csrf
                                            <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:underline px-1" title="Mark returning">Return</button>
                                        </form>
                                        <button type="button" onclick="recalcRoute({{ $route->id }}, this)" class="text-xs text-orange-500 hover:underline px-1" title="Recalculate">Recalc</button>
                                        <form method="POST" action="{{ route('santa.deliveryRoutes.destroy', $route) }}" class="inline" onsubmit="return confirm('Delete route?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-primary hover:underline px-1" title="Delete route">Del</button>
                                        </form>
                                    </div>
                                </div>
                                @if($routeTotal > 0)
                                    <div class="w-full bg-base-300 rounded-full h-1 mt-1.5">
                                        <div class="bg-green-500 h-1 rounded-full transition-all" style="width: {{ $routePct }}%"></div>
                                    </div>
                                @endif
                            </div>
                            <!-- Route families -->
                            @if($route->families->count() > 0)
                                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @foreach($route->families as $family)
                                        @include('delivery-day._family-card', ['family' => $family])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="bg-base-100 shadow-xs rounded-lg p-6 text-center">
                            <p class="text-sm text-base-content/60">No routes yet.</p>
                            <p class="text-xs text-base-content/50 mt-1">Click "Assign Driver" to create your first route.</p>
                        </div>
                    @endforelse

                    {{-- Unrouted families summary --}}
                    @if($unroutedEligible->count() > 0)
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                        {{ $unroutedEligible->count() }} families ready to route
                                    </p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                                        Use "Assign Driver" to create a route for them.
                                        @if($routingStats['missing_coords'] > 0)
                                            {{ $routingStats['missing_coords'] }} families missing GPS.
                                        @endif
                                    </p>
                                </div>
                                <button onclick="openQuickAssign()" class="px-3 py-1.5 bg-primary text-white rounded-md hover:opacity-90 text-xs font-medium transition shrink-0">
                                    Assign Driver
                                </button>
                            </div>
                        </div>
                    @endif

                    @if($routes->every(fn($r) => $r->families->isEmpty()) && $unroutedEligible->isEmpty() && !request('status'))
                        <div class="bg-base-100 shadow-xs rounded-lg p-6 text-center text-base-content/50 text-sm">
                            No families match the selected filters.
                        </div>
                    @endif
                </div>
            </div>

            <div class="pt-1">
                <a href="{{ route('santa.index') }}" class="text-sm text-base-content/70 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Quick Assign Driver Modal --}}
    <div id="quick-assign-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeQuickAssign()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-base-100 rounded-xl shadow-2xl max-w-md w-full p-6 relative">
                <button onclick="closeQuickAssign()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-xl">&times;</button>

                {{-- Step 1: Enter driver info --}}
                <div id="qa-step-form">
                    <h3 class="text-lg font-semibold text-base-content mb-1">Create Delivery Route</h3>
                    <p class="text-sm text-base-content/60 mb-4">
                        Enter a driver name and number of families. The system finds nearby families and creates an optimized route.
                    </p>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-base-content/80 mb-1">Assign Driver</label>
                            <select id="qa-driver-user" onchange="prefillDriverName(this)"
                                class="block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                                <option value="">— Type name below —</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" data-name="{{ $driver->first_name }} {{ $driver->last_name }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-base-content/80 mb-1">Driver Name</label>
                            <input type="text" id="qa-driver-name" placeholder="e.g. John Smith" required
                                class="block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-base-content/80 mb-1">Families per route</label>
                            <input type="number" id="qa-batch-size" value="3" min="1" max="25"
                                class="block w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="qa-use-location" class="rounded-sm border-gray-300 text-primary shadow-xs">
                            <label for="qa-use-location" class="text-sm text-base-content/80">Use my location as starting point</label>
                        </div>

                        <button onclick="submitQuickAssign()" id="qa-submit-btn"
                            class="w-full px-4 py-2.5 bg-primary text-white rounded-md hover:opacity-90 text-sm font-semibold transition">
                            Create Route
                        </button>
                    </div>
                </div>

                {{-- Step 2: Loading --}}
                <div id="qa-step-loading" class="hidden text-center py-8">
                    <div class="inline-block w-8 h-8 border-4 border-primary/30 border-t-primary rounded-full animate-spin mb-3"></div>
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
                        <h3 class="text-lg font-semibold text-base-content">Route Created!</h3>
                    </div>

                    <div class="bg-base-200 rounded-lg p-4 mb-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Route</span>
                            <span class="font-medium text-base-content" id="qa-result-name"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Families</span>
                            <span class="font-medium text-base-content" id="qa-result-stops"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Driver PIN</span>
                            <span class="font-mono font-medium text-base-content" id="qa-result-pin"></span>
                        </div>
                    </div>

                    <div id="qa-suggestions" class="hidden mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Nearby Families</p>
                            <span class="text-xs text-base-content/60">Add to this route?</span>
                        </div>
                        <div id="qa-suggestion-list" class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-md p-2 bg-base-100"></div>
                        <button onclick="addSuggestedFamilies()" id="qa-add-suggestions-btn"
                            class="mt-2 w-full px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                            Add Selected
                        </button>
                    </div>

                    <p class="text-sm text-base-content/70 mb-3">Send this link to the driver:</p>
                    <div class="flex gap-2 mb-4">
                        <input type="text" id="qa-result-url" readonly
                            class="flex-1 rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 text-sm bg-gray-50">
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
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 dark:bg-primary/20 mb-2">
                        <svg class="w-6 h-6 text-primary dark:text-primary" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-base-content mb-1">Could not create route</h3>
                    <p class="text-sm text-base-content/60 mb-4" id="qa-error-message"></p>
                    <button onclick="resetQuickAssign()" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium">
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Logs Drawer --}}
    <div id="log-drawer" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/40" onclick="closeLogDrawer()"></div>
        <div class="fixed top-0 right-0 bottom-0 w-full max-w-lg bg-base-100 shadow-2xl flex flex-col">
            <div class="px-4 py-3 border-b border-base-300 flex items-center justify-between shrink-0">
                <h3 class="text-sm font-semibold text-base-content">Recent Delivery Logs</h3>
                <button onclick="closeLogDrawer()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <div id="log-drawer-content" class="flex-1 overflow-y-auto p-4">
                <div class="text-center text-gray-400 py-8">Loading...</div>
            </div>
            <div class="px-4 py-2 border-t border-base-300 shrink-0">
                <a href="{{ route('delivery.logs') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">View all logs &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-60 hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        // ── Toast ───────────────────────────────────────────
        function showToast(msg, color = 'green') {
            const t = document.getElementById('toast');
            const inner = t.querySelector('div');
            inner.className = `bg-${color}-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium`;
            inner.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        // ── Status AJAX ─────────────────────────────────────
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
                        };
                        badge.className = 'status-badge inline-flex px-2 py-0.5 text-xs font-medium rounded-full ' + (colors[data.status] || '');
                    }
                    if (card && data.status === 'delivered') {
                        const list = card.parentElement;
                        if (list) list.appendChild(card);
                    }
                    updateMap(); // Refresh map pins
                }
            })
            .catch(() => showToast('Update failed', 'red'));
        }

        // ── Recalc Route ────────────────────────────────────
        function recalcRoute(routeId, btn) {
            btn.disabled = true;
            const orig = btn.textContent;
            btn.textContent = '...';
            fetch(`/santa/delivery-routes/${routeId}/recalculate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => { showToast(data.ok ? (data.message || 'Route recalculated') : 'Failed', data.ors ? 'green' : 'yellow'); btn.textContent = orig; btn.disabled = false; })
            .catch(() => { showToast('Error', 'red'); btn.textContent = orig; btn.disabled = false; });
        }

        // ── Location Sharing (inline) ───────────────────────
        let locationWatchId = null;
        function toggleLocationSharing() {
            if (locationWatchId !== null) {
                navigator.geolocation.clearWatch(locationWatchId);
                locationWatchId = null;
                document.getElementById('location-label').textContent = 'Share Location';
                const btn = document.getElementById('location-toggle');
                btn.classList.remove('bg-green-600', 'hover:bg-green-500');
                btn.classList.add('bg-gray-600', 'hover:bg-gray-500');
                showToast('Location sharing stopped');
                return;
            }
            if (!navigator.geolocation) {
                showToast('Geolocation not supported', 'red');
                return;
            }
            const btn = document.getElementById('location-toggle');
            btn.classList.remove('bg-gray-600', 'hover:bg-gray-500');
            btn.classList.add('bg-green-600', 'hover:bg-green-500');
            document.getElementById('location-label').textContent = 'Sharing...';

            locationWatchId = navigator.geolocation.watchPosition(
                pos => {
                    document.getElementById('location-label').textContent = 'Sharing Live';
                    fetch('{{ route("delivery.updateLocation") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ latitude: pos.coords.latitude, longitude: pos.coords.longitude }),
                    }).catch(() => {});
                },
                err => {
                    showToast('Location access denied', 'red');
                    locationWatchId = null;
                    document.getElementById('location-label').textContent = 'Share Location';
                    btn.classList.remove('bg-green-600', 'hover:bg-green-500');
                    btn.classList.add('bg-gray-600', 'hover:bg-gray-500');
                },
                { enableHighAccuracy: true, maximumAge: 15000 }
            );
        }

        // ── Logs Drawer ─────────────────────────────────────
        function openLogDrawer() {
            document.getElementById('log-drawer').classList.remove('hidden');
            loadLogs();
        }
        function closeLogDrawer() {
            document.getElementById('log-drawer').classList.add('hidden');
        }
        function loadLogs() {
            fetch('{{ route("delivery.logs") }}', { headers: { 'Accept': 'text/html' } })
                .then(r => r.text())
                .then(html => {
                    // Extract just the table from the full page
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const table = doc.querySelector('table');
                    if (table) {
                        document.getElementById('log-drawer-content').innerHTML = '<div class="overflow-x-auto text-sm">' + table.outerHTML + '</div>';
                    } else {
                        const empty = doc.querySelector('.text-center.text-gray-500');
                        document.getElementById('log-drawer-content').innerHTML = empty ? empty.outerHTML : '<p class="text-gray-400 text-sm text-center py-8">No logs yet.</p>';
                    }
                })
                .catch(() => {
                    document.getElementById('log-drawer-content').innerHTML = '<p class="text-primary text-sm text-center py-8">Failed to load logs.</p>';
                });
        }

        // ── Embedded Map ────────────────────────────────────
        const statusColors = { pending: '#EAB308', in_transit: '#F97316', delivered: '#22C55E' };
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        let map, familyMarkers = [], driverMarkers = [], routeLines = [];
        let mapBoundsSet = false;

        const carSvg = `<svg viewBox="0 0 24 24" fill="white" width="16" height="16"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>`;

        function initMap() {
            map = L.map('delivery-map').setView([48.08, -121.97], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '', maxZoom: 19,
            }).addTo(map);
            updateMap();
            setInterval(updateMap, 12000);
        }

        function updateMap() {
            if (!map) return;
            const showLines = document.getElementById('showRouteLines')?.checked ?? true;
            const showDrivers = document.getElementById('showDrivers')?.checked ?? true;

            fetch('{{ route("delivery.mapData") }}')
                .then(r => r.json())
                .then(data => {
                    familyMarkers.forEach(m => map.removeLayer(m));
                    driverMarkers.forEach(m => map.removeLayer(m));
                    routeLines.forEach(l => map.removeLayer(l));
                    familyMarkers = []; driverMarkers = []; routeLines = [];
                    const bounds = [];

                    (data.families || []).forEach(f => {
                        const color = statusColors[f.status] || '#6B7280';
                        const marker = L.marker([f.lat, f.lng], {
                            icon: L.divIcon({
                                className: '',
                                html: `<div style="background:${color};width:12px;height:12px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                                iconSize: [12, 12], iconAnchor: [6, 6],
                            })
                        }).addTo(map).bindPopup(`<b>#${f.number} ${f.name}</b><br>${f.address}<br><em>${f.status.replace('_', ' ')}</em>`);
                        familyMarkers.push(marker);
                        bounds.push([f.lat, f.lng]);
                    });

                    if (showDrivers && data.drivers) {
                        data.drivers.forEach(d => {
                            const routeColor = data.routes?.find(r => r.id === d.route_id)?.color || '#2563eb';
                            const marker = L.marker([d.lat, d.lng], {
                                icon: L.divIcon({
                                    className: '',
                                    html: `<div style="background:${routeColor};border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.3);">${carSvg}</div>`,
                                    iconSize: [28, 28], iconAnchor: [14, 14],
                                })
                            }).addTo(map).bindPopup(`<b>${d.name}</b><br>${d.updated}`);
                            driverMarkers.push(marker);
                            bounds.push([d.lat, d.lng]);
                        });
                    }

                    if (showLines && data.routes) {
                        data.routes.forEach(r => {
                            if (!r.polyline || r.polyline.length < 2) return;
                            const line = L.polyline(r.polyline, {
                                color: r.color || '#dc2626', weight: 2, opacity: 0.6, dashArray: '6,8',
                            }).addTo(map);
                            routeLines.push(line);
                        });
                    }

                    if (bounds.length > 0 && !mapBoundsSet) {
                        map.fitBounds(bounds, { padding: [20, 20] });
                        mapBoundsSet = true;
                    }

                    document.getElementById('map-last-update').textContent = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
                })
                .catch(() => {
                    document.getElementById('map-last-update').textContent = 'Update failed';
                });
        }

        document.querySelectorAll('#showRouteLines, #showDrivers')
            .forEach(el => el.addEventListener('change', () => { mapBoundsSet = true; updateMap(); }));

        // Init map after DOM ready
        setTimeout(initMap, 200);

        // ── Quick Assign Modal ──────────────────────────────
        function prefillDriverName(sel) {
            const opt = sel.options[sel.selectedIndex];
            if (opt.dataset.name) document.getElementById('qa-driver-name').value = opt.dataset.name;
        }

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
            document.getElementById('qa-suggestions').classList.add('hidden');
            document.getElementById('qa-suggestion-list').innerHTML = '';
            window.qaRouteId = null;
        }

        function submitQuickAssign() {
            const name = document.getElementById('qa-driver-name').value.trim();
            if (!name) { document.getElementById('qa-driver-name').focus(); return; }

            const batchSize = document.getElementById('qa-batch-size').value;
            const useLocation = document.getElementById('qa-use-location').checked;

            document.getElementById('qa-submit-btn').disabled = true;
            document.getElementById('qa-step-form').classList.add('hidden');
            document.getElementById('qa-step-loading').classList.remove('hidden');

            const driverUserId = document.getElementById('qa-driver-user').value;

            const doRequest = (lat, lng) => {
                const body = { driver_name: name, batch_size: parseInt(batchSize) };
                if (driverUserId) body.driver_user_id = driverUserId;
                if (lat && lng) { body.start_lat = lat; body.start_lng = lng; }

                fetch('{{ route("delivery.quickAssign") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
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
                        document.getElementById('qa-result-pin').textContent = data.route.driver_pin;
                        document.getElementById('qa-result-url').value = data.route.driver_url;
                        document.getElementById('qa-result-link').href = data.route.driver_url;
                        window.qaRouteId = data.route.id;
                        renderSuggestions(data.suggested || []);
                        document.getElementById('qa-step-result').classList.remove('hidden');
                        updateMap(); // Refresh map with new route
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

        function renderSuggestions(list) {
            const container = document.getElementById('qa-suggestion-list');
            const wrapper = document.getElementById('qa-suggestions');
            container.innerHTML = '';
            if (!list.length) {
                wrapper.classList.add('hidden');
                return;
            }
            list.forEach(s => {
                const row = document.createElement('label');
                row.className = 'flex items-start gap-2 text-sm text-gray-700 dark:text-gray-200';
                row.innerHTML = `
                    <input type="checkbox" class="mt-1" value="${s.id}">
                    <div>
                        <div class="font-medium">#${s.number} ${s.name}</div>
                        <div class="text-xs text-base-content/60">${s.address}</div>
                        <div class="text-xs text-gray-400">~${s.distance_miles} mi away</div>
                    </div>
                `;
                container.appendChild(row);
            });
            wrapper.classList.remove('hidden');
        }

        function addSuggestedFamilies() {
            const container = document.getElementById('qa-suggestion-list');
            const ids = [...container.querySelectorAll('input[type="checkbox"]:checked')].map(i => i.value);
            if (!ids.length || !window.qaRouteId) return;
            const btn = document.getElementById('qa-add-suggestions-btn');
            btn.disabled = true;
            btn.textContent = 'Adding...';

            fetch(`/delivery-day/routes/${window.qaRouteId}/add-families`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ family_ids: ids }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    btn.textContent = 'Added!';
                    setTimeout(() => btn.textContent = 'Add Selected', 1500);
                    updateMap();
                } else {
                    btn.textContent = 'Failed';
                }
            })
            .catch(() => { btn.textContent = 'Failed'; })
            .finally(() => { btn.disabled = false; });
        }
    </script>
</x-app-layout>
