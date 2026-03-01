@php
    $logoPath = \App\Models\Setting::get('site_logo', 'logos/current-logo.png');
    $logoFile = storage_path('app/public/' . $logoPath);
    $hasFavicon = file_exists($logoFile);
    $faviconUrl = $hasFavicon ? asset('storage/' . $logoPath) : null;
    $fallbackSvg = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#dc2626"/><text x="32" y="44" text-anchor="middle" font-size="36" fill="#fff">🎁</text></svg>');
@endphp
@if($hasFavicon)
<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@else
<link rel="icon" type="image/svg+xml" href="{{ $fallbackSvg }}">
<link rel="apple-touch-icon" href="{{ $fallbackSvg }}">
@endif
