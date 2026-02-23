<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Shopping Day — Coordinator Assignments
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Current Assignments -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Current Assignments</h3>

                @if($assignments->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($assignments as $assignment)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $assignment->user->first_name }} {{ $assignment->user->last_name }}
                                    </h4>
                                    <form method="POST" action="{{ route('santa.deleteAssignment', $assignment) }}" onsubmit="return confirm('Remove this assignment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Remove</button>
                                    </form>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $assignment->split_type === 'category' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300' }}">
                                        {{ ucfirst(str_replace('_', ' ', $assignment->split_type)) }}
                                    </span>
                                </p>
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $assignment->getDescription() }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $assignment->getTotalItems() }} total items</p>
                                @if($assignment->notes)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic">{{ $assignment->notes }}</p>
                                @endif
                                <div class="mt-3 flex space-x-2">
                                    <a href="{{ route('shopping.assignment', $assignment) }}" target="_blank"
                                       class="inline-flex items-center px-2 py-1 bg-red-700 text-white rounded text-xs hover:bg-red-600 transition">
                                        Mobile Link
                                    </a>
                                    <button type="button" onclick="copyLink('{{ route('shopping.assignment', $assignment) }}')"
                                            class="inline-flex items-center px-2 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded text-xs hover:bg-gray-300 dark:hover:bg-gray-500 transition">
                                        Copy URL
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">No assignments yet. Create one below.</p>
                @endif
            </div>

            <!-- Coverage Indicator -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Coverage</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categories</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allCategories as $cat)
                                @if(in_array($cat, $assignedCategories))
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">{{ ucfirst($cat) }} &#10003;</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">{{ ucfirst($cat) }} — unassigned</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Family Ranges</h4>
                        @if(count($assignedRanges) > 0)
                            <div class="space-y-1">
                                @foreach($assignedRanges as $range)
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        #{{ $range['start'] }}–#{{ $range['end'] }} &#10003;
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-gray-500 dark:text-gray-400">No family range assignments yet.</p>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Highest family number: {{ $maxFamilyNumber }}</p>
                    </div>
                </div>
            </div>

            <!-- Add Assignment -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Add Assignment</h3>
                <form method="POST" action="{{ route('santa.createAssignment') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Coordinator</label>
                            <select name="user_id" id="user_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                                @foreach($coordinators as $coord)
                                    <option value="{{ $coord->id }}">{{ $coord->first_name }} {{ $coord->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Split Type</label>
                            <div class="flex space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="split_type" value="category" checked
                                        class="text-red-600 focus:ring-red-500" onchange="toggleSplitType()">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">By Category</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="split_type" value="family_range"
                                        class="text-red-600 focus:ring-red-500" onchange="toggleSplitType()">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">By Family Range</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Category selection -->
                    <div id="category-section">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categories</label>
                        <div class="flex flex-wrap gap-3">
                            @foreach($allCategories as $cat)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="categories[]" value="{{ $cat }}"
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($cat) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Family range -->
                    <div id="range-section" class="hidden">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="family_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Family #</label>
                                <input type="number" name="family_start" id="family_start" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                            <div>
                                <label for="family_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300">To Family #</label>
                                <input type="number" name="family_end" id="family_end" min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (optional)</label>
                        <input type="text" name="notes" id="notes" placeholder="e.g. Meet at checkout lane 5"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm">
                    </div>

                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Create Assignment
                    </button>
                </form>
            </div>

            <div>
                <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function toggleSplitType() {
            const isCategory = document.querySelector('input[name="split_type"]:checked').value === 'category';
            document.getElementById('category-section').classList.toggle('hidden', !isCategory);
            document.getElementById('range-section').classList.toggle('hidden', isCategory);
        }

        function copyLink(url) {
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }
    </script>
</x-app-layout>
