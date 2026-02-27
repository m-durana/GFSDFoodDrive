<x-guest-layout>
    <div class="mb-4 text-center">
        @if($googleUser['avatar'])
            <img src="{{ $googleUser['avatar'] }}" alt="Profile" class="w-16 h-16 rounded-full mx-auto mb-3">
        @endif
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Welcome, {{ $googleUser['name'] }}!</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $googleUser['email'] }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
            No account exists for your email yet. Request access below and an administrator will review it.
        </p>
    </div>

    <form method="POST" action="{{ route('auth.google.submitRequest') }}" x-data="{ role: 'family' }">
        @csrf

        <!-- Role Selection -->
        <div class="mb-4">
            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300 mb-2">What role do you need?</label>

            <div class="space-y-2">
                <label class="flex items-start p-3 rounded-lg border cursor-pointer transition"
                       :class="role === 'family' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'">
                    <input type="radio" name="requested_role" value="family" x-model="role" class="mt-0.5 text-red-600 focus:ring-red-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Family / Advisor</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Enter and manage family information for the food drive</span>
                    </div>
                </label>

                <label class="flex items-start p-3 rounded-lg border cursor-pointer transition"
                       :class="role === 'coordinator' ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'">
                    <input type="radio" name="requested_role" value="coordinator" x-model="role" class="mt-0.5 text-red-600 focus:ring-red-500">
                    <div class="ml-3">
                        <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">Coordinator</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Generate gift tags, summary sheets, and coordinate deliveries</span>
                    </div>
                </label>
            </div>
            @error('requested_role')
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- School (for family role) -->
        <div class="mb-4" x-show="role === 'family'" x-transition>
            <label for="school_source" class="block font-medium text-sm text-gray-700 dark:text-gray-300">School</label>
            <select name="school_source" id="school_source"
                    class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <option value="">— Select school —</option>
                @foreach($schools as $school)
                    <option value="{{ $school }}">{{ $school }}</option>
                @endforeach
            </select>
        </div>

        <!-- Position (for coordinator role) -->
        <div class="mb-4" x-show="role === 'coordinator'" x-transition>
            <label for="position" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Position</label>
            <select name="position" id="position"
                    class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <option value="">— Select position —</option>
                @foreach($positions as $pos)
                    <option value="{{ $pos }}">{{ $pos }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                &larr; Back to login
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-600 focus:bg-red-600 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Request Access
            </button>
        </div>
    </form>
</x-guest-layout>
