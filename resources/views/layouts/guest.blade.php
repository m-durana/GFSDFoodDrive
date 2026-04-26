<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="" data-theme="northpole">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GFSD Food Drive') }}</title>

    @include('partials.favicon')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Dark mode: apply before page renders -->
    <script>
        (function() {
            const html = document.documentElement;
            const wantDark = localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (wantDark) {
                html.classList.add('dark');
                const t = html.getAttribute('data-theme') || 'northpole';
                if (!t.endsWith('-dark')) html.setAttribute('data-theme', t + '-dark');
            }
        })();
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            const t = html.getAttribute('data-theme') || 'northpole';
            const base = t.endsWith('-dark') ? t.slice(0, -5) : t;
            html.setAttribute('data-theme', isDark ? base + '-dark' : base);
            localStorage.setItem('darkMode', isDark);
        }
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-base-content antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-base-200">
        <div>
            <h1 class="text-3xl font-bold text-primary dark:text-primary">GFSD Food Drive</h1>
            <p class="text-center text-base-content/60 mt-1">North Pole Portal</p>
        </div>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-base-100 shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>

        <p class="mt-3 text-xs text-base-content/50">{!! \App\Models\Setting::get('footer_text', 'Made in 🇨🇭') !!}</p>

        <!-- Dark mode toggle on login page -->
        <button onclick="toggleDarkMode()" class="mt-2 p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition" title="Toggle dark mode">
            <svg class="hidden dark:block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
            <svg class="block dark:hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
        </button>
    </div>
</body>
</html>
