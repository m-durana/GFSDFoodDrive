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
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_families'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_children'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_family_members'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Total People</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['tags_adopted'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Tags Adopted</div>
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

    @if(count($chartYears) > 0)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script>
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
        </script>
    @endif
</x-app-layout>
