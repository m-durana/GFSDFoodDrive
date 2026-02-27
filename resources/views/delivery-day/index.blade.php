<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Delivery Day
                <x-hint key="delivery-dispatch" text="Use the Dispatch Board to manage deliveries in real-time. Switch to Route Builder to create delivery routes, or Teams to manage your delivery crews." />
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('delivery.map') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition">
                    Live Map
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

    <div class="py-6" x-data="dispatch()" x-cloak>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

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

            <!-- Tab Bar -->
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                <nav class="flex border-b border-gray-200 dark:border-gray-700">
                    <button @click="tab = 'dispatch'" :class="tab === 'dispatch' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition">
                        Dispatch Board
                    </button>
                    <button @click="tab = 'routes'" :class="tab === 'routes' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition">
                        Route Builder
                    </button>
                    <button @click="tab = 'teams'" :class="tab === 'teams' ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                        class="px-6 py-3 text-sm font-medium border-b-2 transition">
                        Teams
                    </button>
                </nav>
            </div>

            {{-- ═══════════════════ TAB 1: DISPATCH BOARD ═══════════════════ --}}
            <div x-show="tab === 'dispatch'" x-transition class="space-y-4">

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <form method="GET" action="{{ route('delivery.index') }}" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Team</label>
                            <select name="team" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All teams</option>
                                @foreach($teams as $team)
                                    <option value="{{ $team->id }}" {{ request('team') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                @endforeach
                            </select>
                        </div>
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

                <!-- Routes sidebar + main content -->
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Left sidebar: routes list -->
                    <div class="w-full lg:w-64 shrink-0 space-y-2">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 px-1">Routes</h4>
                        @forelse($routes as $route)
                            @php
                                $routeDone = $route->families->filter(fn($f) => in_array($f->delivery_status?->value, ['delivered', 'picked_up']))->count();
                                $routeTotal = $route->families->count();
                                $routeTeam = $route->families->first()?->deliveryTeam;
                            @endphp
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    @if($routeTeam?->color)
                                        <span class="w-3 h-3 rounded-full shrink-0" style="background: {{ $routeTeam->color }}"></span>
                                    @else
                                        <span class="w-3 h-3 rounded-full bg-gray-300 dark:bg-gray-600 shrink-0"></span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $route->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $route->driver ? $route->driver->first_name : ($route->driver_name ?? 'No driver') }}
                                            &middot; {{ $routeDone }}/{{ $routeTotal }}
                                        </div>
                                    </div>
                                </div>
                                @if($routeTotal > 0)
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $routeTotal > 0 ? round(($routeDone/$routeTotal)*100) : 0 }}%"></div>
                                    </div>
                                @endif
                                <div class="flex items-center gap-1 mt-2">
                                    <a href="{{ route('delivery.driverView', $route->access_token) }}" target="_blank" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Driver View</a>
                                    <span class="text-gray-300 dark:text-gray-600">|</span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ url(route('delivery.driverView', $route->access_token, false)) }}').then(() => {this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 1500)})"
                                        class="text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700">Copy Link</button>
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-gray-400 dark:text-gray-500 p-2">No routes yet. Create routes in the Route Builder tab.</div>
                        @endforelse
                    </div>

                    <!-- Main panel: route family cards -->
                    <div class="flex-1 space-y-4">
                        @foreach($routes as $route)
                            @if($route->families->count() > 0)
                                @php
                                    $routeTeam = $route->families->first()?->deliveryTeam;
                                @endphp
                                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                        @if($routeTeam?->color)
                                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $routeTeam->color }}"></span>
                                        @endif
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
            </div>

            {{-- ═══════════════════ TAB 2: ROUTE BUILDER ═══════════════════ --}}
            <div x-show="tab === 'routes'" x-transition class="space-y-6">

                <!-- Create New Route -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create New Route</h3>
                    <form method="POST" action="{{ route('santa.deliveryRoutes.store') }}" class="space-y-4" x-data="{ search: '' }">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Route Name</label>
                                <input type="text" name="name" required placeholder="e.g. Route A"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver (User)</label>
                                <select name="driver_user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Driver Name (no account)</label>
                                <input type="text" name="driver_name" placeholder="e.g. John Smith"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                        </div>

                        @if($unroutedFamilies->count() > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Assign Families ({{ $unroutedFamilies->count() }} unrouted)
                                </label>
                                <input type="text" x-model="search" placeholder="Search families..."
                                    class="mb-2 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3 space-y-1">
                                    @foreach($unroutedFamilies as $family)
                                        <label class="flex items-center text-sm"
                                            x-show="!search || '{{ strtolower($family->family_number . ' ' . $family->family_name . ' ' . $family->address) }}'.includes(search.toLowerCase())">
                                            <input type="checkbox" name="family_ids[]" value="{{ $family->id }}"
                                                class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500 mr-2">
                                            <span class="text-gray-700 dark:text-gray-300">#{{ $family->family_number }} — {{ $family->family_name }}
                                                <span class="text-xs text-gray-400">({{ Str::limit($family->address, 40) }})</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 dark:text-gray-500">All geocoded families are already assigned to routes.</p>
                        @endif

                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Create Route
                        </button>
                    </form>
                </div>

                <!-- Optimize Routes -->
                @if($routes->count() > 0)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Optimize Routes</h3>
                        @if(empty($orsKey))
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    No OpenRouteService API key configured. <a href="{{ route('santa.settings') }}" class="underline">Set it in Settings</a>.
                                </p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('santa.deliveryRoutes.optimize') }}" class="space-y-4">
                                @csrf
                                <p class="text-sm text-gray-500 dark:text-gray-400">Reorders stops within each route for shortest travel time.</p>
                                <div class="space-y-2">
                                    @foreach($routes as $route)
                                        <label class="flex items-center text-sm">
                                            <input type="checkbox" name="route_ids[]" value="{{ $route->id }}" checked
                                                class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500 mr-2">
                                            <span class="text-gray-700 dark:text-gray-300">{{ $route->name }} ({{ $route->stop_count }} stops)</span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-2 gap-4 max-w-sm">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Lat</label>
                                        <input type="number" name="start_lat" step="any" value="48.0849" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Lng</label>
                                        <input type="number" name="start_lng" step="any" value="-121.9686" required
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">Default: Granite Falls, WA.</p>
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition"
                                    onclick="this.textContent='Optimizing...'; this.disabled=true; this.form.submit();">
                                    Optimize Selected Routes
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                <!-- Existing Routes -->
                @if($routes->count() > 0)
                    <div class="space-y-4">
                        @foreach($routes as $route)
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $route->name }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Driver: {{ $route->driver ? $route->driver->first_name . ' ' . $route->driver->last_name : ($route->driver_name ?? 'Unassigned') }}
                                            &middot; {{ $route->stop_count }} stops
                                            &middot; {{ $route->formattedDistance() }}
                                            &middot; {{ $route->formattedDuration() }}
                                        </p>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('delivery.driverView', $route->access_token) }}" target="_blank"
                                            class="px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                                            Driver View
                                        </a>
                                        <form method="POST" action="{{ route('santa.deliveryRoutes.destroy', $route) }}" class="inline" onsubmit="return confirm('Delete this route?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-md hover:bg-red-200 text-xs font-medium transition">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if($route->families->count() > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                            <thead class="bg-gray-50 dark:bg-gray-700">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Address</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                @foreach($route->families as $family)
                                                    <tr>
                                                        <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $family->route_order }}</td>
                                                        <td class="px-3 py-2 text-sm">
                                                            <a href="{{ route('family.show', $family) }}" class="text-blue-600 hover:underline">#{{ $family->family_number }} {{ $family->family_name }}</a>
                                                        </td>
                                                        <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $family->address }}</td>
                                                        <td class="px-3 py-2 text-sm">
                                                            @php $status = $family->delivery_status?->value ?? 'pending'; @endphp
                                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                                {{ $status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                                {{ $status === 'in_transit' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300' : '' }}
                                                                {{ $status === 'pending' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                                                {{ $status === 'picked_up' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400">No families assigned yet.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- ═══════════════════ TAB 3: TEAMS ═══════════════════ --}}
            <div x-show="tab === 'teams'" x-transition class="space-y-6">

                <!-- Create Team -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create Team</h3>
                    <form method="POST" action="{{ route('santa.deliveryTeams.store') }}" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Team Name</label>
                                <input type="text" name="name" required placeholder="e.g. Red Team"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <input type="color" name="color" value="#dc2626" class="h-9 w-12 rounded border-gray-300 cursor-pointer">
                                    <div class="flex gap-1">
                                        @foreach(['#dc2626','#2563eb','#16a34a','#d97706','#9333ea','#db2777'] as $preset)
                                            <button type="button" onclick="this.closest('form').querySelector('[name=color]').value='{{ $preset }}'"
                                                class="w-6 h-6 rounded-full border-2 border-white shadow-sm" style="background: {{ $preset }}"></button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver (User)</label>
                                <select name="driver_user_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                    <option value="">-- None --</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Driver Name</label>
                                <input type="text" name="driver_name" placeholder="e.g. John Smith"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes..."
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Create Team
                        </button>
                    </form>
                </div>

                <!-- Existing Teams -->
                @if($teams->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($teams as $team)
                            @php
                                $teamDelivered = $team->families->filter(fn($f) => in_array($f->delivery_status?->value, ['delivered', 'picked_up']))->count();
                                $teamTotal = $team->families_count;
                            @endphp
                            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-5 border-l-4" style="border-left-color: {{ $team->color ?? '#6b7280' }}">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ $team->name }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Driver: {{ $team->getDriverDisplayName() }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $teamDelivered }}/{{ $teamTotal }}</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">delivered</p>
                                    </div>
                                </div>

                                @if($teamTotal > 0)
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-3">
                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ round(($teamDelivered/$teamTotal)*100) }}%"></div>
                                    </div>
                                @endif

                                @if($team->notes)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $team->notes }}</p>
                                @endif

                                <!-- Edit form -->
                                <details class="mt-2">
                                    <summary class="text-xs text-blue-600 dark:text-blue-400 cursor-pointer hover:underline">Edit</summary>
                                    <form method="POST" action="{{ route('santa.deliveryTeams.update', $team) }}" class="mt-3 space-y-3">
                                        @csrf @method('PUT')
                                        <input type="text" name="name" value="{{ $team->name }}" required
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                        <div class="flex items-center gap-2">
                                            <input type="color" name="color" value="{{ $team->color ?? '#6b7280' }}" class="h-8 w-10 rounded border-gray-300 cursor-pointer">
                                            <select name="driver_user_id" class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                <option value="">-- No driver --</option>
                                                @foreach($drivers as $driver)
                                                    <option value="{{ $driver->id }}" {{ $team->driver_user_id == $driver->id ? 'selected' : '' }}>
                                                        {{ $driver->first_name }} {{ $driver->last_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <input type="text" name="driver_name" value="{{ $team->driver_name }}" placeholder="Or driver name"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                        <textarea name="notes" rows="2" placeholder="Notes"
                                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">{{ $team->notes }}</textarea>
                                        <div class="flex items-center gap-2">
                                            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium">Save</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('santa.deliveryTeams.destroy', $team) }}" class="mt-2" onsubmit="return confirm('Delete team {{ $team->name }}? Families will be unassigned.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">Delete Team</button>
                                    </form>
                                </details>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-center text-gray-400">
                        No teams created yet.
                    </div>
                @endif
            </div>

            <div class="pt-2">
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Toast notification -->
    <div id="toast" class="fixed bottom-4 right-4 z-50 hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <script>
        function dispatch() {
            const params = new URLSearchParams(window.location.search);
            return {
                tab: params.get('tab') || 'dispatch',
            };
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
    </script>
</x-app-layout>
