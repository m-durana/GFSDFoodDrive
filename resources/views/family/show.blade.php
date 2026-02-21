<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $family->family_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-gray-500">Family detail view will be fully implemented in Phase 3.</p>
                    <a href="{{ route('family.index') }}" class="mt-4 inline-block text-red-600 hover:text-red-900">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
