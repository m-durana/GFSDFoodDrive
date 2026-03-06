<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-gray-100 dark:bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'GFSD Food Drive') }}</title>

    @include('partials.favicon')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Dark mode: apply before page renders to prevent flash -->
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
            localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
        }
    </script>

    <!-- Prevent FOUC: hide until CSS loads -->
    <style>body { visibility: hidden; }</style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>body { visibility: visible; }</style>
</head>
<body class="font-sans antialiased pb-safe">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

        <x-site-footer />
    </div>
    @include('partials.hints')
    @include('partials.guided-tour')

    <!-- Sortable table Alpine component -->
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('sortTable', () => ({
            sortKey: '',
            sortAsc: true,
            sort(key) {
                if (this.sortKey === key) {
                    this.sortAsc = !this.sortAsc;
                } else {
                    this.sortKey = key;
                    this.sortAsc = true;
                }
                const tbody = this.$el.querySelector('tbody');
                if (!tbody) return;
                const rows = Array.from(tbody.querySelectorAll(':scope > tr'));
                const idx = Array.from(this.$el.querySelectorAll('th[data-sort-key]'))
                    .findIndex(th => th.dataset.sortKey === key);
                if (idx < 0) return;
                rows.sort((a, b) => {
                    // Prefer data-col attribute lookup, fall back to positional index
                    const aCell = Array.from(a.querySelectorAll('td')).find(td => td.dataset.col === key) ?? a.querySelectorAll('td')[idx];
                    const bCell = Array.from(b.querySelectorAll('td')).find(td => td.dataset.col === key) ?? b.querySelectorAll('td')[idx];
                    if (!aCell || !bCell) return 0;
                    let aVal = (aCell.dataset.sortValue ?? aCell.textContent).trim();
                    let bVal = (bCell.dataset.sortValue ?? bCell.textContent).trim();
                    const aNum = parseFloat(aVal), bNum = parseFloat(bVal);
                    if (!isNaN(aNum) && !isNaN(bNum)) return this.sortAsc ? aNum - bNum : bNum - aNum;
                    return this.sortAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                });
                rows.forEach(r => tbody.appendChild(r));
            }
        }));
    });
    </script>
    @stack('scripts')
</body>
</html>
