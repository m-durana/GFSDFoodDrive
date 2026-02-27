<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Help &amp; Documentation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <p class="text-gray-600 dark:text-gray-400 mb-8">
                Welcome to the help center. Select a topic below to learn how each feature works.
            </p>

            @php $topics = \App\Http\Controllers\HelpController::topics(); @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($topics as $topic)
                    <a href="{{ route('help.show', $topic['slug']) }}"
                       class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition group">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">
                            {{ $topic['title'] }}
                        </h3>
                        @if($topic['role'] !== 'all')
                            <span class="inline-flex mt-1 px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                {{ ucfirst($topic['role']) }}+
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
