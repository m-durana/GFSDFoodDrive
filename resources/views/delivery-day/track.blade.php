<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Location Sharing
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-center space-y-6">

                <div id="status-icon" class="text-6xl">&#128205;</div>

                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="status-title">
                    Location Sharing
                </h3>

                <p class="text-sm text-gray-500 dark:text-gray-400" id="status-text">
                    Share your location so coordinators can see where delivery teams are on the live map.
                </p>

                <div id="location-info" class="hidden bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <p class="text-sm text-green-700 dark:text-green-300">
                        Sharing location every 30 seconds
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1" id="coords-display"></p>
                </div>

                <div id="error-info" class="hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                    <p class="text-sm text-red-700 dark:text-red-300" id="error-text"></p>
                </div>

                <button id="toggle-btn" onclick="toggleTracking()"
                    class="w-full py-3 bg-red-700 text-white rounded-lg font-semibold text-sm hover:bg-red-600 active:bg-red-800 transition">
                    Start Sharing Location
                </button>

                <p class="text-xs text-gray-400 dark:text-gray-500">
                    Your location is only shared while this page is open.
                    Close the tab to stop sharing.
                </p>
            </div>
        </div>
    </div>

    <script>
        let trackingInterval = null;
        let isTracking = false;

        function toggleTracking() {
            if (isTracking) {
                stopTracking();
            } else {
                startTracking();
            }
        }

        function startTracking() {
            if (!navigator.geolocation) {
                showError('Your browser does not support geolocation.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                pos => {
                    sendLocation(pos.coords.latitude, pos.coords.longitude);
                    isTracking = true;
                    document.getElementById('toggle-btn').textContent = 'Stop Sharing';
                    document.getElementById('toggle-btn').classList.remove('bg-red-700', 'hover:bg-red-600');
                    document.getElementById('toggle-btn').classList.add('bg-gray-600', 'hover:bg-gray-500');
                    document.getElementById('location-info').classList.remove('hidden');
                    document.getElementById('error-info').classList.add('hidden');
                    document.getElementById('status-title').textContent = 'Sharing Active';

                    trackingInterval = setInterval(() => {
                        navigator.geolocation.getCurrentPosition(
                            p => sendLocation(p.coords.latitude, p.coords.longitude),
                            () => {}
                        );
                    }, 30000);
                },
                err => {
                    showError('Location access denied. Please allow location access in your browser settings.');
                }
            );
        }

        function stopTracking() {
            if (trackingInterval) clearInterval(trackingInterval);
            isTracking = false;
            document.getElementById('toggle-btn').textContent = 'Start Sharing Location';
            document.getElementById('toggle-btn').classList.add('bg-red-700', 'hover:bg-red-600');
            document.getElementById('toggle-btn').classList.remove('bg-gray-600', 'hover:bg-gray-500');
            document.getElementById('location-info').classList.add('hidden');
            document.getElementById('status-title').textContent = 'Location Sharing';
        }

        function sendLocation(lat, lng) {
            fetch('{{ route("delivery.updateLocation") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ latitude: lat, longitude: lng }),
            }).then(r => {
                if (r.ok) {
                    document.getElementById('coords-display').textContent =
                        `${lat.toFixed(5)}, ${lng.toFixed(5)} — ${new Date().toLocaleTimeString()}`;
                }
            });
        }

        function showError(msg) {
            document.getElementById('error-info').classList.remove('hidden');
            document.getElementById('error-text').textContent = msg;
        }
    </script>
</x-app-layout>
