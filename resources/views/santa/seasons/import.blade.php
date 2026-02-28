<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Import Historical Data
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            @if(session('import_errors') && count(session('import_errors')) > 0)
                <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 text-yellow-700 dark:text-yellow-300 px-4 py-3 rounded">
                    <h4 class="font-medium mb-2">Import Errors:</h4>
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach(session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Legacy Databases Section --}}
            @if(!empty($legacyFiles))
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Legacy Databases</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        These Access databases were found in the project. Click "Import" to load families and children from a database.
                        @if(!$accessDriver)
                            <span class="text-amber-600 dark:text-amber-400 font-medium">Note: No Access driver detected &mdash; install ODBC driver (Windows) or mdbtools (Linux) first.</span>
                        @endif
                    </p>

                    {{-- Explanation of file types --}}
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded p-3 mb-4 text-sm text-blue-700 dark:text-blue-300">
                        <strong>File types explained:</strong>
                        <code>*_be</code> = Back-End database (contains the actual data &mdash; import this one).
                        <code>*_fe Admin</code> = Front-End (Access forms/UI only, no data).
                        <code>*Survey*</code> = Survey entry form (may contain registration data).
                    </div>

                    @php
                        $importableYears = collect($legacyFiles)->filter(function ($files, $year) use ($existingSeasons) {
                            return !in_array($year, $existingSeasons) && collect($files)->contains('is_main', true);
                        });
                    @endphp
                    @if($importableYears->count() > 1)
                        <div class="flex justify-end mb-3">
                            <form method="POST" action="{{ route('santa.seasons.importAllLegacy') }}" onsubmit="this.querySelector('button').textContent='Importing all...'; this.querySelector('button').disabled=true;">
                                @csrf
                                <button type="submit" @if(!$accessDriver) disabled @endif
                                    class="inline-flex items-center px-4 py-2 bg-green-700 text-white rounded-md hover:bg-green-600 text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                    Import All {{ $importableYears->count() }} Remaining Years
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @foreach($legacyFiles as $year => $files)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $year }} Season
                                        @if(in_array($year, $existingSeasons))
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">Already Imported</span>
                                        @endif
                                    </h4>
                                </div>
                                <div class="space-y-2">
                                    @foreach($files as $file)
                                        <div class="flex items-center justify-between py-2 px-3 rounded bg-gray-50 dark:bg-gray-700/50">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-medium {{ $file['ext'] === 'accdb' || $file['ext'] === 'mdb' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                                                    .{{ $file['ext'] }}
                                                </span>
                                                <span class="text-sm text-gray-900 dark:text-gray-100 truncate" title="{{ $file['name'] }}">
                                                    {{ $file['name'] }}
                                                    @if($file['is_main'])
                                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-medium ml-1">Main DB</span>
                                                    @endif
                                                </span>
                                                <span class="text-xs text-gray-400 flex-shrink-0">{{ number_format($file['size'] / 1024) }} KB</span>
                                            </div>
                                            <form method="POST" action="{{ route('santa.seasons.importLegacy') }}" class="flex-shrink-0 ml-3">
                                                @csrf
                                                <input type="hidden" name="legacy_path" value="{{ $file['path'] }}">
                                                <input type="hidden" name="season_year" value="{{ $year }}">
                                                <button type="submit"
                                                    @if(!$accessDriver && in_array($file['ext'], ['accdb', 'mdb'])) disabled @endif
                                                    class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                                                    Import
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Manual Upload Section --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Upload File</h3>

                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Import Family or Child data from <strong>Excel (.xlsx)</strong> or <strong>Access (.accdb/.mdb)</strong> files.
                    Column headers are matched automatically &mdash; PascalCase, snake_case, and spaced names all work.
                    Import families first, then children (children are linked by family number).
                </p>

                @if($accessDriver)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3 mb-4">
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Access database support: <strong>{{ $accessDriver === 'odbc' ? 'ODBC driver' : 'mdbtools' }}</strong> detected.
                        </p>
                    </div>
                @else
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3 mb-4">
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            No Access database driver detected. To import .accdb files, either install the ODBC driver (Windows)
                            or mdbtools (Linux), or export your Access tables to .xlsx first.
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('santa.seasons.previewImport') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="season_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Season Year</label>
                        <input type="number" name="season_year" id="season_year" min="2000" max="2099"
                            value="{{ old('season_year', $currentYear) }}"
                            class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        @error('season_year')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Type <span class="text-xs text-gray-400">(Excel only &mdash; Access files auto-detect)</span></label>
                        <select name="type" id="type"
                            class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                            <option value="family">Family Table</option>
                            <option value="child">Child Table</option>
                        </select>
                        @error('type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300">File (.xlsx, .accdb, .mdb)</label>
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.accdb,.mdb"
                            class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-red-50 file:text-red-700 hover:file:bg-red-100 dark:file:bg-gray-600 dark:file:text-gray-200">
                        @error('file')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Preview Import
                    </button>
                </form>
            </div>

            @if(count($existingSeasons) > 0)
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Existing Seasons</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($existingSeasons as $year)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $year }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <a href="{{ route('santa.seasons.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Season History
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
