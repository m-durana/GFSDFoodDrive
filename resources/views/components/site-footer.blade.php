@props(['variant' => 'minimal'])

@php
    $footerText = \App\Models\Setting::get('footer_text', 'Made in 🇨🇭');
    $orgName = 'Granite Falls School District Food &amp; Gift Drive';
@endphp

@if($variant === 'full-dark')
    {{-- Used on welcome page --}}
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left">
                    {{ $brand ?? '' }}
                </div>
                <div class="flex items-center gap-6 text-sm">
                    {{ $links ?? '' }}
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-800 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ $orgName }}. All rights reserved.
                <span class="mx-1">&middot;</span>
                <span>{!! $footerText !!}</span>
            </div>
        </div>
    </footer>
@elseif($variant === 'dark')
    {{-- Used on adopt pages --}}
    <footer class="bg-gray-900 text-gray-400 mt-12 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <span class="font-bold text-white">GFSD Food &amp; Gift Drive</span>
                <div class="flex items-center gap-4 text-sm">
                    {{ $links ?? '' }}
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-800 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ $orgName }}.
                <span class="mx-1">&middot;</span>
                <span>{!! $footerText !!}</span>
            </div>
        </div>
    </footer>
@elseif($variant === 'light')
    {{-- Used on confirmation, status pages --}}
    <footer class="bg-gray-100 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
            Organized by {{ $orgName }}
        </div>
    </footer>
@elseif($variant === 'border')
    {{-- Used on family status page --}}
    <footer class="border-t border-gray-200 dark:border-gray-700 mt-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-6 text-center text-xs text-gray-400 dark:text-gray-500">
            Granite Falls School District Food Drive
        </div>
    </footer>
@else
    {{-- Default minimal - used in app layout --}}
    <footer class="py-4 text-center text-xs text-gray-400 dark:text-gray-600">
        <span>GFSD Food Drive &copy; {{ date('Y') }}</span>
        <span class="mx-1">&middot;</span>
        <span>{!! $footerText !!}</span>
    </footer>
@endif
