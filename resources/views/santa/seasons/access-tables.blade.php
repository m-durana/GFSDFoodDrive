<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Access Database &mdash; Select Table ({{ $seasonYear }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
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

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tables Found</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Select a table to import. Import <strong>Family Table first</strong>, then Child Table (children link to families by family number).
                </p>

                <div class="space-y-3">
                    @foreach($tables as $table)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ $table }}</h4>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" action="{{ route('santa.seasons.previewAccessTable') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="path" value="{{ $path }}">
                                        <input type="hidden" name="table" value="{{ $table }}">
                                        <input type="hidden" name="season_year" value="{{ $seasonYear }}">
                                        <input type="hidden" name="type" value="family">
                                        <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-500 transition">
                                            Import as Families
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('santa.seasons.previewAccessTable') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="path" value="{{ $path }}">
                                        <input type="hidden" name="table" value="{{ $table }}">
                                        <input type="hidden" name="season_year" value="{{ $seasonYear }}">
                                        <input type="hidden" name="type" value="child">
                                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-500 transition">
                                            Import as Children
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <a href="{{ route('santa.seasons.import') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Import
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
