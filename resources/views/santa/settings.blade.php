<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin Settings
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('santa.updateSettings') }}">
                @csrf

                <!-- Self-Registration -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Self-Service Registration</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="self_registration_enabled" value="1" {{ $selfRegistration ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Allow families to self-register</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    When enabled, families can submit their own information via <code>{{ url('/register-family') }}</code>
                                </p>
                            </div>

                            @if($selfRegistration)
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        Self-registration is <strong>enabled</strong>. Share this link with families:
                                    </p>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <input type="text" readonly value="{{ url('/register-family') }}" id="registration-link"
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm bg-gray-50 dark:bg-gray-800">
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('registration-link').value).then(() => this.textContent = 'Copied!')"
                                            class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Self-registration is <strong>disabled</strong>. The registration link will show a 403 error.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Adopt-a-Tag Portal -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Adopt-a-Tag Portal</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="adopt_a_tag_enabled" value="1" {{ $adoptATagEnabled ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable public Adopt-a-Tag portal</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    When enabled, community members can browse and claim gift tags at <code>{{ url('/adopt') }}</code>
                                </p>
                            </div>

                            @if($adoptATagEnabled)
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                    <p class="text-sm text-green-700 dark:text-green-300">
                                        Adopt-a-Tag portal is <strong>enabled</strong>. Share this link:
                                    </p>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <input type="text" readonly value="{{ url('/adopt') }}" id="adopt-link"
                                            class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm bg-gray-50 dark:bg-gray-800">
                                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('adopt-link').value).then(() => this.textContent = 'Copied!')"
                                            class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">
                                            Copy
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                        Adopt-a-Tag portal is <strong>disabled</strong>. The link will return a 404 error.
                                    </p>
                                </div>
                            @endif

                            <div>
                                <label for="adopt_a_tag_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Adoption Deadline</label>
                                <input type="date" name="adopt_a_tag_deadline" id="adopt_a_tag_deadline" value="{{ $adoptATagDeadline }}"
                                    class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If not set, adopters will get 14 days from the date they claim a tag.</p>
                            </div>

                            <div>
                                <label for="adopt_a_tag_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Portal Message</label>
                                <textarea name="adopt_a_tag_message" id="adopt_a_tag_message" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Help make the holidays brighter for a child in Granite Falls!">{{ $adoptATagMessage }}</textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Displayed at the top of the public portal page.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Season -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Season</h3>
                        <div>
                            <label for="season_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Current Season Year</label>
                            <input type="number" name="season_year" id="season_year" value="{{ $seasonYear }}" min="2020" max="2099"
                                class="mt-1 block w-32 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        </div>
                    </div>
                </div>

                <!-- Paper Size -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">PDF Paper Size</h3>
                        <div>
                            <label for="paper_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Paper Size for Gift Tags, Family Summaries, and Delivery Sheets</label>
                            <select name="paper_size" id="paper_size"
                                class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                <option value="letter" {{ \App\Models\Setting::get('paper_size', 'letter') === 'letter' ? 'selected' : '' }}>US Letter (8.5 x 11)</option>
                                <option value="a4" {{ \App\Models\Setting::get('paper_size', 'letter') === 'a4' ? 'selected' : '' }}>A4 (210 x 297mm)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Google OAuth -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Google Sign-In (OAuth)</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Enable "Sign in with Google" on the login page. Users must have a matching email in their account.
                            Get credentials from <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label for="google_client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Google Client ID</label>
                                <input type="text" name="google_client_id" id="google_client_id"
                                    value="{{ \App\Models\Setting::get('google_client_id', '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="xxxx.apps.googleusercontent.com">
                            </div>
                            <div>
                                <label for="google_client_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Google Client Secret</label>
                                <input type="password" name="google_client_secret" id="google_client_secret"
                                    value="{{ \App\Models\Setting::get('google_client_secret', '') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="Client secret">
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                <strong>Callback URL:</strong> <code>{{ url('/auth/google/callback') }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geocode -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Address Geocoding</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Geocode family addresses for the live delivery map. Uses free OpenStreetMap Nominatim service (rate limited to 1 req/sec).
                        </p>
                        <form method="POST" action="{{ route('santa.geocodeFamilies') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition"
                                    onclick="this.textContent='Geocoding...'; this.disabled=true; this.form.submit();">
                                Geocode Missing Addresses
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Save -->
                <div class="flex items-center justify-between mt-6">
                    <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">
                        &larr; Back to Dashboard
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
