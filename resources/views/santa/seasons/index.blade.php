<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Season History
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Current Season Stats -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Current Season: {{ $currentYear }}
                    </h3>
                    <div class="flex space-x-3">
                        <a href="{{ route('santa.seasons.import') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">
                            Import Data
                        </a>
                        <form method="POST" action="{{ route('santa.seasons.archive') }}" onsubmit="return confirm('Archive season {{ $currentYear }} and start {{ $currentYear + 1 }}? This will preserve all current data and start a fresh season.')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-amber-600 text-white rounded-md hover:bg-amber-500 text-sm font-medium transition">
                                Archive & Start {{ $currentYear + 1 }}
                            </button>
                        </form>
                    </div>
                </div>

                @if($currentStats)
                    {{-- Overview --}}
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Overview</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_families'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_children'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_adults'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Adults</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_family_members'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total People</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['avg_family_size'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Avg Family Size</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['avg_children_per_family'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Avg Children/Family</div>
                        </div>
                    </div>

                    {{-- Delivery & Demographics side by side --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        {{-- Delivery --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Delivery</h4>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                @foreach($currentStats['families_by_delivery_status'] as $status => $count)
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
                                            'in_transit' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
                                            'delivered' => 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                            'picked_up' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Pending',
                                            'in_transit' => 'In Transit',
                                            'delivered' => 'Delivered',
                                            'picked_up' => 'Picked Up',
                                        ];
                                    @endphp
                                    <div class="rounded-lg p-3 text-center {{ $statusColors[$status] ?? 'bg-gray-50 dark:bg-gray-700' }}">
                                        <div class="text-xl font-bold">{{ $count }}</div>
                                        <div class="text-xs">{{ $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <canvas id="deliveryStatusChart" height="200"></canvas>
                            </div>
                        </div>

                        {{-- Gifts --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Gifts</h4>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-red-50 dark:bg-red-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-red-700 dark:text-red-300">{{ $currentStats['gifts_level_0'] }}</div>
                                    <div class="text-xs text-red-600 dark:text-red-400">No Gifts</div>
                                </div>
                                <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-yellow-700 dark:text-yellow-300">{{ $currentStats['gifts_level_1'] }}</div>
                                    <div class="text-xs text-yellow-600 dark:text-yellow-400">Partial</div>
                                </div>
                                <div class="bg-amber-50 dark:bg-amber-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $currentStats['gifts_level_2'] }}</div>
                                    <div class="text-xs text-amber-600 dark:text-amber-400">Moderate</div>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-green-700 dark:text-green-300">{{ $currentStats['gifts_level_3'] }}</div>
                                    <div class="text-xs text-green-600 dark:text-green-400">Fully Gifted</div>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ $currentStats['tags_adopted'] }}</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400">Tags Adopted</div>
                                </div>
                                <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-indigo-700 dark:text-indigo-300">{{ $currentStats['adoption_rate'] }}%</div>
                                    <div class="text-xs text-indigo-600 dark:text-indigo-400">Adoption Rate</div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <canvas id="giftLevelChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Demographics & Warehouse --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Demographics --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Demographics</h4>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-pink-50 dark:bg-pink-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-pink-700 dark:text-pink-300">{{ $currentStats['families_needing_baby_supplies'] }}</div>
                                    <div class="text-xs text-pink-600 dark:text-pink-400">Need Baby Supplies</div>
                                </div>
                                <div class="bg-orange-50 dark:bg-orange-900/30 rounded-lg p-3 text-center">
                                    <div class="text-xl font-bold text-orange-700 dark:text-orange-300">{{ $currentStats['families_with_pets'] }}</div>
                                    <div class="text-xs text-orange-600 dark:text-orange-400">Families with Pets</div>
                                </div>
                            </div>
                            @if(!empty($currentStats['families_by_language']))
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <h5 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Languages</h5>
                                    <div class="space-y-2">
                                        @foreach($currentStats['families_by_language'] as $language => $count)
                                            @php
                                                $pct = $currentStats['total_families'] > 0 ? round(($count / $currentStats['total_families']) * 100) : 0;
                                            @endphp
                                            <div>
                                                <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300 mb-1">
                                                    <span>{{ $language }}</span>
                                                    <span>{{ $count }} ({{ $pct }}%)</span>
                                                </div>
                                                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Warehouse & Delivery Dates --}}
                        <div>
                            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Warehouse</h4>
                            <div class="bg-teal-50 dark:bg-teal-900/30 rounded-lg p-3 text-center mb-4">
                                <div class="text-2xl font-bold text-teal-700 dark:text-teal-300">{{ number_format($currentStats['total_warehouse_items']) }}</div>
                                <div class="text-xs text-teal-600 dark:text-teal-400">Items Received</div>
                            </div>
                            @if(!empty($currentStats['families_by_delivery_date']))
                                <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Delivery Schedule</h4>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="space-y-2">
                                        @foreach($currentStats['families_by_delivery_date'] as $date => $count)
                                            <div class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
                                                <span>{{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</span>
                                                <span class="font-medium">{{ $count }} {{ Str::plural('family', $count) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Year-over-Year Chart -->
            @if(count($chartYears) > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Year-over-Year</h3>
                    <canvas id="seasonChart" height="100"></canvas>
                </div>
            @endif

            <!-- Past Seasons Table -->
            @if($seasons->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Archived Seasons</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Year</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Families</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Children</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">People</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Delivered</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Archived</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($seasons as $season)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $season->year }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $season->total_families }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $season->total_children }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $season->total_family_members }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $season->deliveries_completed + $season->pickups_completed }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $season->archived_at?->format('M j, Y') ?? 'Imported' }}</td>
                                    <td class="px-6 py-4 text-sm text-right space-x-3">
                                        <a href="{{ route('santa.seasons.show', $season) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Details</a>
                                        <a href="{{ route('santa.seasons.families', $season) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400">Families</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No archived seasons yet. Archive the current season or import historical data to see history here.
                </div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        @if(count($chartYears) > 0)
            new Chart(document.getElementById('seasonChart'), {
                type: 'bar',
                data: {
                    labels: @json(array_reverse($chartYears)),
                    datasets: [
                        {
                            label: 'Families',
                            data: @json(array_reverse($chartFamilies)),
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        },
                        {
                            label: 'Children',
                            data: @json(array_reverse($chartChildren)),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        @endif

        @if($currentStats)
            // Delivery Status Doughnut Chart
            @php
                $dsLabels = [];
                $dsData = [];
                $dsColors = [
                    'pending' => '#eab308',
                    'in_transit' => '#3b82f6',
                    'delivered' => '#22c55e',
                    'picked_up' => '#a855f7',
                ];
                foreach ($currentStats['families_by_delivery_status'] as $status => $count) {
                    $dsLabels[] = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
                    $dsData[] = $count;
                }
            @endphp
            new Chart(document.getElementById('deliveryStatusChart'), {
                type: 'doughnut',
                data: {
                    labels: @json(array_values(['Pending', 'In Transit', 'Delivered', 'Picked Up'])),
                    datasets: [{
                        data: @json(array_values($currentStats['families_by_delivery_status'])),
                        backgroundColor: ['#eab308', '#3b82f6', '#22c55e', '#a855f7'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } }
                    }
                }
            });

            // Gift Level Doughnut Chart
            new Chart(document.getElementById('giftLevelChart'), {
                type: 'doughnut',
                data: {
                    labels: ['No Gifts', 'Partial', 'Moderate', 'Fully Gifted'],
                    datasets: [{
                        data: [
                            {{ $currentStats['gifts_level_0'] }},
                            {{ $currentStats['gifts_level_1'] }},
                            {{ $currentStats['gifts_level_2'] }},
                            {{ $currentStats['gifts_level_3'] }}
                        ],
                        backgroundColor: ['#ef4444', '#eab308', '#f59e0b', '#22c55e'],
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } }
                    }
                }
            });
        @endif
    </script>
</x-app-layout>
