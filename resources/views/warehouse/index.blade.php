<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Warehouse Dashboard
            <x-hint key="warehouse-dashboard" text="Track all incoming donations here. The deficit table shows what's still needed. Use Kiosk Mode for fast barcode scanning, or Gift Intake to record child-specific gifts." />
            <x-live-indicator class="ml-3" />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Quick Actions -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <a href="{{ route('warehouse.kiosk') }}" class="block p-4 bg-green-50 dark:bg-green-900/20 rounded-lg shadow-sm hover:bg-green-100 dark:hover:bg-green-900/30 transition border border-green-200 dark:border-green-800 text-center">
                    <div class="text-2xl mb-1">📦</div>
                    <h4 class="font-medium text-green-800 dark:text-green-300">Kiosk Scanner</h4>
                    <p class="text-xs text-green-700/70 dark:text-green-400/60 mt-1">Scan barcodes and log donations fast</p>
                </a>
                <a href="{{ route('warehouse.inventory') }}" class="block p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition border border-blue-200 dark:border-blue-800 text-center">
                    <div class="text-2xl mb-1">📋</div>
                    <h4 class="font-medium text-blue-800 dark:text-blue-300">Inventory</h4>
                    <p class="text-xs text-blue-700/70 dark:text-blue-400/60 mt-1">View stock levels vs. family needs</p>
                </a>
                <a href="{{ route('warehouse.transactions') }}" class="block p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg shadow-sm hover:bg-amber-100 dark:hover:bg-amber-900/30 transition border border-amber-200 dark:border-amber-800 text-center">
                    <div class="text-2xl mb-1">📜</div>
                    <h4 class="font-medium text-amber-800 dark:text-amber-300">Transaction Log</h4>
                    <p class="text-xs text-amber-700/70 dark:text-amber-400/60 mt-1">Full audit trail of all items</p>
                </a>
                <a href="{{ route('warehouse.gifts-intake') }}" class="block p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg shadow-sm hover:bg-purple-100 dark:hover:bg-purple-900/30 transition border border-purple-200 dark:border-purple-800 text-center">
                    <div class="text-2xl mb-1">🎁</div>
                    <h4 class="font-medium text-purple-800 dark:text-purple-300">Gift Intake</h4>
                    <p class="text-xs text-purple-700/70 dark:text-purple-400/60 mt-1">Record child-specific gift drop-offs</p>
                </a>
                <a href="{{ route('warehouse.gift-bank') }}" class="block p-4 bg-pink-50 dark:bg-pink-900/20 rounded-lg shadow-sm hover:bg-pink-100 dark:hover:bg-pink-900/30 transition border border-pink-200 dark:border-pink-800 text-center">
                    <div class="text-2xl mb-1">🏦</div>
                    <h4 class="font-medium text-pink-800 dark:text-pink-300">Gift Bank</h4>
                    <p class="text-xs text-pink-700/70 dark:text-pink-400/60 mt-1">General gifts for matching to children</p>
                </a>
            </div>

            <!-- Inventory Deficit Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Inventory vs. Needs</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-2 px-3 text-gray-500 dark:text-gray-400">Category</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Needed</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">On Hand</th>
                                    <th class="text-right py-2 px-3 text-gray-500 dark:text-gray-400">Surplus / Deficit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deficits as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 px-3 text-gray-900 dark:text-gray-100">
                                            <span class="inline-block w-2 h-2 rounded-full mr-2 {{ $row['category']->type === 'food' ? 'bg-amber-400' : ($row['category']->type === 'gift' ? 'bg-purple-400' : ($row['category']->type === 'baby' ? 'bg-pink-400' : 'bg-blue-400')) }}"></span>
                                            {{ $row['category']->name }}
                                        </td>
                                        <td class="text-right py-2 px-3 text-gray-600 dark:text-gray-400">{{ $row['needed'] }}</td>
                                        <td class="text-right py-2 px-3 text-gray-600 dark:text-gray-400">{{ $row['on_hand'] }}</td>
                                        <td class="text-right py-2 px-3 font-medium {{ $row['deficit'] > 0 ? 'text-red-600 dark:text-red-400' : ($row['deficit'] < 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400') }}">
                                            @if($row['deficit'] > 0)
                                                -{{ $row['deficit'] }}
                                            @elseif($row['deficit'] < 0)
                                                +{{ abs($row['deficit']) }}
                                            @else
                                                &mdash;
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gift Progress by Age Group -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Gift Progress</h3>
                        <div class="space-y-3">
                            @foreach($giftProgress as $gp)
                                <div>
                                    <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                        <span>{{ $gp['category'] }}</span>
                                        <span>{{ $gp['on_hand'] }}/{{ $gp['needed'] }} ({{ $gp['percent'] }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full {{ $gp['percent'] >= 100 ? 'bg-green-500' : ($gp['percent'] >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $gp['percent'] }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Donation Source Breakdown -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Donation Sources</h3>
                        @if($sourceBreakdown->isEmpty())
                            <p class="text-sm text-gray-500 dark:text-gray-400">No donations recorded yet.</p>
                        @else
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="text-left py-2 text-gray-500 dark:text-gray-400">Source</th>
                                        <th class="text-right py-2 text-gray-500 dark:text-gray-400">Items</th>
                                        <th class="text-right py-2 text-gray-500 dark:text-gray-400">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sourceBreakdown as $src)
                                        <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                            <td class="py-2 text-gray-900 dark:text-gray-100">{{ $src->source ?? 'Unknown' }}</td>
                                            <td class="text-right py-2 text-gray-600 dark:text-gray-400">{{ $src->count }}</td>
                                            <td class="text-right py-2 text-gray-600 dark:text-gray-400">{{ $src->total_qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Transactions Feed -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Live Intake Feed</h3>
                        <a href="{{ route('warehouse.transactions') }}" class="text-sm text-red-600 dark:text-red-400 hover:underline">View All</a>
                    </div>
                    <div id="live-feed">
                        @include('warehouse._feed', ['transactions' => $recentTransactions])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Poll for new transactions every 15 seconds
        setInterval(function() {
            fetch('{{ route('warehouse.index') }}?_feed=1', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function(r) { return r.ok ? r.text() : ''; })
              .then(function(html) { if (html) document.getElementById('live-feed').innerHTML = html; })
              .catch(function() {});
        }, 15000);
    </script>
</x-app-layout>
