<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Delivery Day
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
                    View All Logs
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats cards -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['needs_delivery'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Need Delivery</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Pending</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $stats['in_transit'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">In Transit</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['delivered'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Delivered</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['picked_up'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Picked Up</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">
                <form method="GET" action="{{ route('delivery.index') }}" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Team</label>
                        <select name="team" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All teams</option>
                            @foreach($teams as $team)
                                <option value="{{ $team }}" {{ request('team') == $team ? 'selected' : '' }}>{{ $team }}</option>
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
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Delivery Date</label>
                        <select name="date" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            <option value="">All dates</option>
                            @foreach($dates as $date)
                                <option value="{{ $date }}" {{ request('date') == $date ? 'selected' : '' }}>{{ $date }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Filter
                    </button>
                    <a href="{{ route('delivery.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700">Reset</a>
                </form>
            </div>

            <!-- Families grouped by team -->
            @forelse($grouped as $teamName => $teamFamilies)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        {{ $teamName }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $teamFamilies->count() }} {{ Str::plural('family', $teamFamilies->count()) }})</span>
                    </h3>

                    <div class="space-y-4">
                        @foreach($teamFamilies as $family)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $family->delivery_status?->value === 'delivered' || $family->delivery_status?->value === 'picked_up' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <!-- Family info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-lg font-bold text-gray-900 dark:text-gray-100">#{{ $family->family_number }}</span>
                                            <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">{{ $family->family_name }}</a>
                                            @if($family->delivery_status)
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                        'in_transit' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                                        'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                        'picked_up' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                    ];
                                                @endphp
                                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$family->delivery_status->value] ?? '' }}">
                                                    {{ $family->delivery_status->label() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-300 space-y-0.5">
                                            <div>{{ $family->address }}</div>
                                            <div>{{ $family->phone1 }}@if($family->phone2) / {{ $family->phone2 }}@endif</div>
                                            @if($family->delivery_preference)
                                                <div><span class="font-medium">Pref:</span> {{ $family->delivery_preference }}@if($family->delivery_date) — {{ $family->delivery_date }}@endif @if($family->delivery_time) {{ $family->delivery_time }}@endif</div>
                                            @endif
                                            @if($family->delivery_reason)
                                                <div class="text-red-600 dark:text-red-400"><span class="font-medium">Reason:</span> {{ $family->delivery_reason }}</div>
                                            @endif
                                            @if($family->pet_information)
                                                <div class="text-amber-600 dark:text-amber-400">Pets: {{ $family->pet_information }}</div>
                                            @endif
                                            @if($family->preferred_language && $family->preferred_language !== 'English')
                                                <div class="text-blue-600 dark:text-blue-400">Language: {{ $family->preferred_language }}</div>
                                            @endif
                                            <div class="text-gray-500 dark:text-gray-400">
                                                {{ $family->number_of_family_members }} members ({{ $family->number_of_children }} children)
                                                @if($family->volunteer) — Vol: {{ $family->volunteer->first_name }} {{ $family->volunteer->last_name }}@endif
                                            </div>
                                        </div>

                                        <!-- Recent logs -->
                                        @if($family->deliveryLogs->count() > 0)
                                            <div class="mt-2 space-y-1">
                                                @foreach($family->deliveryLogs->take(3) as $log)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        <span class="font-medium">{{ $log->created_at->format('M j g:ia') }}</span>
                                                        — {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                                        @if($log->user) by {{ $log->user->first_name }}@endif
                                                        @if($log->notes) — {{ $log->notes }}@endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-col space-y-2 w-52 shrink-0">
                                        <!-- Team assignment -->
                                        <form method="POST" action="{{ route('delivery.updateTeam', $family) }}" class="flex items-center gap-1">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="delivery_team" value="{{ $family->delivery_team }}" placeholder="Team..."
                                                   class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs px-2 py-1">
                                            <button type="submit" class="shrink-0 px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-200">Set</button>
                                        </form>

                                        <!-- Quick status update -->
                                        <form method="POST" action="{{ route('delivery.updateStatus', $family) }}" class="flex items-center gap-1">
                                            @csrf
                                            @method('PUT')
                                            <select name="delivery_status" class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-7 py-1">
                                                <option value="pending" {{ $family->delivery_status?->value === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="in_transit" {{ $family->delivery_status?->value === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                                                <option value="delivered" {{ $family->delivery_status?->value === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                <option value="picked_up" {{ $family->delivery_status?->value === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                                            </select>
                                            <button type="submit" class="shrink-0 px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-600">Go</button>
                                        </form>

                                        <!-- Add log note -->
                                        <form method="POST" action="{{ route('delivery.addLog', $family) }}" class="flex flex-col gap-1">
                                            @csrf
                                            <select name="status" class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs pl-2 pr-7 py-1">
                                                <option value="delivered">Delivered</option>
                                                <option value="left_at_door">Left at door</option>
                                                <option value="no_answer">No answer</option>
                                                <option value="attempted">Attempted</option>
                                                <option value="picked_up">Picked up</option>
                                                <option value="note">Note</option>
                                            </select>
                                            <div class="flex items-center gap-1">
                                                <input type="text" name="notes" placeholder="Notes..."
                                                       class="flex-1 min-w-0 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-xs px-2 py-1">
                                                <button type="submit" class="shrink-0 px-2 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-500">Log</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No families match the selected filters. Assign family numbers first.
                </div>
            @endforelse

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
