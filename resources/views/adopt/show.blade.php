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
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <a href="{{ route('adopt.index') }}" class="text-red-200 hover:text-white text-sm transition">&larr; Back to all tags</a>
                <h1 class="text-2xl font-bold mt-2">Adopt a Tag</h1>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Child Info Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-center space-x-4 mb-6">
                    @if(strtolower($child->gender ?? '') === 'female')
                        <div class="w-14 h-14 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                            <svg class="w-7 h-7 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 00-2 13.72V18H8v2h2v2h4v-2h2v-2h-2v-2.28A7 7 0 0012 2zm0 12a5 5 0 110-10 5 5 0 010 10z"/></svg>
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-7 h-7 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M15.5 1h5v5h-2V3.41L14.06 7.86A7 7 0 1012.64 7l4.45-4.45H15.5V1zM12 19a5 5 0 100-10 5 5 0 000 10z"/></svg>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold">{{ $child->gender ?? 'Child' }}, Age {{ $child->age ?? '?' }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Family #{{ $child->family->family_number }}
                            @if($child->school) &middot; {{ $child->school }} @endif
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($child->clothes_size)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Clothing Size</h4>
                            <p class="text-sm">{{ $child->clothes_size }}</p>
                        </div>
                    @endif

                    @if($child->clothing_styles)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Clothing Styles</h4>
                            <p class="text-sm">{{ $child->clothing_styles }}</p>
                        </div>
                    @endif

                    @if($child->clothing_options)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Clothing Preferences</h4>
                            <p class="text-sm">{{ $child->clothing_options }}</p>
                        </div>
                    @endif

                    @if($child->all_sizes)
                        <div>
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">All Sizes</h4>
                            <p class="text-sm">{{ $child->all_sizes }}</p>
                        </div>
                    @endif

                    @if($child->toy_ideas)
                        <div class="sm:col-span-2">
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Toy Ideas / Interests</h4>
                            <p class="text-sm">{{ $child->toy_ideas }}</p>
                        </div>
                    @endif

                    @if($child->gift_preferences)
                        <div class="sm:col-span-2">
                            <h4 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-1">Gift Preferences</h4>
                            <p class="text-sm">{{ $child->gift_preferences }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Claim Form -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold mb-4">Adopt This Tag</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    By adopting this tag, you agree to purchase a gift for this child and drop it off before the deadline.
                </p>

                <form method="POST" action="{{ route('adopt.claim', $child) }}">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="adopter_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Your Name *</label>
                            <input type="text" id="adopter_name" name="adopter_name" value="{{ old('adopter_name') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                placeholder="Jane Smith">
                            @error('adopter_name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="adopter_contact_info" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email or Phone *</label>
                            <input type="text" id="adopter_contact_info" name="adopter_contact_info" value="{{ old('adopter_contact_info') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                placeholder="jane@example.com or (360) 555-1234">
                            @error('adopter_contact_info')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 font-medium transition text-center">
                            Adopt This Tag
                        </button>
                    </div>
                </form>
            </div>
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
