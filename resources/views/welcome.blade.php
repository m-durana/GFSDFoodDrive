<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GFSD Food &amp; Gift Drive &mdash; Love in Action</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 antialiased">

    {{-- Navigation --}}
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur border-b border-gray-200 dark:border-gray-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            @php $siteLogo = \App\Models\Setting::get('site_logo', 'logos/current-logo.png'); @endphp
            <div class="flex items-center gap-3">
                <img src="{{ asset('storage/' . $siteLogo) }}" alt="GFSD Food Drive" class="h-10 w-auto" onerror="this.style.display='none'">
                <span class="font-bold text-lg text-red-700 dark:text-red-500">GFSD Food &amp; Gift Drive</span>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="#about" class="hidden md:inline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">About</a>
                <a href="#get-involved" class="hidden md:inline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">Get Involved</a>
                <a href="#contact" class="hidden md:inline text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 transition">Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25a2.25 2.25 0 0 1-2.25-2.25v-2.25Z" /></svg>
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-medium transition">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                        Staff Login
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-28 bg-gradient-to-br from-red-700 via-red-800 to-red-900 text-white overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-8xl">&#10052;</div>
            <div class="absolute top-20 right-20 text-6xl">&#127876;</div>
            <div class="absolute bottom-10 left-1/3 text-7xl">&#10052;</div>
            <div class="absolute bottom-20 right-10 text-9xl">&#127873;</div>
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <img src="{{ asset('storage/' . $siteLogo) }}" alt="GFSD Food Drive Logo" class="mx-auto h-28 sm:h-36 w-auto mb-6 drop-shadow-lg" onerror="this.style.display='none'">
            <p class="text-sm uppercase tracking-widest text-red-200 mb-4">Granite Falls School District</p>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight mb-4">
                Food &amp; Gift Drive
            </h1>
            <p class="text-2xl sm:text-3xl font-light text-red-100 italic">Love in Action</p>
            <p class="text-base sm:text-lg text-red-200 max-w-2xl mx-auto mt-6">
                The GFSD Food &amp; Gift Drive collects non-perishable food items, toiletries, and gifts
                for families in our community. More families than ever before need help this winter.
            </p>
            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                @if($adoptionEnabled)
                    <a href="{{ route('adopt.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 bg-white text-red-700 rounded-lg hover:bg-red-50 text-base font-semibold shadow-lg transition">
                        &#127873; Adopt a Tag
                    </a>
                @endif
                @if($selfRegistrationEnabled)
                    <a href="{{ route('self-service.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 bg-red-600 text-white border-2 border-white/30 rounded-lg hover:bg-red-500 text-base font-semibold transition">
                        &#128221; Register Your Family
                    </a>
                @endif
                <a href="#get-involved" class="w-full sm:w-auto inline-flex justify-center items-center px-8 py-3 border-2 border-white/40 text-white rounded-lg hover:bg-white/10 text-base font-semibold transition">
                    How to Help
                </a>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">About the Food Drive</h2>
                <div class="mt-2 h-1 w-16 bg-red-600 mx-auto rounded"></div>
            </div>

            <div class="max-w-3xl mx-auto text-center mb-12">
                <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                    Welcome to the <strong class="text-gray-900 dark:text-gray-100">Granite Falls School District Food &amp; Gift Drive</strong>!
                    Every year, students, staff, and community volunteers come together to make the holiday season
                    brighter for families in need. Food and toiletries can be received at each GFSD school building
                    and the District office.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Community Driven</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Run by students, staff, and volunteers from across the Granite Falls School District.
                        Every effort counts toward helping our neighbors.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Gifts &amp; Food</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Pick up a Giving Tree tag at your child's school to buy gifts for children in need.
                        You can adopt individual children or an entire family. Cash donations go toward fresh food.
                    </p>
                </div>
                <div class="text-center">
                    <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
                    </div>
                    <h3 class="font-semibold text-lg mb-2">Delivered with Care</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Volunteer delivery teams bring everything directly to families' homes,
                        ensuring every household is reached during the holiday season.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Get Involved Section --}}
    <section id="get-involved" class="py-16 sm:py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">How You Can Help</h2>
                <div class="mt-2 h-1 w-16 bg-red-600 mx-auto rounded"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    There are many ways to get involved with the Food &amp; Gift Drive. Every contribution makes a difference.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($adoptionEnabled)
                    <a href="{{ route('adopt.index') }}" class="group block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">Adopt a Tag</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Pick up a Giving Tree tag and buy a gift for a child in need. You can adopt individual children or an entire family.
                                </p>
                            </div>
                        </div>
                    </a>
                @endif

                @if($selfRegistrationEnabled)
                    <a href="{{ route('self-service.create') }}" class="group block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm hover:shadow-md hover:border-red-300 dark:hover:border-red-700 transition">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-lg flex items-center justify-center">
                                <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 group-hover:text-red-700 dark:group-hover:text-red-400 transition">Register Your Family</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    If your family could use help this holiday season, sign up to receive food and gifts.
                                    All information is kept confidential.
                                </p>
                            </div>
                        </div>
                    </a>
                @endif

                <div class="group block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Donate</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Cash donations supplement non-perishable food with fresh food items.
                                Drop off non-perishable items and toiletries at any GFSD school building or the District office.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="group block p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.05 4.575a1.575 1.575 0 1 0-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 0 1 3.15 0v1.5m-3.15 0 .075 5.925m3.075.75V4.575m0 0a1.575 1.575 0 0 1 3.15 0V15M6.9 7.575a1.575 1.575 0 1 0-3.15 0v8.175a6.75 6.75 0 0 0 6.75 6.75h2.018a5.25 5.25 0 0 0 3.712-1.538l1.732-1.732a5.25 5.25 0 0 0 1.538-3.712l.003-2.024a.668.668 0 0 0-.294-.554 1.575 1.575 0 0 0-2.471.949l-.345 1.38c-.072.288-.307.484-.6.484H9.075l.186-7.413Z" /></svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">Volunteer</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Help sort donations, wrap gifts, or join a delivery team.
                                Contact us at <a href="mailto:fooddrive@gfalls.wednet.edu" class="text-red-600 hover:underline">fooddrive@gfalls.wednet.edu</a> to sign up.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Family Status Check --}}
            @if(\App\Models\Setting::get('family_status_enabled', '0') === '1')
                <div class="mt-10 text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Already registered?</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Check your family's status using the link you received when you registered.
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Coordinator Team Section --}}
    <section class="py-16 sm:py-24 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Coordinator Team</h2>
                <div class="mt-2 h-1 w-16 bg-red-600 mx-auto rounded"></div>
                <p class="mt-4 text-gray-600 dark:text-gray-400 max-w-xl mx-auto">
                    The Food Drive is organized by a dedicated team of student coordinators working alongside staff advisors.
                </p>
            </div>

            @php
                $coordinators = \App\Models\User::whereNotNull('position')
                    ->where('position', '!=', '')
                    ->where('permission', '>=', 8)
                    ->orderByRaw("CASE WHEN position = 'System Engineer' THEN 0 ELSE 1 END")
                    ->orderBy('position')
                    ->get();
            @endphp

            @if($coordinators->count() > 0)
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($coordinators as $coord)
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center shadow-sm border border-gray-200 dark:border-gray-700">
                            <div class="mx-auto w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mb-3">
                                <span class="text-red-700 dark:text-red-400 font-bold text-lg">{{ strtoupper(substr($coord->first_name, 0, 1)) }}{{ strtoupper(substr($coord->last_name, 0, 1)) }}</span>
                            </div>
                            <div class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $coord->first_name }} {{ $coord->last_name }}</div>
                            <div class="text-xs text-red-600 dark:text-red-400 mt-0.5">{{ $coord->position }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 dark:text-gray-500">
                    <p class="text-sm">Coordinator team will be announced soon.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Contact Section --}}
    <section id="contact" class="py-16 sm:py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Contact Us</h2>
                <div class="mt-2 h-1 w-16 bg-red-600 mx-auto rounded"></div>
            </div>

            <div class="max-w-2xl mx-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center">
                        <svg class="mx-auto h-8 w-8 text-red-600 dark:text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Email</h3>
                        <a href="mailto:fooddrive@gfalls.wednet.edu" class="text-sm text-red-600 dark:text-red-400 hover:underline">fooddrive@gfalls.wednet.edu</a>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Questions about donations or receiving food and gifts</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 text-center">
                        <svg class="mx-auto h-8 w-8 text-red-600 dark:text-red-400 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        <h3 class="font-medium text-gray-900 dark:text-gray-100 mb-1">Drop-Off Locations</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Any GFSD school building<br>or the District Office</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Granite Falls, WA 98252</p>
                    </div>
                </div>

                {{-- Social Links --}}
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="https://www.facebook.com/GFSDFoodDrive/" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-blue-600 hover:border-blue-300 dark:hover:text-blue-400 transition" title="Facebook">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://granite.gfalls.wednet.edu/fooddrive/" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-red-600 hover:border-red-300 dark:hover:text-red-400 transition" title="Official Website">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Sponsors Section --}}
    @php
        $sponsorLogos = json_decode(\App\Models\Setting::get('sponsor_logos', '[]'), true) ?: [];
        // Also include default sponsor images from public/images/sponsors if no uploaded ones
        if (empty($sponsorLogos)) {
            $defaultSponsors = glob(public_path('images/sponsors/*.{jpg,png,gif,webp}'), GLOB_BRACE);
            foreach ($defaultSponsors as $file) {
                $sponsorLogos[] = ['path' => null, 'name' => pathinfo($file, PATHINFO_FILENAME), 'url' => asset('images/sponsors/' . basename($file))];
            }
        }
    @endphp
    @if(count($sponsorLogos) > 0)
        <section class="py-12 sm:py-16 bg-gray-50 dark:bg-gray-900">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Our Sponsors</h2>
                    <div class="mt-2 h-1 w-12 bg-red-600 mx-auto rounded"></div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Thank you to our generous sponsors for supporting the Food &amp; Gift Drive.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-8">
                    @foreach($sponsorLogos as $sponsor)
                        <div class="flex flex-col items-center gap-2">
                            <img src="{{ $sponsor['url'] ?? asset('storage/' . $sponsor['path']) }}"
                                 alt="{{ $sponsor['name'] ?? 'Sponsor' }}"
                                 class="h-16 sm:h-20 w-auto max-w-[160px] object-contain grayscale hover:grayscale-0 transition">
                            @if(!empty($sponsor['name']))
                                <span class="text-xs text-gray-400">{{ ucwords(str_replace('-', ' ', $sponsor['name'])) }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-center sm:text-left">
                    <div class="flex items-center gap-2 justify-center sm:justify-start">
                        <img src="{{ asset('storage/' . $siteLogo) }}" alt="" class="h-8 w-auto" onerror="this.outerHTML='<span class=\'text-xl\'>&#10052;</span>'">
                        <span class="font-bold text-white">GFSD Food &amp; Gift Drive</span>
                    </div>
                    <p class="text-sm mt-1">Granite Falls School District &mdash; Love in Action</p>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('login') }}" class="hover:text-white transition">Staff Login</a>
                    @if($adoptionEnabled)
                        <a href="{{ route('adopt.index') }}" class="hover:text-white transition">Adopt a Tag</a>
                    @endif
                    <a href="https://granite.gfalls.wednet.edu/fooddrive/" target="_blank" class="hover:text-white transition">Official Website</a>
                </div>
            </div>
            <div class="mt-8 pt-6 border-t border-gray-800 text-center text-xs text-gray-500">
                &copy; {{ date('Y') }} Granite Falls School District Food &amp; Gift Drive. All rights reserved.
                <span class="mx-1">&middot;</span>
                <span>Made in 🇨🇭</span>
            </div>
        </div>
    </footer>

    @include('partials.grinch-overscroll')
</body>
</html>
