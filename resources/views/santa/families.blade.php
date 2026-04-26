<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            All Families
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-base-100 overflow-hidden shadow-xs sm:rounded-lg">
                <div class="p-6 text-base-content" x-data="{ ...sortTable(), search: '' }">
                    <div class="mb-4">
                        <input type="text" x-model="search" placeholder="Search by number, name, address, or coordinator..."
                            class="w-full sm:w-96 rounded-md border-base-300 dark:bg-gray-700 dark:text-gray-100 shadow-xs text-sm focus:border-primary focus:ring-primary">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-base-300">
                            <thead class="bg-base-200">
                                <tr>
                                    <x-sort-th key="family_number">Family #</x-sort-th>
                                    <x-sort-th key="family_name">Family Name</x-sort-th>
                                    <x-sort-th key="address">Address</x-sort-th>
                                    <x-sort-th key="entered_by">Entered By</x-sort-th>
                                </tr>
                            </thead>
                            <tbody class="bg-base-100 divide-y divide-base-300">
                                @foreach($families as $family)
                                    <tr x-show="!search || '{{ $family->family_number }}'.includes(search) || '{{ strtolower(addslashes($family->family_name)) }}'.includes(search.toLowerCase()) || '{{ strtolower(addslashes($family->address ?? '')) }}'.includes(search.toLowerCase()) || '{{ strtolower($family->user->first_name ?? '') }}'.includes(search.toLowerCase())">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm" data-sort-value="{{ $family->family_number ?? 0 }}">{{ $family->family_number ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('family.show', $family) }}" class="text-primary hover:text-primary dark:text-primary dark:hover:text-primary">{{ $family->family_name }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->user->first_name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
