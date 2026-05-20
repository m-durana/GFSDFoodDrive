<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Route PIN') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- REL-43: prevent iOS focus-zoom on form controls. --}}
    <style>@media (max-width:767px){input:not([type=checkbox]):not([type=radio]):not([type=range]),select,textarea{font-size:16px!important}}</style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900 flex items-center justify-center px-4">
    <main class="w-full max-w-sm bg-white border border-slate-200 rounded-2xl shadow-xs p-6">
        <div class="mb-5 flex items-start justify-between gap-2">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ __('Driver Route') }}</p>
                <h1 class="text-xl font-bold text-slate-900">{{ $route->display_name }}</h1>
            </div>
            @php $other = app()->getLocale() === 'es' ? 'en' : 'es'; @endphp
            <a href="?{{ http_build_query(array_merge(request()->query(), ['lang' => $other])) }}" class="text-slate-500 text-xs underline">
                {{ $other === 'es' ? 'ES' : 'EN' }}
            </a>
        </div>

        <form method="POST" action="{{ route('delivery.verifyDriverPin', $route->access_token) }}" class="space-y-4">
            @csrf
            <div>
                <label for="pin" class="block text-sm font-medium text-slate-700">{{ __('Route PIN') }}</label>
                <input id="pin" name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus
                    class="mt-1 block w-full rounded-lg border-slate-300 text-center text-2xl tracking-widest shadow-xs focus:border-primary focus:ring-primary"
                    value="{{ old('pin') }}">
                @error('pin')
                    <p class="mt-2 text-sm text-primary">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white hover:opacity-90">
                {{ __('Unlock Route') }}
            </button>
        </form>
    </main>
</body>
</html>
