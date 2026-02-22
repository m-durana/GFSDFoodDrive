<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Family Number Assignment
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('santa.schoolRanges') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                    School Ranges
                </a>
                @if(count($grouped) > 0 || count($noSchool) > 0)
                    <form method="POST" action="{{ route('santa.autoAssign') }}" onsubmit="return confirm('Auto-assign numbers to all unassigned families?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                            Auto-Assign All
                        </button>
                    </form>
                @endif
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

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $assignedCount }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Assigned</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 shadow-sm sm:rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{{ collect($grouped)->flatten()->count() + count($noSchool) }}</div>
                    <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Unassigned</div>
                </div>
                @foreach($schoolRanges->take(6) as $range)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-center">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $range->school_name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $range->range_start }}–{{ $range->range_end }}</div>
                        <div class="text-xs mt-1 {{ ($rangeInfo[$range->school_name]['next'] ?? null) ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            Next: {{ $rangeInfo[$range->school_name]['next'] ?? 'FULL' }}
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Grouped by School -->
            @foreach($grouped as $school => $families)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $school }}</h3>
                        @php
                            $matchedRange = $schoolRanges->first(function ($r) use ($school) {
                                return stripos($school, $r->school_name) !== false || stripos($r->school_name, $school) !== false;
                            });
                        @endphp
                        @if($matchedRange)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                                Range: {{ $matchedRange->range_start }}–{{ $matchedRange->range_end }} |
                                Next available: <span class="font-medium">{{ $rangeInfo[$matchedRange->school_name]['next'] ?? 'FULL' }}</span>
                            </p>
                        @else
                            <p class="text-xs text-yellow-600 dark:text-yellow-400 mb-4">No matching school range configured</p>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Children</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Oldest Child</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assign Number</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($families as $family)
                                        @php
                                            $oldest = $family->children->sortByDesc(fn($c) => (int) $c->age)->first();
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 text-sm">
                                                <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $family->family_name }}</a>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $family->children->count() }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                @if($oldest)
                                                    {{ $oldest->gender }}, age {{ $oldest->age }} — {{ $oldest->school ?? 'No school' }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                <form method="POST" action="{{ route('santa.updateFamilyNumber') }}" class="flex items-center space-x-2">
                                                    @csrf
                                                    <input type="hidden" name="family_id" value="{{ $family->id }}">
                                                    <input type="number" name="family_number"
                                                        value="{{ $matchedRange ? ($rangeInfo[$matchedRange->school_name]['next'] ?? '') : '' }}"
                                                        min="1" required
                                                        class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                                                        Assign
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- No School -->
            @if(count($noSchool) > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-yellow-700 dark:text-yellow-400 mb-1">No School / No Children</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">These families have no children or children without a school set. Assign manually or update the family first.</p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family Name</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Children</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assign Number</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($noSchool as $family)
                                        <tr>
                                            <td class="px-3 py-2 text-sm">
                                                <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $family->family_name }}</a>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $family->children->count() }}</td>
                                            <td class="px-3 py-2">
                                                <form method="POST" action="{{ route('santa.updateFamilyNumber') }}" class="flex items-center space-x-2">
                                                    @csrf
                                                    <input type="hidden" name="family_id" value="{{ $family->id }}">
                                                    <input type="number" name="family_number" min="1" required placeholder="#"
                                                        class="w-24 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                                                        Assign
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            @if(count($grouped) === 0 && count($noSchool) === 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">All families have been assigned numbers.</p>
                </div>
            @endif

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
