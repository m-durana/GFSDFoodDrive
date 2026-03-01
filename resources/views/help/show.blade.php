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
        <div class="max-w-[90rem] mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-8">
                {{-- Sidebar: Table of Contents from article headings --}}
                <nav class="hidden lg:block w-56 shrink-0">
                    <div class="sticky top-20">
                        <a href="{{ route('help.index') }}" class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 mb-4 font-medium">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            Back to Help
                        </a>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">On this page</p>
                        <div id="toc" class="space-y-0.5">
                            {{-- Populated by JS from rendered headings --}}
                        </div>
                    </div>
                </nav>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 sm:p-8">
                        <style>
                            .wiki-content { color: #374151; line-height: 1.85; font-size: 0.95rem; }
                            .wiki-content h2, .wiki-content h3, .wiki-content h4 { color: #111827; font-weight: 600; margin-top: 2em; margin-bottom: 0.6em; }
                            .wiki-content h2 { font-size: 1.6em; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.4em; }
                            .wiki-content h3 { font-size: 1.3em; }
                            .wiki-content h4 { font-size: 1.1em; }
                            .wiki-content p { margin-bottom: 1.1em; }
                            .wiki-content ul, .wiki-content ol { margin-bottom: 1.1em; padding-left: 1.8em; }
                            .wiki-content li { margin-bottom: 0.35em; }
                            .wiki-content strong { color: #111827; font-weight: 600; }
                            .wiki-content code { color: #dc2626; background: #fef2f2; padding: 0.15em 0.4em; border-radius: 0.25em; font-size: 0.875em; }
                            .wiki-content a { color: #2563eb; text-decoration: underline; }
                            .wiki-content blockquote { border-left: 4px solid #3b82f6; background: #eff6ff; padding: 0.8em 1.2em; margin: 1.2em 0; border-radius: 0 0.5em 0.5em 0; color: #1e40af; font-size: 0.9em; }
                            .wiki-content table { border-collapse: collapse; width: 100%; margin: 1em 0; }
                            .wiki-content th, .wiki-content td { border: 1px solid #e5e7eb; padding: 0.5em 0.8em; text-align: left; }
                            .wiki-content th { background: #f9fafb; font-weight: 600; }
                            .dark .wiki-content { color: #d1d5db; }
                            .dark .wiki-content h2, .dark .wiki-content h3, .dark .wiki-content h4 { color: #f3f4f6; }
                            .dark .wiki-content h2 { border-bottom-color: #374151; }
                            .dark .wiki-content strong { color: #f3f4f6; }
                            .dark .wiki-content code { color: #f87171; background: rgba(127,29,29,0.2); }
                            .dark .wiki-content a { color: #60a5fa; }
                            .dark .wiki-content blockquote { border-left-color: #3b82f6; background: rgba(59,130,246,0.1); color: #93c5fd; }
                            .dark .wiki-content th { background: #1f2937; }
                            .dark .wiki-content th, .dark .wiki-content td { border-color: #374151; }
                            #toc a.active { color: #b91c1c; font-weight: 500; }
                            .dark #toc a.active { color: #f87171; }
                        </style>
                        <div class="wiki-content max-w-none" id="wiki-content">
                            {!! \Illuminate\Support\Str::markdown($current['content']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Build TOC from rendered headings
        document.addEventListener('DOMContentLoaded', function() {
            const content = document.getElementById('wiki-content');
            const toc = document.getElementById('toc');
            const headings = content.querySelectorAll('h2, h3');
            const tocLinks = [];

            headings.forEach((h, i) => {
                const id = 'heading-' + i;
                h.id = id;
                const a = document.createElement('a');
                a.href = '#' + id;
                a.textContent = h.textContent;
                a.className = h.tagName === 'H3'
                    ? 'block pl-3 py-1 text-xs text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition truncate'
                    : 'block py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition truncate';
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    h.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                toc.appendChild(a);
                tocLinks.push({ el: a, target: h });
            });

            // Highlight active heading on scroll
            if (tocLinks.length > 0) {
                const observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            tocLinks.forEach(l => l.el.classList.remove('active'));
                            const match = tocLinks.find(l => l.target === entry.target);
                            if (match) match.el.classList.add('active');
                        }
                    });
                }, { rootMargin: '-80px 0px -70% 0px' });

                tocLinks.forEach(l => observer.observe(l.target));
            }
        });
    </script>
</x-app-layout>
