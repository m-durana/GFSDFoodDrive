<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gift Tag — GFSD Food Drive</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-lg mx-auto px-4 py-6">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">GFSD Food Drive</h1>
            <p class="text-sm text-gray-500">Gift Tag Scanner</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Child Info Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="bg-red-700 text-white px-4 py-3 text-center">
                <span class="text-3xl font-bold">#{{ $child->family->family_number }}</span>
            </div>
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Gender</span>
                        <p class="text-sm font-semibold text-gray-900">{{ $child->gender ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Age</span>
                        <p class="text-sm font-semibold text-gray-900">{{ $child->age ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">School</span>
                        <p class="text-sm font-semibold text-gray-900">{{ $child->school ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Current Gift Level</span>
                        @php
                            $levelColors = [
                                0 => 'bg-gray-100 text-gray-700',
                                1 => 'bg-yellow-100 text-yellow-700',
                                2 => 'bg-blue-100 text-blue-700',
                                3 => 'bg-green-100 text-green-700',
                            ];
                            $levelValue = $child->gift_level?->value ?? 0;
                        @endphp
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full {{ $levelColors[$levelValue] }}">
                            {{ $child->gift_level?->label() ?? 'None' }}
                        </span>
                    </div>
                </div>

                @if($child->clothing_options)
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Clothing</span>
                        <p class="text-sm text-gray-900">{{ $child->clothing_options }}</p>
                    </div>
                @endif
                @if($child->all_sizes)
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Sizes</span>
                        <p class="text-sm text-gray-900">{{ $child->all_sizes }}</p>
                    </div>
                @endif
                @if($child->toy_ideas)
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Toy Ideas</span>
                        <p class="text-sm text-gray-900">{{ $child->toy_ideas }}</p>
                    </div>
                @endif
                @if($child->gift_preferences)
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase">Gift Preferences</span>
                        <p class="text-sm text-gray-900">{{ $child->gift_preferences }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Update Form -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Update Gift Status</h2>

            <form method="POST" action="{{ url()->signedRoute('scan.update', ['child' => $child->id]) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Gift Level Buttons -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gift Level</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach([0 => 'None', 1 => 'Partial', 2 => 'Moderate', 3 => 'Full'] as $value => $label)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="gift_level" value="{{ $value }}" {{ $levelValue === $value ? 'checked' : '' }}
                                    class="sr-only peer">
                                <div class="border-2 rounded-lg px-4 py-3 text-center text-sm font-medium transition
                                    peer-checked:border-red-600 peer-checked:bg-red-50 peer-checked:text-red-700
                                    border-gray-200 text-gray-700 hover:bg-gray-50">
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('gift_level')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gifts Received -->
                <div>
                    <label for="gifts_received" class="block text-sm font-medium text-gray-700">Gifts Received</label>
                    <textarea name="gifts_received" id="gifts_received" rows="2"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        placeholder="e.g. Lego set, jacket, doll...">{{ old('gifts_received', $child->gifts_received) }}</textarea>
                </div>

                <!-- Adopter Name -->
                <div>
                    <label for="adopter_name" class="block text-sm font-medium text-gray-700">Adopter Name</label>
                    <input type="text" name="adopter_name" id="adopter_name"
                        value="{{ old('adopter_name', $child->adopter_name) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        placeholder="Who adopted this child's gifts?">
                </div>

                <button type="submit"
                    class="w-full py-3 bg-red-700 text-white rounded-lg font-semibold text-sm hover:bg-red-600 active:bg-red-800 transition">
                    Update Gift Status
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">GFSD Food Drive &copy; {{ date('Y') }}</p>
    </div>
</body>
</html>
