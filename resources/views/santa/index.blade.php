<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Santa Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Welcome, Santa!</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="{{ route('santa.families') }}" class="block p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <h4 class="font-medium text-gray-900">All Families</h4>
                            <p class="text-sm text-gray-500 mt-1">View all registered families</p>
                        </a>
                        <a href="{{ route('santa.users') }}" class="block p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <h4 class="font-medium text-gray-900">Manage Users</h4>
                            <p class="text-sm text-gray-500 mt-1">Add and edit system users</p>
                        </a>
                        <a href="{{ route('delivery.index') }}" class="block p-6 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <h4 class="font-medium text-gray-900">Delivery Day</h4>
                            <p class="text-sm text-gray-500 mt-1">Manage delivery logistics</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
