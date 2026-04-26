<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route PIN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen text-slate-900 flex items-center justify-center px-4">
    <main class="w-full max-w-sm bg-white border border-slate-200 rounded-2xl shadow-xs p-6">
        <div class="mb-5">
            <p class="text-xs uppercase tracking-wide text-slate-500">Driver Route</p>
            <h1 class="text-xl font-bold text-slate-900">{{ $route->display_name }}</h1>
        </div>

        <form method="POST" action="{{ route('delivery.verifyDriverPin', $route->access_token) }}" class="space-y-4">
            @csrf
            <div>
                <label for="pin" class="block text-sm font-medium text-slate-700">Route PIN</label>
                <input id="pin" name="pin" inputmode="numeric" pattern="[0-9]*" maxlength="6" autofocus
                    class="mt-1 block w-full rounded-lg border-slate-300 text-center text-2xl tracking-widest shadow-xs focus:border-primary focus:ring-primary"
                    value="{{ old('pin') }}">
                @error('pin')
                    <p class="mt-2 text-sm text-primary">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 text-sm font-semibold text-white hover:opacity-90">
                Unlock Route
            </button>
        </form>
    </main>
</body>
</html>
