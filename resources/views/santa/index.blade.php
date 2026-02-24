<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Santa Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Families & People -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Families & People</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="{{ route('family.index') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">All Families</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">View and manage registered families</p>
                    </a>
                    <a href="{{ route('santa.numberAssignment') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Number Assignment</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Assign family numbers by school</p>
                    </a>
                    <a href="{{ route('santa.volunteers') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Volunteer Assignment</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Assign families to volunteers</p>
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

            <!-- Delivery -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Delivery</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a href="{{ route('delivery.index') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Delivery Day</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Manage delivery logistics & status</p>
                    </a>
                    <a href="{{ route('delivery.map') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Live Map</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Real-time driver & family map</p>
                    </a>
                    <a href="{{ route('coordinator.index') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Print Documents</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Gift tags, family summaries, delivery sheets</p>
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
                </div>
            </div>

            <!-- Admin -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 px-1">Admin</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a href="{{ route('santa.users') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Manage Users</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add and edit system users</p>
                    </a>
                    <a href="{{ route('santa.schoolRanges') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">School Ranges</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Configure school number ranges</p>
                    </a>
                    <a href="{{ route('santa.settings') }}" class="block p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition border border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100">Settings</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Registration, paper size, OAuth, geocoding</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
