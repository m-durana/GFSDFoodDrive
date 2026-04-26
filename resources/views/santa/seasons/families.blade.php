<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Season {{ $season->year }} &mdash; Families
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-base-100 shadow-xs sm:rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-base-300">
                    <thead class="bg-base-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Family Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Members</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Children</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-base-300">
                        @forelse($families as $family)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm font-medium text-base-content">{{ $family->family_number ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-base-content/80">{{ $family->family_name }}</td>
                                <td class="px-6 py-4 text-sm text-base-content/80">{{ $family->address }}</td>
                                <td class="px-6 py-4 text-sm text-base-content/80">{{ $family->phone1 }}</td>
                                <td class="px-6 py-4 text-sm text-base-content/80">{{ $family->number_of_family_members }}</td>
                                <td class="px-6 py-4 text-sm text-base-content/80">{{ $family->children_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-base-content/60">No families found for this season.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $families->links() }}</div>

            <div>
                <a href="{{ route('santa.seasons.show', $season) }}" class="text-sm text-base-content/70 hover:text-gray-900 dark:hover:text-gray-200 transition">
                    &larr; Back to Season {{ $season->year }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
