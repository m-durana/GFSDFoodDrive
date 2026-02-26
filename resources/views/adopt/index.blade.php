<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adopt a Tag - GFSD Food Drive</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-red-700 dark:bg-red-900 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
                <h1 class="text-3xl sm:text-4xl font-bold">GFSD Food Drive</h1>
                <p class="text-red-200 text-lg mt-1">Adopt a Tag</p>
                @if($customMessage)
                    <p class="mt-4 text-red-100 max-w-2xl mx-auto">{{ $customMessage }}</p>
                @endif
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Stats bar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 text-center border border-gray-200 dark:border-gray-700">
                <p class="text-lg font-medium">
                    <span class="text-red-600 dark:text-red-400 font-bold text-2xl">{{ $totalAvailable }}</span>
                    of {{ $totalChildren }} tags still need adopters!
                </p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter bar -->
            <form method="GET" action="{{ route('adopt.index') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6 border border-gray-200 dark:border-gray-700">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Gender</label>
                        <select name="gender" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option value="">All</option>
                            <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Boy</option>
                            <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Girl</option>
                            <option value="Other" {{ request('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Age Min</label>
                        <input type="number" name="age_min" value="{{ request('age_min') }}" min="0" max="18" placeholder="0"
                            class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Age Max</label>
                        <input type="number" name="age_max" value="{{ request('age_max') }}" min="0" max="18" placeholder="18"
                            class="w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">School</label>
                        <select name="school" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school }}" {{ request('school') === $school ? 'selected' : '' }}>{{ $school }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                            Filter
                        </button>
                        @if(request()->hasAny(['gender', 'age_min', 'age_max', 'school']))
                            <a href="{{ route('adopt.index') }}" class="ml-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Clear</a>
                        @endif
                    </div>
                </div>
            </form>

            <!-- Card grid -->
            @if($children->isEmpty())
                <div class="text-center py-16 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 01-1.5 1.5H5.25a1.5 1.5 0 01-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 109.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1114.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                    <p class="text-lg font-medium">No tags available right now</p>
                    <p class="mt-1">Check back later or adjust your filters!</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($children as $child)
                        <a href="{{ route('adopt.show', $child) }}"
                           class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition p-5">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center space-x-3">
                                    <x-gender-icon :gender="$child->gender" size="sm" />
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">
                                            {{ strtolower($child->gender ?? '') === 'other' ? 'Child' : ($child->gender ?? 'Child') }}, Age {{ $child->age ?? '?' }}
                                        </p>
                                        @if($child->school)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $child->school }}</p>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-1 rounded font-mono">
                                    #{{ $child->family->family_number }}
                                </span>
                            </div>

                            @if($child->toy_ideas)
                                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                    {{ Str::limit($child->toy_ideas, 80) }}
                                </p>
                            @endif

                            @if($child->clothes_size || $child->all_sizes)
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Size: {{ $child->clothes_size ?: $child->all_sizes }}
                                </p>
                            @endif

                            <div class="mt-4">
                                <span class="inline-flex items-center px-3 py-1.5 bg-red-700 text-white text-sm font-medium rounded-md">
                                    Adopt This Tag
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer -->
        <footer class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                Organized by Granite Falls School District Food Drive
            </div>
        </footer>
    </div>
</body>
</html>
