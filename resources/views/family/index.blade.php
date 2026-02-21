<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Family Entry Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Welcome, {{ auth()->user()->first_name }}!</h3>

                    <div class="flex space-x-4 mb-6">
                        <a href="{{ route('family.create') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 transition">
                            Add New Family
                        </a>
                    </div>

                    @if($families->count() > 0)
                        <h4 class="font-medium text-gray-700 mb-2">My Families</h4>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Family #</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Family Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($families as $family)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->family_number ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ $family->family_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->address }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $family->phone1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('family.show', $family) }}" class="text-red-600 hover:text-red-900">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-gray-500">No families added yet. Click "Add New Family" to get started.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
