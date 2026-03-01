<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Mobile Scanner
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8 text-center">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8">
                <div class="text-6xl mb-4">&#x1F4F1;</div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Coming Soon</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    The phone-based camera scanner is under development. For now, please use the
                    <a href="{{ route('warehouse.kiosk') }}" class="text-red-600 dark:text-red-400 hover:underline">desktop kiosk</a>
                    with a USB barcode scanner or type barcodes manually.
                </p>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                    Camera scanning coming in a future update
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
