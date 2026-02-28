<?php
    $logoPath = \App\Models\Setting::get('site_logo', 'logos/current-logo.png');
    $faviconUrl = asset('storage/' . $logoPath);
?>
<link rel="icon" type="image/png" href="<?php echo e($faviconUrl); ?>">
<link rel="apple-touch-icon" href="<?php echo e($faviconUrl); ?>">
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/partials/favicon.blade.php ENDPATH**/ ?>