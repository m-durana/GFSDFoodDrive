<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Delivery Routes
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <!-- Create New Route -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Create New Route</h3>
                <form method="POST" action="{{ route('santa.deliveryRoutes.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Route Name</label>
                            <input type="text" name="name" id="name" required placeholder="e.g. Route A, Team Red"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="driver_user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Driver (User)</label>
                            <select name="driver_user_id" id="driver_user_id"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                <option value="">-- None --</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">{{ $driver->first_name }} {{ $driver->last_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="driver_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Or Driver Name (no account)</label>
                            <input type="text" name="driver_name" id="driver_name" placeholder="e.g. John Smith"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                    </div>

                    @if($unroutedFamilies->count() > 0)
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign Families ({{ $unroutedFamilies->count() }} unrouted)</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-md p-3 space-y-1">
                                @foreach($unroutedFamilies as $family)
                                    <label class="flex items-center text-sm">
                                        <input type="checkbox" name="family_ids[]" value="{{ $family->id }}"
                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500 mr-2">
                                        <span class="text-gray-700 dark:text-gray-300">#{{ $family->family_number }} — {{ $family->family_name }} <span class="text-xs text-gray-400">({{ $family->address }})</span></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Create Route
                    </button>
                </form>
            </div>

            <!-- Optimize Routes -->
            @if($routes->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Optimize Routes</h3>
                    @if(empty($orsKey))
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3 mb-4">
                            <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                No OpenRouteService API key configured. <a href="{{ route('santa.settings') }}" class="underline">Set it in Settings</a> to enable route optimization.
                            </p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('santa.deliveryRoutes.optimize') }}" class="space-y-4">
                            @csrf
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Select routes to optimize. The API will reorder stops within each route for shortest travel time.
                            </p>
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
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Latitude</label>
                                    <input type="number" name="start_lat" step="any" value="48.0849" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Longitude</label>
                                    <input type="number" name="start_lng" step="any" value="-121.9686" required
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Default: Granite Falls, WA. This is where drivers start and end.</p>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition"
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
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
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
                                        class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                                        Driver View
                                    </a>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ url(route('delivery.driverView', $route->access_token, false)) }}').then(() => this.textContent = 'Copied!')"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">
                                        Copy Link
                                    </button>
                                    <form method="POST" action="{{ route('santa.deliveryRoutes.destroy', $route) }}" class="inline" onsubmit="return confirm('Delete this route?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-md hover:bg-red-200 dark:hover:bg-red-900/50 text-xs font-medium transition">
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
                                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Navigate</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($route->families as $family)
                                                <tr>
                                                    <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $family->route_order }}</td>
                                                    <td class="px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                        <a href="{{ route('family.show', $family) }}" class="text-blue-600 hover:underline">#{{ $family->family_number }} {{ $family->family_name }}</a>
                                                    </td>
                                                    <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $family->address }}</td>
                                                    <td class="px-3 py-2 text-sm">
                                                        @php $status = $family->delivery_status?->value ?? 'pending'; @endphp
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                                            {{ $status === 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                                            {{ $status === 'in_transit' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                                            {{ $status === 'pending' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                                            {{ $status === 'picked_up' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}">
                                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2 text-sm">
                                                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($family->address) }}"
                                                            target="_blank" class="text-blue-600 hover:underline text-xs">
                                                            Google Maps
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-gray-400 dark:text-gray-500">No families assigned to this route yet.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-400 dark:text-gray-500">
                    <p>No routes created yet. Create a route above to get started.</p>
                </div>
            @endif

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
