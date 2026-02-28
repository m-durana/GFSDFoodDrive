<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Live Delivery Map
            </h2>
            <a href="<?php echo e(route('delivery.index')); ?>"
               class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white rounded-md hover:bg-gray-500 text-xs font-medium transition">
                Back to Dispatch
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex gap-4">
                <!-- Filter sidebar -->
                <div class="w-56 shrink-0 space-y-3" id="sidebar">
                    <!-- Legend -->
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Status</h4>
                        <div class="space-y-1.5">
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-yellow-500" value="pending" checked>
                                <span class="w-3 h-3 rounded-full bg-yellow-500"></span> Pending
                            </label>
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-orange-500" value="in_transit" checked>
                                <span class="w-3 h-3 rounded-full bg-orange-500"></span> In Transit
                            </label>
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-green-500" value="delivered" checked>
                                <span class="w-3 h-3 rounded-full bg-green-500"></span> Delivered
                            </label>
                            <label class="flex items-center gap-2 text-xs cursor-pointer">
                                <input type="checkbox" class="status-filter rounded text-blue-500" value="picked_up" checked>
                                <span class="w-3 h-3 rounded-full bg-blue-500"></span> Picked Up
                            </label>
                        </div>
                    </div>

                    <!-- Team filters -->
                    <?php if($teams->count() > 0): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Teams</h4>
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-xs cursor-pointer">
                                    <input type="checkbox" class="team-filter rounded" value="" checked>
                                    <span class="w-3 h-3 rounded-full bg-gray-400"></span> All / Unassigned
                                </label>
                                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" class="team-filter rounded" value="<?php echo e($team->id); ?>" checked>
                                        <span class="w-3 h-3 rounded-full" style="background: <?php echo e($team->color ?? '#6b7280'); ?>"></span>
                                        <?php echo e($team->name); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Route filters -->
                    <?php if($routes->count() > 0): ?>
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                            <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Routes</h4>
                            <div class="space-y-1.5">
                                <?php $__currentLoopData = $routes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center gap-2 text-xs cursor-pointer">
                                        <input type="checkbox" class="route-filter rounded" value="<?php echo e($route->id); ?>" checked>
                                        <?php echo e($route->name); ?>

                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-3">
                        <label class="flex items-center gap-2 text-xs cursor-pointer">
                            <input type="checkbox" id="showRouteLines" class="rounded" checked>
                            Show route lines
                        </label>
                        <label class="flex items-center gap-2 text-xs cursor-pointer mt-1.5">
                            <input type="checkbox" id="showVolunteers" class="rounded" checked>
                            Show volunteers
                        </label>
                    </div>

                    <div class="text-xs text-gray-400 dark:text-gray-500 px-1" id="last-update">Updating...</div>
                </div>

                <!-- Map -->
                <div class="flex-1">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden relative" style="height: 75vh;">
                        <div id="map" style="width: 100%; height: 100%;"></div>
                        <div id="no-data-overlay" class="hidden absolute inset-0 flex items-center justify-center bg-gray-100/80 dark:bg-gray-800/80 z-[1000]">
                            <div class="text-center p-8">
                                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">No geocoded families</p>
                                <p class="text-sm text-gray-400 mt-2">
                                    <a href="<?php echo e(route('santa.settings')); ?>" class="text-blue-500 hover:underline">Settings &rarr; Geocoding</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast" class="fixed bottom-4 right-4 z-[2000] hidden">
        <div class="bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        const map = L.map('map').setView([48.0849, -121.9683], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors', maxZoom: 19,
        }).addTo(map);

        const statusColors = { pending: '#EAB308', in_transit: '#F97316', delivered: '#22C55E', picked_up: '#3B82F6' };
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        let familyMarkers = [], volunteerMarkers = [], routeLines = [];
        let boundsSet = false;

        function getActiveStatuses() {
            return [...document.querySelectorAll('.status-filter:checked')].map(el => el.value);
        }
        function getActiveTeams() {
            return [...document.querySelectorAll('.team-filter:checked')].map(el => el.value);
        }
        function getActiveRoutes() {
            return [...document.querySelectorAll('.route-filter:checked')].map(el => el.value);
        }

        function createFamilyIcon(status) {
            const color = statusColors[status] || '#6B7280';
            return L.divIcon({
                className: '',
                html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid white;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>`,
                iconSize: [14, 14], iconAnchor: [7, 7],
            });
        }

        function createVolunteerIcon(initial) {
            return L.divIcon({
                className: '',
                html: `<div style="background:#9333EA;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);color:white;font-size:11px;font-weight:bold;display:flex;align-items:center;justify-content:center;">${initial}</div>`,
                iconSize: [24, 24], iconAnchor: [12, 12],
            });
        }

        function showToast(msg, color = 'green') {
            const t = document.getElementById('toast');
            const inner = t.querySelector('div');
            inner.className = `bg-${color}-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm font-medium`;
            inner.textContent = msg;
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 3000);
        }

        function familyPopup(f) {
            const phone = f.phone ? `<a href="tel:${f.phone}" class="text-blue-600">${f.phone}</a><br>` : '';
            const statusLabel = f.status.replace('_', ' ');
            const markBtn = (f.status !== 'delivered' && f.status !== 'picked_up')
                ? `<button onclick="markDelivered(${f.id}, this)" style="margin-top:6px;padding:3px 10px;background:#16a34a;color:white;border:none;border-radius:4px;font-size:12px;cursor:pointer;">Mark Delivered</button>`
                : '';
            return `<strong>#${f.number} ${f.name}</strong><br>${f.address}<br>${phone}Status: <em>${statusLabel}</em>${markBtn}`;
        }

        window.markDelivered = function(familyId, btn) {
            btn.disabled = true;
            btn.textContent = '...';
            fetch(`/delivery-day/${familyId}/status-ajax`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ delivery_status: 'delivered' }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    showToast('Marked delivered!');
                    updateMap();
                }
            })
            .catch(() => { btn.disabled = false; btn.textContent = 'Mark Delivered'; });
        };

        function updateMap() {
            const statuses = getActiveStatuses();
            const teamIds = getActiveTeams();
            const routeIds = getActiveRoutes();
            const showLines = document.getElementById('showRouteLines')?.checked ?? true;
            const showVols = document.getElementById('showVolunteers')?.checked ?? true;

            fetch('<?php echo e(route("delivery.mapData")); ?>')
                .then(r => r.json())
                .then(data => {
                    // Clear
                    familyMarkers.forEach(m => map.removeLayer(m));
                    volunteerMarkers.forEach(m => map.removeLayer(m));
                    routeLines.forEach(l => map.removeLayer(l));
                    familyMarkers = []; volunteerMarkers = []; routeLines = [];

                    const bounds = [];

                    // Families
                    data.families.forEach(f => {
                        if (!statuses.includes(f.status)) return;
                        const fTeam = f.team_id ? String(f.team_id) : '';
                        if (!teamIds.includes(fTeam) && !(fTeam === '' && teamIds.includes(''))) return;

                        const marker = L.marker([f.lat, f.lng], { icon: createFamilyIcon(f.status) })
                            .bindPopup(familyPopup(f))
                            .addTo(map);
                        familyMarkers.push(marker);
                        bounds.push([f.lat, f.lng]);
                    });

                    // Volunteers
                    if (showVols) {
                        data.volunteers.forEach(v => {
                            const marker = L.marker([v.lat, v.lng], { icon: createVolunteerIcon(v.initial) })
                                .bindPopup(`<strong>${v.name}</strong><br>Updated ${v.updated}`)
                                .addTo(map);
                            volunteerMarkers.push(marker);
                            bounds.push([v.lat, v.lng]);
                        });
                    }

                    // Route polylines
                    if (showLines && data.routes) {
                        data.routes.forEach(r => {
                            if (!routeIds.includes(String(r.id))) return;
                            if (r.polyline.length < 2) return;
                            const line = L.polyline(r.polyline, {
                                color: r.color || '#dc2626',
                                weight: 2, opacity: 0.6, dashArray: '6,8',
                            }).addTo(map);
                            routeLines.push(line);
                        });
                    }

                    // Fit bounds
                    if (bounds.length > 0 && !boundsSet) {
                        map.fitBounds(bounds, { padding: [30, 30] });
                        boundsSet = true;
                    }

                    // Empty state
                    const overlay = document.getElementById('no-data-overlay');
                    overlay.classList.toggle('hidden', data.families.length > 0 || data.volunteers.length > 0);

                    document.getElementById('last-update').textContent = 'Updated ' + new Date().toLocaleTimeString();
                })
                .catch(() => {
                    document.getElementById('last-update').textContent = 'Update failed';
                });
        }

        // Bind filter changes
        document.querySelectorAll('.status-filter, .team-filter, .route-filter, #showRouteLines, #showVolunteers')
            .forEach(el => el.addEventListener('change', () => { boundsSet = true; updateMap(); }));

        updateMap();
        setInterval(updateMap, 10000);
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/delivery-day/map.blade.php ENDPATH**/ ?>