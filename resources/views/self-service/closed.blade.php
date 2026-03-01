<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Closed | GFSD Food Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-b from-red-900 via-red-800 to-green-900 flex items-center justify-center">
    <div class="max-w-lg mx-auto px-6 text-center">
        <div class="text-6xl mb-4">🎄</div>
        <h1 class="text-3xl font-bold text-white mb-3">Family Registration is Currently Closed</h1>
        <p class="text-red-200 mb-4">
            Online self-registration is not available at this time. To register your family for the food &amp; gift drive, please reach out to us or contact one of our school advisors directly.
        </p>
        @php $contactEmail = \App\Models\Setting::get('primary_contact_email', 'fooddrive@gfalls.wednet.edu'); @endphp
        <p class="text-white mb-8">
            <a href="mailto:{{ $contactEmail }}" class="underline hover:text-red-200">{{ $contactEmail }}</a>
        </p>

        @if($advisors->isNotEmpty())
            <div class="bg-white/10 backdrop-blur rounded-xl p-6 mb-8">
                <h2 class="text-lg font-semibold text-white mb-4">Contact an Advisor</h2>
                <div class="space-y-3">
                    @foreach($advisors as $advisor)
                        <div class="flex items-center justify-between bg-white/10 rounded-lg px-4 py-3">
                            <div class="text-left">
                                <p class="text-white font-medium">{{ $advisor->name }}</p>
                                @if($advisor->position)
                                    <p class="text-red-200 text-sm">{{ $advisor->position }}</p>
                                @endif
                                @if($advisor->school_source)
                                    <p class="text-red-300 text-xs">{{ $advisor->school_source }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ url('/') }}" class="inline-flex items-center px-6 py-3 bg-white text-red-800 font-semibold rounded-full shadow-lg hover:bg-red-50 transition">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
            Back to Home
        </a>
    </div>
</body>
</html>
