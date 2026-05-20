<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="" data-theme="gfsd">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'GFSD Food Drive') }}</title>

    @include('partials.favicon')
    @include('partials.pwa')

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
                const t = html.getAttribute('data-theme') || 'gfsd';
                if (!t.endsWith('-dark')) html.setAttribute('data-theme', t + '-dark');
            }
        })();
        function toggleDarkMode() {
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            const t = html.getAttribute('data-theme') || 'gfsd';
            const base = t.endsWith('-dark') ? t.slice(0, -5) : t;
            html.setAttribute('data-theme', isDark ? base + '-dark' : base);
            localStorage.setItem('darkMode', isDark);
        }
    </script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-base-content antialiased bg-base-100 min-h-screen">
    {{ $slot }}
</body>
</html>
