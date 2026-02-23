<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Volunteer Assignments
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Assign families -->
            @if($unassignedFamilies->count() > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Unassigned Families ({{ $unassignedFamilies->count() }})
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Assign To</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($unassignedFamilies as $family)
                                    <tr>
                                        <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $family->family_number }}</td>
                                        <td class="px-3 py-2 text-sm">
                                            <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $family->family_name }}</a>
                                        </td>
                                        <td class="px-3 py-2">
                                            <form method="POST" action="{{ route('santa.assignVolunteer') }}" class="flex items-center space-x-2">
                                                @csrf
                                                <input type="hidden" name="family_id" value="{{ $family->id }}">
                                                <select name="volunteer_id" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                    <option value="">Select volunteer...</option>
                                                    @foreach($volunteers as $vol)
                                                        <option value="{{ $vol->id }}">{{ $vol->first_name }} {{ $vol->last_name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
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
            @endif

            <!-- Per-volunteer sections -->
            @foreach($volunteers as $volunteer)
                @php $volFamilies = $assignments[$volunteer->id] ?? collect(); @endphp
                @if($volFamilies->count() > 0)
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ $volunteer->first_name }} {{ $volunteer->last_name }}
                                <span class="text-sm font-normal text-gray-500 dark:text-gray-400">({{ $volFamilies->count() }} {{ Str::plural('family', $volFamilies->count()) }})</span>
                            </h3>
                            <a href="{{ route('santa.volunteerList', $volunteer) }}" target="_blank"
                               class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-xs font-medium transition">
                                Print List
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">#</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Family</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Children</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Delivery</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($volFamilies as $family)
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $family->family_number }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <a href="{{ route('family.show', $family) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $family->family_name }}</a>
                                            </td>
                                            <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->children->count() }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $family->delivery_preference ?? '-' }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                <form method="POST" action="{{ route('santa.unassignVolunteer', $family) }}" class="inline" onsubmit="return confirm('Unassign this family?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Unassign</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endforeach

            @if(collect($assignments)->every(fn($a) => $a->count() === 0) && $unassignedFamilies->count() === 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center text-gray-500 dark:text-gray-400">
                    No families with assigned numbers yet. Assign family numbers first.
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
