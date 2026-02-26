<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin Settings
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="flex gap-6">
                {{-- Sidebar Navigation --}}
                <nav class="hidden lg:block w-56 flex-shrink-0">
                    <div class="sticky top-20 space-y-1 text-sm">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2 px-3">Public Features</p>
                        <a href="#self-registration" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Self-Registration</a>
                        <a href="#adopt-a-tag" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Adopt-a-Tag</a>
                        <a href="#family-status" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Family Status Pages</a>

                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-4 mb-2 px-3">Operations</p>
                        <a href="#delivery-dates" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Delivery Dates</a>
                        <a href="#season" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Season</a>
                        <a href="#coordinator-positions" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Coordinator Positions</a>
                        <a href="#paper-size" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Paper Size</a>
                        <a href="#geocoding" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Geocoding</a>

                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-4 mb-2 px-3">Notifications</p>
                        <a href="#notifications" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Email</a>
                        <a href="#sms" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">SMS (Twilio)</a>

                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mt-4 mb-2 px-3">Integrations</p>
                        <a href="#google-oauth" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Google OAuth</a>
                        <a href="#openrouteservice" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">OpenRouteService</a>
                        <a href="#website-embed" class="settings-nav block px-3 py-1.5 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Website Embed</a>
                    </div>
                </nav>

                {{-- Main Content --}}
                <div class="flex-1 min-w-0 space-y-6">
                    <form method="POST" action="{{ route('santa.updateSettings') }}" id="settings-form">
                        @csrf

                        {{-- ═══ PUBLIC FEATURES ═══ --}}

                        <!-- Self-Registration -->
                        <div id="self-registration" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg scroll-mt-20">
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
                                            <p class="text-sm text-green-700 dark:text-green-300">Self-registration is <strong>enabled</strong>.</p>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <input type="text" readonly value="{{ url('/register-family') }}" id="registration-link"
                                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm bg-gray-50 dark:bg-gray-800">
                                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('registration-link').value).then(() => this.textContent = 'Copied!')"
                                                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">Copy</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded p-3">
                                            <p class="text-sm text-yellow-700 dark:text-yellow-300">Self-registration is <strong>disabled</strong>. Visitors will see a message with school advisor contacts.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Adopt-a-Tag -->
                        <div id="adopt-a-tag" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
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
                                            Community members can browse and claim gift tags at <code>{{ url('/adopt') }}</code>
                                        </p>
                                    </div>
                                    @if($adoptATagEnabled)
                                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded p-3">
                                            <p class="text-sm text-green-700 dark:text-green-300">Adopt-a-Tag portal is <strong>enabled</strong>.</p>
                                            <div class="mt-2 flex items-center space-x-2">
                                                <input type="text" readonly value="{{ url('/adopt') }}" id="adopt-link"
                                                    class="flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm text-sm bg-gray-50 dark:bg-gray-800">
                                                <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('adopt-link').value).then(() => this.textContent = 'Copied!')"
                                                    class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-xs font-medium transition">Copy</button>
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <label for="adopt_a_tag_deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Adoption Deadline</label>
                                        <input type="date" name="adopt_a_tag_deadline" id="adopt_a_tag_deadline" value="{{ $adoptATagDeadline }}"
                                            class="mt-1 block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">If not set, defaults to ~8 days before first delivery date.</p>
                                    </div>
                                    <div>
                                        <label for="adopt_a_tag_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Custom Portal Message</label>
                                        <textarea name="adopt_a_tag_message" id="adopt_a_tag_message" rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                            placeholder="Help make the holidays brighter for a child in Granite Falls!">{{ $adoptATagMessage }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Family Status -->
                        <div id="family-status" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Family Status Pages</h3>
                                <div>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="family_status_enabled" value="1" {{ $familyStatusEnabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable Family Status Pages</span>
                                    </label>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Families can view their status via a private link shared from their detail page.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ═══ OPERATIONS ═══ --}}

                        <!-- Delivery Dates -->
                        <div id="delivery-dates" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Delivery Dates</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Dates shown as options on family registration and edit forms.</p>
                                <input type="hidden" name="delivery_dates" id="delivery_dates_hidden" value="{{ $deliveryDates }}">
                                <div id="delivery-dates-container" class="space-y-2"></div>
                                <button type="button" onclick="addDeliveryDate()" class="mt-3 inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 text-sm font-medium transition">
                                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Add Date
                                </button>
                            </div>
                        </div>

                        <!-- Season -->
                        <div id="season" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Season</h3>
                                <div class="space-y-3">
                                    <p class="text-sm text-gray-700 dark:text-gray-300">Current Season: <strong class="text-lg">{{ $seasonYear }}</strong></p>
                                    <input type="hidden" name="season_year" value="{{ $seasonYear }}">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('santa.seasons.index') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition">Season History</a>
                                        <a href="{{ route('santa.seasons.import') }}" class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-sm font-medium transition">Import Historical Data</a>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">To start a new season, use <a href="{{ route('santa.seasons.index') }}" class="text-blue-600 hover:underline">Season History</a>.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Coordinator Positions -->
                        <div id="coordinator-positions" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Coordinator Positions</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Comma-separated list. Shown when assigning positions to users.</p>
                                <textarea name="coordinator_positions" rows="2"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    placeholder="System Engineer,Activities Coordinator,...">{{ $coordinatorPositions }}</textarea>
                            </div>
                        </div>

                        <!-- Paper Size -->
                        <div id="paper-size" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">PDF Paper Size</h3>
                                <select name="paper_size" id="paper_size"
                                    class="block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="letter" {{ \App\Models\Setting::get('paper_size', 'letter') === 'letter' ? 'selected' : '' }}>US Letter (8.5 x 11)</option>
                                    <option value="a4" {{ \App\Models\Setting::get('paper_size', 'letter') === 'a4' ? 'selected' : '' }}>A4 (210 x 297mm)</option>
                                </select>
                            </div>
                        </div>

                        {{-- ═══ NOTIFICATIONS ═══ --}}

                        <!-- Email Notifications -->
                        <div id="notifications" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Email Notifications</h3>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="notifications_enabled" value="1" {{ $notificationsEnabled ? 'checked' : '' }}
                                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable email notifications</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Adopters receive confirmation emails when claiming a tag and reminders before the deadline.
                                </p>
                            </div>
                        </div>

                        <!-- SMS -->
                        <div id="sms" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">SMS Notifications (Twilio)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Get credentials at <a href="https://console.twilio.com/" target="_blank" class="text-blue-600 hover:underline">console.twilio.com</a>. Cost: ~$0.0079/message.
                                </p>
                                <div class="space-y-4">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="sms_enabled" value="1" {{ \App\Models\Setting::get('sms_enabled', '0') === '1' ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable SMS notifications</span>
                                    </label>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="twilio_sid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Account SID</label>
                                            <input type="text" name="twilio_sid" id="twilio_sid" value="{{ \App\Models\Setting::get('twilio_sid', '') }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                placeholder="ACxxxxxxxx">
                                        </div>
                                        <div>
                                            <label for="twilio_token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Auth Token</label>
                                            <input type="password" name="twilio_token" id="twilio_token" value="{{ \App\Models\Setting::get('twilio_token', '') }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                        </div>
                                        <div>
                                            <label for="twilio_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">From Number</label>
                                            <input type="text" name="twilio_from" id="twilio_from" value="{{ \App\Models\Setting::get('twilio_from', '') }}"
                                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                placeholder="+1XXXXXXXXXX">
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Send SMS when:</p>
                                        <div class="space-y-2">
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="sms_on_registration" value="1" {{ \App\Models\Setting::get('sms_on_registration', '1') === '1' ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">A family registers</span>
                                            </label><br>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="sms_on_gift_adopted" value="1" {{ \App\Models\Setting::get('sms_on_gift_adopted', '1') === '1' ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">A gift is adopted for their child</span>
                                            </label><br>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="sms_on_in_transit" value="1" {{ \App\Models\Setting::get('sms_on_in_transit', '1') === '1' ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Delivery status changes to "in transit"</span>
                                            </label><br>
                                            <label class="inline-flex items-center">
                                                <input type="checkbox" name="sms_on_delivered" value="1" {{ \App\Models\Setting::get('sms_on_delivered', '1') === '1' ? 'checked' : '' }}
                                                    class="rounded border-gray-300 dark:border-gray-600 text-red-600 shadow-sm focus:ring-red-500">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Delivery status changes to "delivered"</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ═══ INTEGRATIONS ═══ --}}

                        <!-- Google OAuth -->
                        <div id="google-oauth" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Google Sign-In (OAuth)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Get credentials from <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="text-blue-600 hover:underline">Google Cloud Console</a>.
                                </p>
                                <div class="space-y-4">
                                    <div>
                                        <label for="google_client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client ID</label>
                                        <input type="text" name="google_client_id" id="google_client_id" value="{{ \App\Models\Setting::get('google_client_id', '') }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                            placeholder="xxxx.apps.googleusercontent.com">
                                    </div>
                                    <div>
                                        <label for="google_client_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client Secret</label>
                                        <input type="password" name="google_client_secret" id="google_client_secret" value="{{ \App\Models\Setting::get('google_client_secret', '') }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400"><strong>Callback URL:</strong> <code>{{ url('/auth/google/callback') }}</code></p>
                                </div>
                            </div>
                        </div>

                        <!-- OpenRouteService -->
                        <div id="openrouteservice" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Route Optimization (OpenRouteService)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Free API key at <a href="https://openrouteservice.org/dev/#/signup" target="_blank" class="text-blue-600 hover:underline">openrouteservice.org</a> (500 req/day).
                                </p>
                                <div>
                                    <label for="openrouteservice_key" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Key</label>
                                    <input type="text" name="openrouteservice_key" id="openrouteservice_key"
                                        value="{{ \App\Models\Setting::get('openrouteservice_key', '') }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm font-mono"
                                        placeholder="Your ORS API key">
                                </div>
                            </div>
                        </div>

                        <!-- Website Embed -->
                        <div id="website-embed" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Website Integration (Wix / Jimdo / Other)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Paste these HTML snippets into an "Embed Code" block on your external website. URLs update automatically based on your domain.
                                </p>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Staff Login Button</label>
                                        <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 font-mono text-xs text-gray-800 dark:text-gray-200 select-all break-all">
                                            &lt;a href="<span class="js-base-url">{{ url('/') }}</span>/login" style="display:inline-block;padding:12px 24px;background:#b91c1c;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;"&gt;Staff Login&lt;/a&gt;
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Adopt-a-Tag Button</label>
                                        <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 font-mono text-xs text-gray-800 dark:text-gray-200 select-all break-all">
                                            &lt;a href="<span class="js-base-url">{{ url('/') }}</span>/adopt" style="display:inline-block;padding:12px 24px;background:#b91c1c;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:14px;"&gt;Adopt a Tag&lt;/a&gt;
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Page Embed (iframe)</label>
                                        <div class="bg-gray-100 dark:bg-gray-700 rounded p-3 font-mono text-xs text-gray-800 dark:text-gray-200 select-all break-all">
                                            &lt;iframe src="<span class="js-base-url">{{ url('/') }}</span>" width="100%" height="800" frameborder="0" style="border:none;"&gt;&lt;/iframe&gt;
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        These URLs reflect your current domain (<code class="js-base-url">{{ url('/') }}</code>). In production they will automatically show your production URL.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- ═══ SAVE BUTTON (sticky) ═══ --}}
                        <div class="sticky bottom-4 mt-8 flex items-center justify-between bg-white dark:bg-gray-800 shadow-lg rounded-lg px-6 py-3 border border-gray-200 dark:border-gray-700 z-10">
                            <a href="{{ route('santa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">&larr; Dashboard</a>
                            <button type="submit" id="save-btn" class="inline-flex items-center px-6 py-2.5 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition shadow">
                                Save Settings
                            </button>
                        </div>
                    </form>

                    {{-- ═══ SEPARATE FORMS (outside main form) ═══ --}}

                    <!-- Geocoding -->
                    <div id="geocoding" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mt-6 scroll-mt-20">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Address Geocoding</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Geocode family addresses for the live delivery map. Uses free OpenStreetMap Nominatim (rate limited 1 req/sec).
                            </p>
                            <form method="POST" action="{{ route('santa.geocodeFamilies') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-500 text-sm font-medium transition"
                                        onclick="this.textContent='Geocoding...'; this.disabled=true; this.form.submit();">
                                    Geocode Missing Addresses
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Save button feedback
        document.getElementById('settings-form')?.addEventListener('submit', function() {
            syncDeliveryDates();
            const btn = document.getElementById('save-btn');
            btn.textContent = 'Saving...';
            btn.disabled = true;
            btn.classList.add('opacity-75');
        });

        // Sidebar active state on scroll
        const sections = document.querySelectorAll('[id].scroll-mt-20');
        const navLinks = document.querySelectorAll('.settings-nav');
        if (sections.length && navLinks.length) {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        navLinks.forEach(l => l.classList.remove('bg-red-50', 'dark:bg-red-900/20', 'text-red-700', 'dark:text-red-400', 'font-medium'));
                        const active = document.querySelector(`.settings-nav[href="#${entry.target.id}"]`);
                        if (active) active.classList.add('bg-red-50', 'dark:bg-red-900/20', 'text-red-700', 'dark:text-red-400', 'font-medium');
                    }
                });
            }, { rootMargin: '-20% 0px -70% 0px' });
            sections.forEach(s => observer.observe(s));
        }

        // Delivery dates
        const deliveryContainer = document.getElementById('delivery-dates-container');
        const deliveryHidden = document.getElementById('delivery_dates_hidden');
        const existingDates = deliveryHidden.value ? deliveryHidden.value.split(',').map(d => d.trim()).filter(Boolean) : [];

        function renderDeliveryDate(value = '') {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            row.innerHTML = `
                <input type="date" value="${value}" class="delivery-date-input block w-48 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                <button type="button" onclick="this.parentElement.remove(); syncDeliveryDates();"
                    class="p-1.5 text-gray-400 hover:text-red-500 transition" title="Remove">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            row.querySelector('input').addEventListener('change', syncDeliveryDates);
            deliveryContainer.appendChild(row);
        }

        function addDeliveryDate() { renderDeliveryDate(''); }

        function syncDeliveryDates() {
            const dates = [];
            deliveryContainer.querySelectorAll('.delivery-date-input').forEach(input => {
                if (input.value) {
                    const d = new Date(input.value + 'T12:00:00');
                    if (!isNaN(d)) {
                        const month = d.toLocaleString('en-US', { month: 'long' });
                        const day = d.getDate();
                        const suffix = day === 1 || day === 21 || day === 31 ? 'st' : day === 2 || day === 22 ? 'nd' : day === 3 || day === 23 ? 'rd' : 'th';
                        dates.push(`${month} ${day}${suffix}`);
                    }
                }
            });
            deliveryHidden.value = dates.join(',');
        }

        function parseDateToISO(dateStr) {
            const months = {January:0,February:1,March:2,April:3,May:4,June:5,July:6,August:7,September:8,October:9,November:10,December:11};
            const match = dateStr.match(/^(\w+)\s+(\d+)/);
            if (match) {
                const m = months[match[1]];
                const d = parseInt(match[2]);
                if (m !== undefined && d) {
                    const year = new Date().getFullYear();
                    return `${year}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                }
            }
            return '';
        }

        existingDates.forEach(d => renderDeliveryDate(parseDateToISO(d)));
        if (existingDates.length === 0) renderDeliveryDate('');
    </script>
</x-app-layout>
