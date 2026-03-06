<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Family Entry Dashboard
            <x-hint key="family-dashboard" text="Add families and their children here. Mark a family 'Done' when all info is complete. Santa users can see all families across coordinators." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Welcome, {{ auth()->user()->first_name }}!</h3>

                    @if($families->count() > 0)
                        <div x-data="{ ...sortTable(), search: '' }">
                            <div class="flex items-center justify-between mb-6 gap-4">
                                <a href="{{ route('family.create') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 transition whitespace-nowrap">
                                    Add New Family
                                </a>
                                <input type="text" x-model="search" placeholder="Search by number, name, or phone..."
                                    class="w-full sm:w-80 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm focus:border-red-500 focus:ring-red-500">
                            </div>

                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-2">My Families</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <x-sort-th key="family_number">Family #</x-sort-th>
                                            <x-sort-th key="family_name">Family Name</x-sort-th>
                                            <x-sort-th key="address">Address</x-sort-th>
                                            <x-sort-th key="phone">Phone</x-sort-th>
                                            @if((auth()->user()->isCoordinator() || auth()->user()->isSanta()) && \App\Models\Setting::get('packing_system_enabled', '1') === '1')
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Packing</th>
                                            @endif
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach($families as $family)
                                            <tr x-show="!search || '{{ $family->family_number }}'.includes(search) || '{{ strtolower(addslashes($family->family_name)) }}'.includes(search.toLowerCase()) || '{{ $family->phone1 }}'.includes(search)">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm" data-sort-value="{{ $family->family_number ?? 0 }}">{{ $family->family_number ?? '-' }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $family->family_name }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->address }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->phone1 }}</td>
                                                @if((auth()->user()->isCoordinator() || auth()->user()->isSanta()) && \App\Models\Setting::get('packing_system_enabled', '1') === '1')
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                        @if($family->packingList)
                                                            @php
                                                                $packingProgress = $family->packingList->progressSummary();
                                                                $packingStatusColors = [
                                                                    'pending' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
                                                                    'in_progress' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
                                                                    'complete' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
                                                                    'verified' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                                ];
                                                            @endphp
                                                            <a href="{{ route('packing.show', $family->packingList) }}" class="inline-flex items-center gap-1">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $packingStatusColors[$family->packingList->status->value] ?? $packingStatusColors['pending'] }}">
                                                                    {{ $family->packingList->status->label() }}
                                                                </span>
                                                                <span class="text-xs text-gray-400">{{ $packingProgress['packed'] }}/{{ $packingProgress['total'] }}</span>
                                                            </a>
                                                        @else
                                                            <span class="text-xs text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                @endif
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <a href="{{ route('family.show', $family) }}" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="flex space-x-4 mb-6">
                            <a href="{{ route('family.create') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 transition">
                                Add New Family
                            </a>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400">No families added yet. Click "Add New Family" to get started.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
