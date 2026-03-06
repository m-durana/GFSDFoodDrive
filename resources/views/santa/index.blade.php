<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Santa Dashboard
            <x-hint key="santa-dashboard" text="This is your command center. Manage families, assign numbers, generate gift tags, set up delivery routes, and configure settings from here." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Families & People -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Families & People</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a href="{{ route('santa.numberAssignment') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Number Assignment</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Assign family numbers by school</p>
                    </a>
                    <a href="{{ route('santa.duplicates') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Duplicate Detection</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Find and merge duplicate families</p>
                    </a>
                </div>
            </div>

            <!-- Gifts & Shopping -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Gifts & Shopping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="{{ route('santa.gifts') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Gift Tracking</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Track gift levels and adopters</p>
                    </a>
                    <a href="{{ route('santa.adoptions') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Adopt-a-Tag</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Public tag adoption portal & tracking</p>
                    </a>
                    <a href="{{ route('santa.shoppingList') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Shopping Lists</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Grocery lists by family size</p>
                    </a>
                    <a href="{{ route('santa.shoppingDay') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Shopping Day</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">NINJA assignments & live checklists</p>
                    </a>
                </div>
            </div>


            <!-- Data & Reports -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Data & Reports</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a href="{{ route('santa.reports') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Reports</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Stats, progress, and analytics</p>
                    </a>
                    <a href="{{ route('santa.export') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Filter & Export</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Smart filters and CSV export</p>
                    </a>
                    <a href="{{ route('santa.seasons.index') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Season History</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Archive seasons, import data, view trends</p>
                    </a>
                </div>
            </div>

            <!-- Admin -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Admin</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @php $pendingRequests = \App\Models\AccessRequest::where('status', 'pending')->count(); @endphp
                    <a href="{{ route('santa.users') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700 relative">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Manage Users</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add and edit system users</p>
                        @if($pendingRequests > 0)
                            <span class="absolute top-2 right-2 inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold leading-none text-white bg-amber-500 rounded-full">{{ $pendingRequests }}</span>
                        @endif
                    </a>
                    <a href="{{ route('santa.schoolRanges') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">School Ranges</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Configure school number ranges</p>
                    </a>
                    <a href="{{ route('santa.settings') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Settings</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Registration, paper size, OAuth, geocoding</p>
                    </a>
                    <a href="{{ route('santa.backups') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Backups</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Database backups every 4 hours</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
