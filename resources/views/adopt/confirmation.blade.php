<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your Adopted Tag - GFSD Food Drive</title>
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
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center">
                <h1 class="text-2xl sm:text-3xl font-bold">Thank you, {{ $child->adopter_name }}!</h1>
                <p class="text-red-200 mt-1">You're making a difference for a child in Granite Falls.</p>
            </div>
        </header>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <!-- What you claimed -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Your Adopted Tag</h3>
                <div class="flex items-center space-x-4 mb-4">
                    @if(strtolower($child->gender ?? '') === 'female')
                        <div class="w-12 h-12 rounded-full bg-pink-100 dark:bg-pink-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a7 7 0 00-2 13.72V18H8v2h2v2h4v-2h2v-2h-2v-2.28A7 7 0 0012 2zm0 12a5 5 0 110-10 5 5 0 010 10z"/></svg>
                        </div>
                    @else
                        <div class="w-12 h-12 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M15.5 1h5v5h-2V3.41L14.06 7.86A7 7 0 1012.64 7l4.45-4.45H15.5V1zM12 19a5 5 0 100-10 5 5 0 000 10z"/></svg>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold">{{ $child->gender ?? 'Child' }}, Age {{ $child->age ?? '?' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Family #{{ $child->family->family_number }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @if($child->clothes_size)
                        <div><span class="text-gray-500 dark:text-gray-400">Size:</span> {{ $child->clothes_size }}</div>
                    @endif
                    @if($child->toy_ideas)
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400">Interests:</span> {{ $child->toy_ideas }}</div>
                    @endif
                    @if($child->gift_preferences)
                        <div class="sm:col-span-2"><span class="text-gray-500 dark:text-gray-400">Gift preferences:</span> {{ $child->gift_preferences }}</div>
                    @endif
                </div>
            </div>

            <!-- Deadline & Drop-off -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold mb-4">Drop-off Information</h3>

                @if($child->adoption_deadline)
                    <div class="flex items-center space-x-2 mb-4">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">
                            Please drop off the gift by <span class="text-red-600 dark:text-red-400">{{ $child->adoption_deadline->format('F j, Y') }}</span>
                        </p>
                    </div>
                @endif

                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    When dropping off the gift, please label it with <strong>Family #{{ $child->family->family_number }}</strong> so we can match it to the right child.
                </p>

                @if($child->gift_dropped_off)
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 flex items-center space-x-3">
                        <svg class="w-8 h-8 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <p class="font-semibold text-green-700 dark:text-green-300">Gift dropped off — Thank you!</p>
                            <p class="text-sm text-green-600 dark:text-green-400">Your generosity will make this child's holiday special.</p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('adopt.markDelivered', $child->adoption_token) }}">
                        @csrf
                        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-500 font-medium transition text-center"
                                onclick="return confirm('Mark this gift as dropped off?')">
                            Mark as Dropped Off
                        </button>
                    </form>
                @endif
            </div>

            <!-- Bookmark reminder -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 text-sm text-yellow-700 dark:text-yellow-300">
                <strong>Bookmark this page!</strong> This is your private link to track your adopted tag. You can come back anytime to check the status or mark the gift as dropped off.
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
