<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-base-content leading-tight">
            Location Sharing
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-base-100 shadow-xs sm:rounded-lg p-6 text-center space-y-6">

                <div id="status-icon" class="text-6xl">&#128205;</div>

                <h3 class="text-lg font-medium text-base-content" id="status-title">
                    Location Sharing
                </h3>

                <p class="text-sm text-base-content/60" id="status-text">
                    Share your location so coordinators can see where delivery teams are on the live map.
                </p>

                <div id="location-info" class="hidden bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                    <p class="text-sm text-green-700 dark:text-green-300">
                        Sharing location every 30 seconds
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1" id="coords-display"></p>
                </div>

                <div id="error-info" class="hidden bg-primary/5 dark:bg-primary/20 border border-primary/30 dark:border-primary rounded-lg p-4">
                    <p class="text-sm text-primary dark:text-primary" id="error-text"></p>
                </div>

                <button id="toggle-btn" onclick="toggleTracking()"
                    class="w-full py-3 bg-primary text-white rounded-lg font-semibold text-sm hover:opacity-90 active:opacity-80 transition">
                    Start Sharing Location
                </button>

                <p class="text-xs text-base-content/50">
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
                    document.getElementById('toggle-btn').classList.remove('bg-primary', 'hover:opacity-90');
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
            document.getElementById('toggle-btn').classList.add('bg-primary', 'hover:opacity-90');
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
