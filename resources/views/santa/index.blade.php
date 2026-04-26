<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Santa Dashboard
            <x-hint key="santa-dashboard" text="This is your command center. Manage families, assign numbers, generate gift tags, set up delivery routes, and configure settings from here." />
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- Families & People -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-base-content/60 mb-3 px-1">Families & People</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <a href="{{ route('santa.duplicates') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Duplicate Detection</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Find and merge duplicate families</p>
                    </a>
                </div>
            </div>

            <!-- Gifts & Shopping -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-base-content/60 mb-3 px-1">Gifts & Shopping</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="{{ route('santa.gifts') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Gift Tracking</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Track gift levels and adopters</p>
                    </a>
                    <a href="{{ route('santa.adoptions') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Adopt-a-Tag</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Public tag adoption portal & tracking</p>
                    </a>
                    <a href="{{ route('santa.shopping') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Shopping Hub</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Deficits, assignments, formulas & lists</p>
                    </a>
                </div>
            </div>


            <!-- Data & Reports -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-base-content/60 mb-3 px-1">Data & Reports</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <a href="{{ route('santa.analytics') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Analytics</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Charts, trends, and deep insights</p>
                    </a>
                    <a href="{{ route('santa.reports') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Reports</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Stats, progress, and summaries</p>
                    </a>
                    <a href="{{ route('santa.export') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Filter & Export</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Smart filters and CSV export</p>
                    </a>
                    <a href="{{ route('santa.seasons.index') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Season History</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Archive seasons, import data, view trends</p>
                    </a>
                </div>
            </div>

            <!-- Admin -->
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-base-content/60 mb-3 px-1">Admin</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @php $pendingRequests = \App\Models\AccessRequest::where('status', 'pending')->count(); @endphp
                    <a href="{{ route('santa.users') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300 relative">
                        <h4 class="font-medium text-base-content">Manage Users</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Add and edit system users</p>
                        @if($pendingRequests > 0)
                            <x-badge type="warning" class="absolute top-2 right-2">{{ $pendingRequests }}</x-badge>
                        @endif
                    </a>
                    <a href="{{ route('santa.schoolRanges') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">School Ranges</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Configure school number ranges</p>
                    </a>
                    <a href="{{ route('santa.settings') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Settings</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Registration, paper size, OAuth, geocoding</p>
                    </a>
                    <a href="{{ route('santa.backups') }}" class="block p-4 bg-base-100 rounded-box shadow-xs hover:bg-base-200 transition border border-base-300">
                        <h4 class="font-medium text-base-content">Backups</h4>
                        <p class="text-xs text-base-content/60 mt-0.5">Database backups every 4 hours</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
