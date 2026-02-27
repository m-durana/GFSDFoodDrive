@php
    $logoPath = \App\Models\Setting::get('site_logo', 'logos/current-logo.png');
    $faviconUrl = asset('storage/' . $logoPath);
@endphp
<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
