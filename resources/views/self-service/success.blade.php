<x-public-layout>
    <div class="min-h-screen bg-base-200 flex items-center justify-center py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-8 text-center">
                <div class="text-green-600 dark:text-green-400 text-5xl mb-4">&check;</div>
                <h1 class="text-2xl font-bold text-base-content mb-3">{{ __('Registration Submitted!') }}</h1>
                <p class="text-base-content/70 mb-6">
                    {{ __('Thank you for registering your family with the GFSD Food Drive. Your information has been received and will be reviewed by our team.') }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mb-6">
                    {{ __('You do not need to create an account. Our volunteers will follow up with you regarding delivery scheduling and gift coordination.') }}
                </p>
                <a href="{{ route('self-service.create') }}" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md hover:opacity-90 text-sm font-medium transition">
                    {{ __('Register Another Family') }}
                </a>
            </div>
        </div>
    </div>
</x-public-layout>
