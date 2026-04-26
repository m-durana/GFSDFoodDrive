<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Import Preview &mdash; {{ ucfirst($type) }} Table ({{ $seasonYear }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Column Mapping -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-base-content mb-4">Column Mapping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($preview['mapped'] as $col => $info)
                        <div class="flex items-center space-x-2 text-sm">
                            @if($info['mapped_to'])
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">Matched</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-sm text-xs font-medium bg-base-200 text-base-content/60">Ignored</span>
                            @endif
                            <span class="text-base-content/80">{{ $info['original'] }}</span>
                            @if($info['mapped_to'])
                                <span class="text-gray-400">&rarr;</span>
                                <span class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ $info['mapped_to'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Data Preview -->
            <div class="bg-base-100 shadow-xs sm:rounded-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-base-content mb-4">First {{ count($preview['preview']) }} Rows</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-base-300 text-sm">
                        <thead class="bg-base-200">
                            <tr>
                                @foreach($preview['headers'] as $header)
                                    @if($header)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase whitespace-nowrap">{{ $header }}</th>
                                    @endif
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-base-300">
                            @foreach($preview['preview'] as $row)
                                <tr>
                                    @foreach(array_values($row) as $i => $cell)
                                        @if(isset($preview['headers'][$i]) && $preview['headers'][$i])
                                            <td class="px-4 py-2 text-base-content/80 whitespace-nowrap">{{ \Illuminate\Support\Str::limit($cell, 60) }}</td>
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
                <a href="{{ route('santa.seasons.import') }}" class="text-sm text-base-content/70 hover:text-gray-900 dark:hover:text-gray-200 transition">
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
                        <label class="flex items-center gap-2 text-sm text-base-content/70">
                            <input type="checkbox" name="background" value="1" class="rounded-sm border-base-300">
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
