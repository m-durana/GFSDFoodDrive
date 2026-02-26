<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Adopt-a-Tag Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['available'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Available</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-yellow-200 dark:border-yellow-700 p-4 text-center">
                    <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['adopted'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Adopted (pending)</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-green-200 dark:border-green-700 p-4 text-center">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['dropped_off'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dropped Off</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-red-200 dark:border-red-700 p-4 text-center">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['overdue'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Overdue</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <form method="GET" action="{{ route('santa.adoptions') }}" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status" onchange="this.form.submit()"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm">
                            <option value="adopted" {{ $status === 'adopted' ? 'selected' : '' }}>Adopted (pending drop-off)</option>
                            <option value="dropped_off" {{ $status === 'dropped_off' ? 'selected' : '' }}>Dropped Off</option>
                            <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family #</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Child</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopter</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contact</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adopted</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Deadline</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($children as $child)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-900 dark:text-gray-100">
                                    <td class="px-4 py-3 text-sm font-mono">
                                        @if($child->family)
                                            {{ $child->family->family_number }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $child->gender }}, {{ $child->age }}
                                        @if($child->school)
                                            <span class="text-xs text-gray-500 dark:text-gray-400">({{ $child->school }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $child->adopter_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($child->adopter_email)
                                            <span class="block">{{ $child->adopter_email }}</span>
                                        @endif
                                        @if($child->adopter_phone)
                                            <span class="block text-gray-500 dark:text-gray-400">{{ $child->adopter_phone }}</span>
                                        @endif
                                        @if(!$child->adopter_email && !$child->adopter_phone)
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $child->adopted_at ? $child->adopted_at->format('M j') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($child->adoption_deadline)
                                            <span class="{{ $child->adoption_deadline->isPast() && !$child->gift_dropped_off ? 'text-red-600 dark:text-red-400 font-semibold' : '' }}">
                                                {{ $child->adoption_deadline->format('M j') }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($child->gift_dropped_off)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                                Dropped off
                                            </span>
                                        @elseif($child->isAdopted() && $child->adoption_deadline?->isPast())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                                Overdue
                                            </span>
                                        @elseif($child->isAdopted())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300">
                                                Adopted
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                                Available
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if($child->isAdopted())
                                            <div class="flex justify-end space-x-2">
                                                <form method="POST" action="{{ route('santa.releaseAdoption', $child) }}" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                                            onclick="return confirm('Release this tag back to the pool? The adopter will lose their claim.')">
                                                        Release
                                                    </button>
                                                </form>
                                                @if($child->gift_level?->value < 3)
                                                    <form method="POST" action="{{ route('santa.completeAdoption', $child) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="text-xs text-green-600 dark:text-green-400 hover:underline"
                                                                onclick="return confirm('Mark as complete? Gift level will be set to Full.')">
                                                            Complete
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No children match this filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $children->count() }} results</p>
            </div>
        </div>
    </div>
</x-app-layout>
