{{-- REL-08: PWA manifest + service-worker registration.
     One-line include in every <head>. Service worker is opt-in via the
     `pwa_install_shell_enabled` setting (default 1) so we can kill-switch
     it from Santa Settings if a buggy SW gets cached on volunteer phones. --}}
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="#C5261B">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<link rel="apple-touch-icon" href="/images/logo-default.png">
@if(\App\Models\Setting::get('pwa_install_shell_enabled', '1') === '1')
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        });
    }
</script>
@endif
