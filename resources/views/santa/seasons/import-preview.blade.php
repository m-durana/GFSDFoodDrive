<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Import Preview &mdash; {{ ucfirst($type) }} Table ({{ $seasonYear }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Column Mapping -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Column Mapping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($preview['mapped'] as $col => $info)
                        <div class="flex items-center space-x-2 text-sm">
                            @if($info['mapped_to'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Matched</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Ignored</span>
                            @endif
                            <span class="text-gray-700 dark:text-gray-300">{{ $info['original'] }}</span>
                            @if($info['mapped_to'])
                                <span class="text-gray-400">&rarr;</span>
                                <span class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ $info['mapped_to'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Data Preview -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">First {{ count($preview['preview']) }} Rows</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach($preview['headers'] as $header)
                                    @if($header)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase whitespace-nowrap">{{ $header }}</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($preview['preview'] as $row)
                                <tr>
                                    @foreach(array_values($row) as $i => $cell)
                                        @if(isset($preview['headers'][$i]) && $preview['headers'][$i])
                                            <td class="px-4 py-2 text-gray-700 dark:text-gray-300 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($cell, 60) }}</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirm -->
            <div class="flex items-center justify-between">
                <a href="{{ route('santa.seasons.import') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Cancel
                </a>
                <form method="POST" action="{{ route('santa.seasons.executeImport') }}">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="season_year" value="{{ $seasonYear }}">
                    @if(!empty($isAccess) && !empty($accessTable))
                        <input type="hidden" name="access_table" value="{{ $accessTable }}">
                    @endif
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <input type="checkbox" name="background" value="1" class="rounded border-gray-300 dark:border-gray-600">
                            Run in background
                        </label>
                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-700 text-white rounded-md hover:bg-green-600 text-sm font-medium transition"
                                onclick="this.textContent='Importing...'; this.disabled=true; this.form.submit();">
                            Confirm Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
