<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Packing Dashboard
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('packing.summary') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                    Summary
                </a>
                <a href="{{ route('packing.index') }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-gray-600 text-white hover:bg-gray-700 transition">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    All Lists
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="packingDashboard()" x-init="fetchStats()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Fulfillment Alert Banner --}}
            <template x-if="stats.fulfillment_alert === true">
                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">
                                Fulfillment Rate Below Threshold
                            </p>
                            <p class="text-xs text-amber-600 dark:text-amber-400">
                                Current rate: <span x-text="stats.fulfillment_rate"></span>% — Threshold: <span x-text="stats.fulfillment_threshold"></span>%
                            </p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Row 1: KPI Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.total_families">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Families</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-gray-500 dark:text-gray-400" x-text="stats.not_started">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Not Started</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" x-text="stats.in_progress">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">In Progress</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="stats.packed">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Complete / QA Ready</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400" x-text="stats.verified">-</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Verified</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400"><span x-text="stats.fulfillment_rate">-</span>%</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fulfillment Rate</div>
                </div>
            </div>

            {{-- Unfulfilled Slots Alert --}}
            <template x-if="stats.unfulfilled_items > 0">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-red-800 dark:text-red-300">
                                    <span x-text="stats.unfulfilled_items"></span> Unfulfilled Slots across
                                    <span x-text="stats.unfulfilled_families"></span> families
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400">Items needing coordinator review (dietary conflicts, missing gifts, etc.)</p>
                            </div>
                        </div>
                        <a href="{{ route('packing.index', ['status' => 'unfulfilled']) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition">
                            View Affected
                        </a>
                    </div>
                </div>
            </template>

            {{-- Row 2: Charts --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Status Breakdown</h3>
                    <div class="relative" style="height: 260px">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Category Progress</h3>
                    <div class="relative" style="height: 260px">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Row 3: Global Metrics + Session/QA --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Today's Metrics with Trend --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Today's Metrics</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.total_items_packed_today">0</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Items Packed Today</div>
                        </div>
                        <div>
                            <div class="flex items-center gap-1">
                                <span class="text-3xl font-bold text-gray-900 dark:text-gray-100" x-text="stats.overall_items_per_hour">0</span>
                                <template x-if="stats.trend?.trend_direction === 'up'">
                                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" /></svg>
                                </template>
                                <template x-if="stats.trend?.trend_direction === 'down'">
                                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 4.5l15 15m0 0V8.25m0 11.25H8.25" /></svg>
                                </template>
                                <template x-if="stats.trend?.trend_direction === 'flat'">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                                </template>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Items / Hour
                                <span x-show="stats.trend" class="text-gray-400" x-text="'vs yesterday'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Volunteer Sessions --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Volunteer Activity</h3>
                        <template x-if="stats.trend?.active_sessions > 0">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300">
                                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                <span x-text="stats.trend.active_sessions"></span> active
                            </span>
                        </template>
                    </div>
                    <div class="space-y-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span x-text="stats.trend?.today_sessions_count || 0"></span> sessions today
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span x-text="(stats.volunteers || []).length"></span> volunteers active today
                        </div>
                    </div>
                </div>

                {{-- QA Ready --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Ready for QA Verification</h3>
                    <template x-if="stats.packed > 0">
                        <div>
                            <div class="flex items-center gap-2 mb-3">
                                <span class="relative flex h-3 w-3">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                                </span>
                                <span class="text-sm font-medium text-blue-700 dark:text-blue-300" x-text="stats.packed + ' list(s) ready for QA'"></span>
                            </div>
                            <a href="{{ route('packing.index', ['status' => 'complete']) }}" class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition">
                                Review Now
                            </a>
                        </div>
                    </template>
                    <template x-if="stats.packed == 0">
                        <p class="text-sm text-gray-500 dark:text-gray-400">No lists awaiting QA verification.</p>
                    </template>
                </div>
            </div>

            {{-- Row 4: Volunteer Stats --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6 pb-2">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Volunteer Activity (Today)</h3>
                </div>
                <div class="overflow-x-auto" x-data="sortTable()">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <x-sort-th key="name">Volunteer</x-sort-th>
                                <x-sort-th key="items_packed">Items Packed</x-sort-th>
                                <x-sort-th key="lists_worked">Lists Worked</x-sort-th>
                                <x-sort-th key="items_per_hour">Items/Hour</x-sort-th>
                                <x-sort-th key="last_packed_at">Last Active</x-sort-th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <template x-for="v in stats.volunteers || []" :key="v.id">
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="v.name" data-sort-value="v.name"></td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="v.items_packed" :data-sort-value="v.items_packed"></td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="v.lists_worked" :data-sort-value="v.lists_worked"></td>
                                    <td class="px-6 py-3 text-sm text-gray-900 dark:text-gray-100" x-text="v.items_per_hour" :data-sort-value="v.items_per_hour"></td>
                                    <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400" x-text="v.last_packed_at ? new Date(v.last_packed_at).toLocaleTimeString() : '-'"></td>
                                </tr>
                            </template>
                            <template x-if="!stats.volunteers || stats.volunteers.length === 0">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No volunteer activity recorded today.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Auto-refresh indicator --}}
            <div class="text-center text-xs text-gray-400 dark:text-gray-500">
                Auto-refreshes every 20 seconds &middot; Last updated: <span x-text="lastUpdated"></span>
            </div>
        </div>

        {{-- Toast notifications --}}
        <div class="fixed bottom-4 right-4 z-50 space-y-2" id="toast-area">
            <template x-for="toast in toasts" :key="toast.id">
                <div class="bg-blue-600 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-slide-in-right"
                     x-show="toast.visible"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 translate-x-0"
                     x-transition:leave-end="opacity-0 translate-x-4">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                    <span class="text-sm" x-text="toast.message"></span>
                </div>
            </template>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        function packingDashboard() {
            return {
                stats: {},
                lastUpdated: '-',
                toasts: [],
                seenCompletions: new Set(),
                statusChart: null,
                categoryChart: null,
                interval: null,

                async fetchStats() {
                    try {
                        const res = await fetch('/api/packing/stats');
                        this.stats = await res.json();
                        this.lastUpdated = new Date().toLocaleTimeString();

                        this.updateCharts();
                        this.checkNewCompletions();
                    } catch (e) {
                        console.error('Failed to fetch packing stats', e);
                    }

                    if (!this.interval) {
                        this.interval = setInterval(() => this.fetchStats(), 20000);
                    }
                },

                updateCharts() {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#9ca3af' : '#6b7280';

                    // Status doughnut
                    const statusData = {
                        labels: ['Not Started', 'In Progress', 'Complete', 'Verified'],
                        datasets: [{
                            data: [
                                this.stats.not_started || 0,
                                this.stats.in_progress || 0,
                                this.stats.packed || 0,
                                this.stats.verified || 0,
                            ],
                            backgroundColor: ['#9ca3af', '#eab308', '#3b82f6', '#22c55e'],
                            borderWidth: 0,
                        }],
                    };

                    if (this.statusChart) {
                        this.statusChart.data = statusData;
                        this.statusChart.update();
                    } else {
                        const ctx = document.getElementById('statusChart');
                        if (ctx) {
                            this.statusChart = new Chart(ctx, {
                                type: 'doughnut',
                                data: statusData,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: textColor, padding: 16 } },
                                    },
                                },
                            });
                        }
                    }

                    // Category stacked bar
                    const cats = this.stats.categories || {};
                    const categoryData = {
                        labels: ['Food', 'Gift', 'Baby'],
                        datasets: [
                            {
                                label: 'Packed',
                                data: [cats.food?.packed || 0, cats.gift?.packed || 0, cats.baby?.packed || 0],
                                backgroundColor: '#22c55e',
                            },
                            {
                                label: 'Remaining',
                                data: [
                                    (cats.food?.total || 0) - (cats.food?.packed || 0),
                                    (cats.gift?.total || 0) - (cats.gift?.packed || 0),
                                    (cats.baby?.total || 0) - (cats.baby?.packed || 0),
                                ],
                                backgroundColor: '#e5e7eb',
                            },
                        ],
                    };

                    if (this.categoryChart) {
                        this.categoryChart.data = categoryData;
                        this.categoryChart.update();
                    } else {
                        const ctx = document.getElementById('categoryChart');
                        if (ctx) {
                            this.categoryChart = new Chart(ctx, {
                                type: 'bar',
                                data: categoryData,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        x: { stacked: true, ticks: { color: textColor } },
                                        y: { stacked: true, beginAtZero: true, ticks: { color: textColor } },
                                    },
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: textColor, padding: 16 } },
                                    },
                                },
                            });
                        }
                    }
                },

                checkNewCompletions() {
                    const recent = this.stats.recently_completed || [];
                    recent.forEach(item => {
                        if (!this.seenCompletions.has(item.id)) {
                            this.seenCompletions.add(item.id);
                            this.showToast(`#${item.family_number} ${item.family_name} packing complete!`);
                        }
                    });
                },

                showToast(message) {
                    const id = Date.now();
                    this.toasts.push({ id, message, visible: true });
                    setTimeout(() => {
                        const toast = this.toasts.find(t => t.id === id);
                        if (toast) toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 400);
                    }, 5000);
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
