<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adopt a Tag - Closed - GFSD Food Drive</title>
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
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full text-center">
            <div class="text-6xl mb-6">🎄</div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">Adopt-a-Tag Has Closed</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                The adoption deadline was <strong class="text-red-600 dark:text-red-400">{{ $deadline->format('F j, Y') }}</strong>.
                Thank you to everyone who adopted a tag this year!
            </p>

            @if($deliveryDates)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-6">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        Delivery dates: <strong>{{ $deliveryDates }}</strong>
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                        If you already adopted a tag, please make sure your gifts are dropped off before delivery day.
                    </p>
                </div>
            @endif

            <div class="space-y-3">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-lg hover:bg-red-600 font-medium transition">
                    Back to Homepage
                </a>
            </div>

            <p class="mt-8 text-xs text-gray-400 dark:text-gray-600">
                GFSD Food Drive &copy; {{ date('Y') }} &middot; Made with love in 🇨🇭
            </p>
        </div>
    </div>
</body>
</html>
