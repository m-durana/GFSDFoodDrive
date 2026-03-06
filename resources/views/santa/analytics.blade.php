<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Analytics Dashboard
            </h2>
            <a href="{{ route('santa.analytics.export') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700 transition">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpis['total_years']) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Years Running</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpis['all_time_families']) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">All-Time Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpis['all_time_children']) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">All-Time Children</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpis['all_time_people']) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">All-Time People Served</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($kpis['avg_families_per_year']) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Avg Families/Year</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    @if($kpis['current_year_growth_families'] !== null)
                        <div class="text-2xl font-bold {{ $kpis['current_year_growth_families'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $kpis['current_year_growth_families'] > 0 ? '+' : '' }}{{ $kpis['current_year_growth_families'] }}%
                        </div>
                    @else
                        <div class="text-2xl font-bold text-gray-400">--</div>
                    @endif
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Family Growth YoY</div>
                </div>
            </div>

            {{-- Current Season Snapshot --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $currentYear }} Season Snapshot</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_families'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Families</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_children'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Children</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['total_family_members'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">People</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['tags_adopted'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Tags Adopted</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['adoption_rate'] }}%</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Adoption Rate</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['deliveries_completed'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Deliveries Done</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['avg_family_size'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Avg Family Size</div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $currentStats['families_severe_need'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Severe Need</div>
                    </div>
                </div>
            </div>

            {{-- Chart Row 1: Trends --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Families & Children Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Families & Children Over Time</h3>
                    <div class="relative h-64"><canvas id="trendChart"></canvas></div>
                </div>

                {{-- Growth Rate --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Year-Over-Year Growth %</h3>
                    <div class="relative h-64"><canvas id="growthChart"></canvas></div>
                </div>
            </div>

            {{-- Chart Row 2: Gift Program --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Gift Levels Stacked Bar --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Gift Level Distribution by Year</h3>
                    <div class="relative h-64"><canvas id="giftLevelChart"></canvas></div>
                </div>

                {{-- Adoption Rate Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Tag Adoption Rate Trend</h3>
                    <div class="relative h-64"><canvas id="adoptionChart"></canvas></div>
                </div>
            </div>

            {{-- Chart Row 3: Demographics --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Age Groups (current year) --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Children by Age Group ({{ $currentYear }})</h3>
                    <div class="relative h-60"><canvas id="ageGroupChart"></canvas></div>
                </div>

                {{-- Family Size Distribution (current year) --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Family Size Distribution ({{ $currentYear }})</h3>
                    <div class="relative h-60"><canvas id="familySizeChart"></canvas></div>
                </div>

                {{-- Languages (current year) --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Preferred Language ({{ $currentYear }})</h3>
                    <div class="relative h-60"><canvas id="languageChart"></canvas></div>
                </div>
            </div>

            {{-- Chart Row 4: Delivery & Operations --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Delivery Completion Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Deliveries Completed by Year</h3>
                    <div class="relative h-64"><canvas id="deliveryChart"></canvas></div>
                </div>

                {{-- Avg Family Size Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Average Family Size Over Time</h3>
                    <div class="relative h-64"><canvas id="avgSizeChart"></canvas></div>
                </div>
            </div>

            {{-- School Breakdown Table (current year) --}}
            @if(!empty($currentStats['families_by_school']))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Families by School ({{ $currentYear }})</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" x-data="sortTable()">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <x-sort-th key="school">School</x-sort-th>
                                <x-sort-th key="families" class="text-right">Families</x-sort-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($currentStats['families_by_school'] as $school => $count)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $school }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $count }}">{{ $count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Year-Over-Year Data Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Year-Over-Year Data</h3>
                    <a href="{{ route('santa.analytics.export') }}" class="text-xs text-green-600 dark:text-green-400 hover:underline">Download CSV</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm" x-data="sortTable()">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <x-sort-th key="year">Year</x-sort-th>
                                <x-sort-th key="families" class="text-right">Families</x-sort-th>
                                <x-sort-th key="children" class="text-right">Children</x-sort-th>
                                <x-sort-th key="people" class="text-right">People</x-sort-th>
                                <x-sort-th key="avgsize" class="text-right">Avg Size</x-sort-th>
                                <x-sort-th key="adopted" class="text-right">Tags Adopted</x-sort-th>
                                <x-sort-th key="rate" class="text-right">Adoption %</x-sort-th>
                                <x-sort-th key="deliveries" class="text-right">Deliveries</x-sort-th>
                                <x-sort-th key="growth" class="text-right">Growth %</x-sort-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($sortedYears as $year)
                                @php $s = $allYearStats[$year]; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $year == $currentYear ? 'bg-green-50 dark:bg-green-900/20 font-medium' : '' }}">
                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                    {{ $year }}
                                    @if($year == $currentYear) <span class="text-xs text-green-600 dark:text-green-400 ml-1">(current)</span> @endif
                                </td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['total_families'] }}">{{ number_format($s['total_families']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['total_children'] }}">{{ number_format($s['total_children']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['total_family_members'] }}">{{ number_format($s['total_family_members']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['avg_family_size'] }}">{{ $s['avg_family_size'] }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['tags_adopted'] }}">{{ number_format($s['tags_adopted']) }}</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['adoption_rate'] }}">{{ $s['adoption_rate'] }}%</td>
                                <td class="px-3 py-2 text-right text-gray-600 dark:text-gray-300" data-sort-value="{{ $s['deliveries_completed'] }}">{{ number_format($s['deliveries_completed']) }}</td>
                                <td class="px-3 py-2 text-right {{ isset($growthRates[$year]) && $growthRates[$year]['families'] !== null ? ($growthRates[$year]['families'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400') : 'text-gray-400' }}"
                                    data-sort-value="{{ $growthRates[$year]['families'] ?? 0 }}">
                                    @if(isset($growthRates[$year]) && $growthRates[$year]['families'] !== null)
                                        {{ $growthRates[$year]['families'] > 0 ? '+' : '' }}{{ $growthRates[$year]['families'] }}%
                                    @else
                                        --
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.08)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            Chart.defaults.color = textColor;
            Chart.defaults.borderColor = gridColor;

            const years = @json($sortedYears);
            const allStats = @json($allYearStats);
            const growthRates = @json($growthRates);

            // --- Trend Chart: Families & Children ---
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [
                        {
                            label: 'Families',
                            data: years.map(y => allStats[y]?.total_families ?? 0),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239,68,68,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Children',
                            data: years.map(y => allStats[y]?.total_children ?? 0),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59,130,246,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Total People',
                            data: years.map(y => allStats[y]?.total_family_members ?? 0),
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.1)',
                            fill: true,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // --- Growth Rate Chart ---
            const growthYears = years.filter(y => growthRates[y]);
            new Chart(document.getElementById('growthChart'), {
                type: 'bar',
                data: {
                    labels: growthYears,
                    datasets: [
                        {
                            label: 'Families Growth %',
                            data: growthYears.map(y => growthRates[y]?.families ?? 0),
                            backgroundColor: growthYears.map(y => (growthRates[y]?.families ?? 0) >= 0 ? 'rgba(16,185,129,0.7)' : 'rgba(239,68,68,0.7)'),
                            borderRadius: 4,
                        },
                        {
                            label: 'Children Growth %',
                            data: growthYears.map(y => growthRates[y]?.children ?? 0),
                            backgroundColor: growthYears.map(y => (growthRates[y]?.children ?? 0) >= 0 ? 'rgba(59,130,246,0.7)' : 'rgba(249,115,22,0.7)'),
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                    scales: { y: { ticks: { callback: v => v + '%' } } }
                }
            });

            // --- Gift Level Stacked Bar ---
            new Chart(document.getElementById('giftLevelChart'), {
                type: 'bar',
                data: {
                    labels: years,
                    datasets: [
                        { label: 'No Gifts', data: years.map(y => allStats[y]?.gifts_level_0 ?? 0), backgroundColor: '#ef4444', borderRadius: 2 },
                        { label: 'Partial', data: years.map(y => allStats[y]?.gifts_level_1 ?? 0), backgroundColor: '#f59e0b', borderRadius: 2 },
                        { label: 'Moderate', data: years.map(y => allStats[y]?.gifts_level_2 ?? 0), backgroundColor: '#3b82f6', borderRadius: 2 },
                        { label: 'Full', data: years.map(y => allStats[y]?.gifts_level_3 ?? 0), backgroundColor: '#10b981', borderRadius: 2 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                    scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
                }
            });

            // --- Adoption Rate Line ---
            new Chart(document.getElementById('adoptionChart'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Adoption Rate %',
                        data: years.map(y => allStats[y]?.adoption_rate ?? 0),
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139,92,246,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 5,
                        pointBackgroundColor: '#8b5cf6',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
                }
            });

            // --- Age Group Doughnut (current year) ---
            const ageData = allStats[{{ $currentYear }}]?.children_by_age_group ?? {};
            const ageLabels = Object.keys(ageData);
            const ageValues = Object.values(ageData);
            new Chart(document.getElementById('ageGroupChart'), {
                type: 'doughnut',
                data: {
                    labels: ageLabels,
                    datasets: [{
                        data: ageValues,
                        backgroundColor: ['#f87171', '#fb923c', '#fbbf24', '#34d399', '#60a5fa'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
                }
            });

            // --- Family Size Bar (current year) ---
            const sizeData = allStats[{{ $currentYear }}]?.families_by_size ?? {};
            new Chart(document.getElementById('familySizeChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(sizeData),
                    datasets: [{
                        label: 'Families',
                        data: Object.values(sizeData),
                        backgroundColor: ['#60a5fa', '#34d399', '#fbbf24', '#f87171'],
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // --- Language Pie (current year) ---
            const langData = allStats[{{ $currentYear }}]?.families_by_language ?? {};
            const langLabels = Object.keys(langData);
            const langValues = Object.values(langData);
            new Chart(document.getElementById('languageChart'), {
                type: 'pie',
                data: {
                    labels: langLabels.length ? langLabels : ['No data'],
                    datasets: [{
                        data: langValues.length ? langValues : [1],
                        backgroundColor: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } }
                }
            });

            // --- Deliveries Completed Bar ---
            new Chart(document.getElementById('deliveryChart'), {
                type: 'bar',
                data: {
                    labels: years,
                    datasets: [{
                        label: 'Deliveries',
                        data: years.map(y => allStats[y]?.deliveries_completed ?? 0),
                        backgroundColor: 'rgba(239,68,68,0.7)',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });

            // --- Avg Family Size Line ---
            new Chart(document.getElementById('avgSizeChart'), {
                type: 'line',
                data: {
                    labels: years,
                    datasets: [
                        {
                            label: 'Avg Family Size',
                            data: years.map(y => allStats[y]?.avg_family_size ?? 0),
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245,158,11,0.1)',
                            fill: true,
                            tension: 0.3,
                        },
                        {
                            label: 'Avg Children/Family',
                            data: years.map(y => allStats[y]?.avg_children_per_family ?? 0),
                            borderColor: '#8b5cf6',
                            backgroundColor: 'rgba(139,92,246,0.1)',
                            fill: true,
                            tension: 0.3,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
