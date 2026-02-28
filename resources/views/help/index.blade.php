<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Help &amp; Documentation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <p class="text-gray-600 dark:text-gray-400">
                    Welcome to the help center. Select a topic below to learn how each feature works.
                </p>
                <button onclick="restartTour()" class="shrink-0 ml-4 inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-xs font-medium transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" /></svg>
                    Take a Tour
                </button>
            </div>

            @php $topics = \App\Http\Controllers\HelpController::topics(); @endphp

            @php
                $iconMap = [
                    'rocket' => '🚀', 'users' => '👨‍👩‍👧‍👦', 'tag' => '🏷️', 'truck' => '🚚',
                    'cart' => '🛒', 'monitor' => '📺', 'cog' => '⚙️', 'database' => '🗄️', 'archive' => '📦',
                ];
                $descMap = [
                    'getting-started' => 'Log in, navigate the dashboard, and understand your role.',
                    'family-management' => 'Add families, assign numbers, manage children and self-registration.',
                    'gift-tags' => 'Print gift tags, set up Adopt-a-Tag, and manage tag distribution.',
                    'delivery-day' => 'Dispatch board, live map, driver views, and location sharing.',
                    'shopping' => 'Grocery formulas, shopping assignments, and NINJA progress tracking.',
                    'command-center' => 'Full-screen dashboard for TVs — overview, shopping, and delivery modes.',
                    'settings' => 'Configure registration, notifications, branding, and integrations.',
                    'legacy-import' => 'Import historical data from Access databases (_be vs _fe files).',
                    'warehouse' => 'Track donations, scan barcodes, manage inventory and gift drop-offs.',
                ];
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($topics as $topic)
                    <a href="{{ route('help.show', $topic['slug']) }}"
                       class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition group">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl">{{ $iconMap[$topic['icon']] ?? '📄' }}</span>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">
                                {{ $topic['title'] }}
                            </h3>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $descMap[$topic['slug']] ?? '' }}</p>
                        @if($topic['role'] !== 'all')
                            <span class="inline-flex mt-2 px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                {{ ucfirst($topic['role']) }}+
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
