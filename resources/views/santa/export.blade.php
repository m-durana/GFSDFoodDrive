<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-base-content leading-tight">
                Filter & Export
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('santa.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md hover:bg-green-500 text-xs font-medium transition">
                    Export Families CSV
                </a>
                <a href="{{ route('santa.export', array_merge(request()->query(), ['format' => 'children-csv'])) }}"
                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition">
                    Export Children CSV
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <form method="GET" action="{{ route('santa.export') }}" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">School</label>
                        <select name="school" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school }}" {{ request('school') === $school ? 'selected' : '' }}>{{ $school }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Language</label>
                        <select name="language" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All Languages</option>
                            @foreach($languages as $lang)
                                <option value="{{ $lang }}" {{ request('language') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Delivery Status</label>
                        <select name="delivery_status" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('delivery_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_transit" {{ request('delivery_status') === 'in_transit' ? 'selected' : '' }}>In Transit</option>
                            <option value="delivered" {{ request('delivery_status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Gift Level</label>
                        <select name="gift_level" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All Levels</option>
                            <option value="0" {{ request('gift_level') === '0' ? 'selected' : '' }}>No Gifts</option>
                            <option value="1" {{ request('gift_level') === '1' ? 'selected' : '' }}>Partial</option>
                            <option value="2" {{ request('gift_level') === '2' ? 'selected' : '' }}>Moderate</option>
                            <option value="3" {{ request('gift_level') === '3' ? 'selected' : '' }}>Fully Gifted</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Assigned #</label>
                        <select name="assigned" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All</option>
                            <option value="1" {{ request('assigned') === '1' ? 'selected' : '' }}>Assigned</option>
                            <option value="0" {{ request('assigned') === '0' ? 'selected' : '' }}>Unassigned</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-base-content/60 mb-1">Done?</label>
                        <select name="done" class="w-full rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm">
                            <option value="">All</option>
                            <option value="1" {{ request('done') === '1' ? 'selected' : '' }}>Complete</option>
                            <option value="0" {{ request('done') === '0' ? 'selected' : '' }}>Incomplete</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-3">
                        <label class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="needs_baby" value="1" {{ request('needs_baby') ? 'checked' : '' }} class="rounded-sm border-base-300 dark:bg-gray-700 mr-1.5">
                            Baby Supplies
                        </label>
                    </div>
                    <div class="flex items-center space-x-3">
                        <label class="inline-flex items-center text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" name="severe_need" value="1" {{ request('severe_need') ? 'checked' : '' }} class="rounded-sm border-base-300 dark:bg-gray-700 mr-1.5">
                            Severe Need
                        </label>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                            Filter
                        </button>
                        <a href="{{ route('santa.export') }}" class="inline-flex items-center px-3 py-2 text-base-content/60 hover:text-gray-700 dark:hover:text-gray-300 text-sm">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Results -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm text-base-content/60 mb-4">
                        Showing {{ $families->count() }} {{ Str::plural('family', $families->count()) }}
                        ({{ $families->sum(fn($f) => $f->children->count()) }} children)
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-base-300">
                            <thead class="bg-base-200">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Family</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Phone</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Children</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Language</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Delivery</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Status</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-base-content/60 uppercase">Flags</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-base-300">
                                @forelse($families as $family)
                                    <tr class="{{ $family->family_done ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                                        <td class="px-3 py-2 text-sm font-medium text-base-content">
                                            {{ $family->family_number ?? '-' }}
                                        </td>
                                        <td class="px-3 py-2 text-sm">
                                            <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                {{ $family->family_name }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->phone1 }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->children->count() }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->preferred_language ?? '-' }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->delivery_preference ?? '-' }}</td>
                                        <td class="px-3 py-2 text-sm">
                                            @if($family->delivery_status)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $family->delivery_status->value === 'delivered' ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300' : '' }}
                                                    {{ $family->delivery_status->value === 'in_transit' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : '' }}
                                                    {{ $family->delivery_status->value === 'pending' ? 'bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-300' : '' }}
                                                ">{{ $family->delivery_status->label() }}</span>
                                            @else
                                                <span class="text-gray-400 text-xs">N/A</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-sm space-x-1">
                                            @if($family->family_done)
                                                <span class="text-green-600 dark:text-green-400 text-xs" title="Complete">Done</span>
                                            @endif
                                            @if($family->needs_baby_supplies)
                                                <span class="text-orange-600 dark:text-orange-400 text-xs" title="Needs baby supplies">Baby</span>
                                            @endif
                                            @if($family->severe_need)
                                                <span class="text-primary dark:text-primary text-xs font-bold" title="Severe need">!</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-3 py-8 text-center text-base-content/60">
                                            No families match the selected filters.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-base-content/70 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
