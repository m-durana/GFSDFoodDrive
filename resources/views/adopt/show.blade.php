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
                    <x-gender-icon :gender="$child->gender" size="lg" />
                    <div>
                        <h2 class="text-xl font-bold">{{ strtolower($child->gender ?? '') === 'other' ? 'Child' : ($child->gender ?? 'Child') }}, Age {{ $child->age ?? '?' }}</h2>
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
                            <label for="adopter_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email *</label>
                            <input type="email" id="adopter_email" name="adopter_email" value="{{ old('adopter_email') }}" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                placeholder="jane@example.com">
                            @error('adopter_email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(\App\Models\Setting::get('notifications_enabled', '0') === '1' && \App\Models\Setting::get('twilio_sid'))
                            <div>
                                <label for="adopter_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone <span class="text-gray-400 font-normal">(optional, for SMS updates)</span></label>
                                <input type="tel" id="adopter_phone" name="adopter_phone" value="{{ old('adopter_phone') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="(360) 555-1234">
                                @error('adopter_phone')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @error('contact')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <button type="submit" class="w-full px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 font-medium transition text-center">
                            Adopt This Tag
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-400 mt-12 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <span class="font-bold text-white">GFSD Food &amp; Gift Drive</span>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
                        <a href="{{ route('adopt.index') }}" class="hover:text-white transition">All Tags</a>
                        <a href="mailto:{{ \App\Models\Setting::get('primary_contact_email', 'fooddrive@gfalls.wednet.edu') }}" class="hover:text-white transition">Contact</a>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-800 text-center text-xs text-gray-500">
                    &copy; {{ date('Y') }} Granite Falls School District Food &amp; Gift Drive.
                    <span class="mx-1">&middot;</span>
                    <span>Made in &#x1F1E8;&#x1F1ED;</span>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
