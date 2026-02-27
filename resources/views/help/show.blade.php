<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $current['title'] }}
            </h2>
            <a href="{{ route('help.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                &larr; All Topics
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-8">
                {{-- Sidebar --}}
                <nav class="hidden lg:block w-48 shrink-0">
                    <div class="sticky top-20 space-y-1">
                        @foreach($topics as $topic)
                            <a href="{{ route('help.show', $topic['slug']) }}"
                               class="block px-3 py-1.5 rounded-md text-sm transition
                                   {{ $topic['slug'] === $current['slug']
                                       ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 font-medium'
                                       : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                                {{ $topic['title'] }}
                            </a>
                        @endforeach
                    </div>
                </nav>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 sm:p-8">
                        <div class="prose dark:prose-invert max-w-none prose-headings:text-gray-900 dark:prose-headings:text-gray-100 prose-p:text-gray-600 dark:prose-p:text-gray-400 prose-li:text-gray-600 dark:prose-li:text-gray-400 prose-strong:text-gray-900 dark:prose-strong:text-gray-100 prose-code:text-red-600 dark:prose-code:text-red-400">
                            {!! \Illuminate\Support\Str::markdown($current['content']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
