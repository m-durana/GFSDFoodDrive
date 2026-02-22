<x-guest-layout>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex items-center justify-center py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-8 text-center">
                <div class="text-green-600 dark:text-green-400 text-5xl mb-4">&check;</div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Registration Submitted!</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Thank you for registering your family with the GFSD Food Drive. Your information has been received and will be reviewed by our team.
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">
                    You do not need to create an account. Our volunteers will follow up with you regarding delivery scheduling and gift coordination.
                </p>
                <a href="{{ route('self-service.create') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                    Register Another Family
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
