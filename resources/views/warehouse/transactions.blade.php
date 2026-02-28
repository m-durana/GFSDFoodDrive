<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Transaction Log
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form method="GET" action="{{ route('warehouse.transactions') }}" class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Type</label>
                            <select name="type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Types</option>
                                @foreach(\App\Enums\TransactionType::cases() as $type)
                                    <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Category</label>
                            <select name="category_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Source</label>
                            <select name="source" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                <option value="">All Sources</option>
                                <option value="School Drive" {{ request('source') === 'School Drive' ? 'selected' : '' }}>School Drive</option>
                                <option value="Adopt-a-Tag" {{ request('source') === 'Adopt-a-Tag' ? 'selected' : '' }}>Adopt-a-Tag</option>
                                <option value="Community Donation" {{ request('source') === 'Community Donation' ? 'selected' : '' }}>Community Donation</option>
                                <option value="Store Purchase" {{ request('source') === 'Store Purchase' ? 'selected' : '' }}>Store Purchase</option>
                                <option value="Gift Drop-off" {{ request('source') === 'Gift Drop-off' ? 'selected' : '' }}>Gift Drop-off</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400 mb-1">Date To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search donor name or barcode..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">Filter</button>
                        <a href="{{ route('warehouse.transactions') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Time</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Type</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Category</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Item</th>
                                <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400">Qty</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Source</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Donor</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Scanned By</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">Volunteer</th>
                                <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400">IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $txn)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-2 px-4 text-gray-500 dark:text-gray-400 whitespace-nowrap text-xs">
                                        {{ $txn->scanned_at?->format('M j, g:ia') ?? $txn->created_at->format('M j, g:ia') }}
                                    </td>
                                    <td class="py-2 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $txn->transaction_type->color() === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' }}
                                            {{ $txn->transaction_type->color() === 'red' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                            {{ $txn->transaction_type->color() === 'blue' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' }}
                                            {{ $txn->transaction_type->color() === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                        ">{{ $txn->transaction_type->label() }}</span>
                                    </td>
                                    <td class="py-2 px-4 text-gray-900 dark:text-gray-100">{{ $txn->category->name }}</td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $txn->item?->name ?? '—' }}</td>
                                    <td class="text-right py-2 px-4 text-gray-900 dark:text-gray-100 font-medium">{{ $txn->quantity }}</td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $txn->source ?? '—' }}</td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $txn->donor_name ?? '—' }}</td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $txn->scanner?->first_name ?? '—' }}</td>
                                    <td class="py-2 px-4 text-gray-600 dark:text-gray-400">{{ $txn->volunteer_name ?? '—' }}</td>
                                    <td class="py-2 px-4 text-gray-500 dark:text-gray-500 text-xs font-mono">{{ $txn->ip_address ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-gray-500 dark:text-gray-400">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
