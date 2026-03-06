@php
    $logoPath = \App\Models\Setting::get('site_logo', 'logos/current-logo.png');
    $logoFile = storage_path('app/public/' . $logoPath);
    $hasFavicon = file_exists($logoFile);
    $faviconUrl = $hasFavicon ? asset('storage/' . $logoPath) : null;
    $fallbackSvg = 'data:image/svg+xml,' . rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" rx="12" fill="#dc2626"/><path d="M32 16v32M16 28h32M20 28c0-6 5-10 12-12M44 28c0-6-5-10-12-12M16 28v16a4 4 0 004 4h24a4 4 0 004-4V28z" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>');
@endphp
@if($hasFavicon)
<link rel="icon" type="image/png" href="{{ $faviconUrl }}">
<link rel="apple-touch-icon" href="{{ $faviconUrl }}">
@else
<link rel="icon" type="image/svg+xml" href="{{ $fallbackSvg }}">
<link rel="apple-touch-icon" href="{{ $fallbackSvg }}">
@endif
