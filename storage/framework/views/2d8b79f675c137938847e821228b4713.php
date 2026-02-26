<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($route->name); ?> - Delivery Route</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #route-map { height: 300px; width: 100%; }
        .stop-delivered { opacity: 0.5; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <?php if(session('success')): ?>
        <div class="bg-green-500 text-white px-4 py-2 text-sm text-center">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="bg-red-700 text-white px-4 py-3">
        <h1 class="text-lg font-bold"><?php echo e($route->name); ?></h1>
        <p class="text-sm text-red-200">
            <?php echo e($route->stop_count); ?> stops &middot; <?php echo e($route->formattedDistance()); ?> &middot; <?php echo e($route->formattedDuration()); ?>

        </p>
    </div>

    <!-- Map -->
    <div id="route-map"></div>

    <!-- Progress Bar -->
    <?php
        $delivered = $route->families->filter(fn($f) => $f->delivery_status?->value === 'delivered' || $f->delivery_status?->value === 'picked_up')->count();
        $total = $route->families->count();
        $pct = $total > 0 ? round(($delivered / $total) * 100) : 0;
    ?>
    <div class="bg-white px-4 py-3 shadow-sm">
        <div class="flex items-center justify-between text-sm mb-1">
            <span class="font-medium text-gray-700">Progress</span>
            <span class="text-gray-500"><?php echo e($delivered); ?>/<?php echo e($total); ?> (<?php echo e($pct); ?>%)</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: <?php echo e($pct); ?>%"></div>
        </div>
    </div>

    <!-- Stops List -->
    <div class="px-4 py-3 space-y-3">
        <?php $__currentLoopData = $route->families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $status = $family->delivery_status?->value ?? 'pending';
                $isDone = in_array($status, ['delivered', 'picked_up']);
            ?>
            <div class="bg-white rounded-lg shadow-sm p-4 <?php echo e($isDone ? 'stop-delivered' : ''); ?>">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center justify-center h-7 w-7 rounded-full text-sm font-bold
                                <?php echo e($isDone ? 'bg-green-500 text-white' : 'bg-red-700 text-white'); ?>">
                                <?php echo e($isDone ? '&#10003;' : $family->route_order); ?>

                            </span>
                            <span class="font-medium text-gray-900">#<?php echo e($family->family_number); ?> <?php echo e($family->family_name); ?></span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1 ml-9"><?php echo e($family->address); ?></p>
                        <?php if($family->phone1): ?>
                            <p class="text-sm text-gray-500 ml-9">
                                <a href="tel:<?php echo e($family->phone1); ?>" class="text-blue-600"><?php echo e($family->phone1); ?></a>
                            </p>
                        <?php endif; ?>
                        <?php if($family->delivery_reason): ?>
                            <p class="text-xs text-yellow-600 mt-1 ml-9">Note: <?php echo e($family->delivery_reason); ?></p>
                        <?php endif; ?>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                        <?php echo e($status === 'delivered' ? 'bg-green-100 text-green-800' : ''); ?>

                        <?php echo e($status === 'in_transit' ? 'bg-blue-100 text-blue-800' : ''); ?>

                        <?php echo e($status === 'pending' ? 'bg-gray-100 text-gray-800' : ''); ?>

                        <?php echo e($status === 'picked_up' ? 'bg-purple-100 text-purple-800' : ''); ?>">
                        <?php echo e(ucfirst(str_replace('_', ' ', $status))); ?>

                    </span>
                </div>

                <div class="flex items-center space-x-2 mt-3 ml-9">
                    <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo e(urlencode($family->address)); ?>"
                        target="_blank"
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs font-medium">
                        <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                        Navigate
                    </a>

                    <?php if(! $isDone): ?>
                        <form method="POST" action="<?php echo e(route('delivery.completeStop', [$route->access_token, $family])); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium"
                                onclick="return confirm('Mark #<?php echo e($family->family_number); ?> as delivered?')">
                                <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                Delivered
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('route-map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        <?php
            $stopsData = $route->families->map(function($f) {
                return [
                    'lat' => (float) $f->latitude,
                    'lng' => (float) $f->longitude,
                    'order' => $f->route_order,
                    'number' => $f->family_number,
                    'name' => $f->family_name,
                    'status' => $f->delivery_status?->value ?? 'pending',
                ];
            })->filter(function($s) {
                return $s['lat'] && $s['lng'];
            })->values();
        ?>
        const stops = <?php echo json_encode($stopsData, 15, 512) ?>;

        const bounds = [];
        const polyline = [];

        <?php if($route->start_lat && $route->start_lng): ?>
            const startLatLng = [<?php echo e($route->start_lat); ?>, <?php echo e($route->start_lng); ?>];
            L.marker(startLatLng, {
                icon: L.divIcon({
                    className: '',
                    html: '<div style="background:#333;color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;">S</div>',
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                })
            }).addTo(map).bindPopup('Start/End');
            bounds.push(startLatLng);
            polyline.push(startLatLng);
        <?php endif; ?>

        stops.forEach(s => {
            const isDone = s.status === 'delivered' || s.status === 'picked_up';
            const color = isDone ? '#22c55e' : '#dc2626';
            const marker = L.marker([s.lat, s.lng], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="background:${color};color:#fff;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;${isDone ? 'opacity:0.6;' : ''}">${isDone ? '&#10003;' : s.order}</div>`,
                    iconSize: [24, 24],
                    iconAnchor: [12, 12],
                })
            }).addTo(map).bindPopup(`#${s.number} ${s.name}`);
            bounds.push([s.lat, s.lng]);
            polyline.push([s.lat, s.lng]);
        });

        <?php if($route->start_lat && $route->start_lng): ?>
            polyline.push(startLatLng);
        <?php endif; ?>

        if (polyline.length > 1) {
            L.polyline(polyline, {color: '#dc2626', weight: 2, opacity: 0.5, dashArray: '5,10'}).addTo(map);
        }

        if (bounds.length > 0) {
            map.fitBounds(bounds, {padding: [30, 30]});
        } else {
            map.setView([48.08, -121.97], 13);
        }
    </script>
    <?php echo $__env->make('partials.grinch-overscroll', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/delivery-routes/driver.blade.php ENDPATH**/ ?>