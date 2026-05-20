<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Adopt a Tag') }} - {{ __('GFSD Food Drive') }}</title>
    <script>
        if (localStorage.getItem('darkMode') === 'true' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-base-content">
    <div class="min-h-screen flex flex-col items-center justify-center px-4">
        <div class="max-w-lg w-full text-center">
            <div class="text-6xl mb-6">&#127873;</div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ __('Online Adoption is Currently Disabled') }}</h1>
            <p class="text-lg text-base-content/70 mb-6">
                {{ __('The online Adopt-a-Tag portal is not available right now. To adopt a physical gift tag, please visit the front office at Granite Falls High School.') }}
            </p>

            @php $contactEmail = \App\Models\Setting::get('primary_contact_email', 'fooddrive@gfalls.wednet.edu'); @endphp
            <p class="text-sm text-base-content/60 mb-8">
                {{ __('Questions? Contact us at') }}
                <a href="mailto:{{ $contactEmail }}" class="text-primary dark:text-primary hover:underline">{{ $contactEmail }}</a>
            </p>

            <div class="space-y-3">
                <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:opacity-90 font-medium transition">
                    {{ __('Back to Homepage') }}
                </a>
            </div>

            <p class="mt-8 text-xs text-base-content/50">
                GFSD Food Drive &copy; {{ date('Y') }}
            </p>
        </div>
    </div>
</body>
</html>
