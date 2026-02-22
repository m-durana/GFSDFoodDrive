<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            School Ranges Configuration
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Add New Range -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add School Range</h3>
                    <form method="POST" action="{{ route('santa.storeSchoolRange') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400">School Name</label>
                            <input type="text" name="school_name" required placeholder="e.g. Mountain Way"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Range Start</label>
                            <input type="number" name="range_start" required min="0" placeholder="1"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Range End</label>
                            <input type="number" name="range_end" required min="0" placeholder="99"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 dark:text-gray-400">Sort Order</label>
                            <input type="number" name="sort_order" value="0" min="0"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                        </div>
                        <div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                                Add Range
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Existing Ranges -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Ranges</h3>

                    @if($ranges->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">School</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">End</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Capacity</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Order</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($ranges as $range)
                                        <tr>
                                            <td colspan="6" class="px-0 py-0">
                                                <form method="POST" action="{{ route('santa.updateSchoolRange', $range) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <table class="w-full">
                                                        <tr>
                                                            <td class="px-3 py-2">
                                                                <input type="text" name="school_name" value="{{ $range->school_name }}" required
                                                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="number" name="range_start" value="{{ $range->range_start }}" required min="0"
                                                                    class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="number" name="range_end" value="{{ $range->range_end }}" required min="0"
                                                                    class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            </td>
                                                            <td class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                                                {{ $range->range_end - $range->range_start + 1 }}
                                                            </td>
                                                            <td class="px-3 py-2">
                                                                <input type="number" name="sort_order" value="{{ $range->sort_order }}" min="0"
                                                                    class="w-16 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                                            </td>
                                                            <td class="px-3 py-2 whitespace-nowrap">
                                                                <button type="submit" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Save</button>
                                                </form>
                                                                <form method="POST" action="{{ route('santa.destroySchoolRange', $range) }}" class="inline" onsubmit="return confirm('Remove this school range?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs ml-2">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">No school ranges configured. Add one above.</p>
                    @endif
                </div>
            </div>

            <div>
                <a href="{{ route('santa.numberAssignment') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Number Assignment
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
