@props(['interval' => 15])

<div class="inline-flex items-center gap-1.5 text-xs" {{ $attributes }}>
    <span class="relative flex h-2.5 w-2.5">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
    </span>
    <span class="font-semibold text-red-500 dark:text-red-400 uppercase tracking-wider">Live</span>
    <span class="text-gray-400 dark:text-gray-500 live-updated-ago">Updated just now</span>
</div>

@once
<script>
(function() {
    let lastUpdate = Date.now();
    const origFetch = window.fetch;
    window.fetch = function() {
        return origFetch.apply(this, arguments).then(r => {
            lastUpdate = Date.now();
            document.querySelectorAll('.live-updated-ago').forEach(el => el.textContent = 'Updated just now');
            return r;
        });
    };
    setInterval(() => {
        const secs = Math.round((Date.now() - lastUpdate) / 1000);
        const label = secs < 5 ? 'Updated just now' : `Updated ${secs}s ago`;
        document.querySelectorAll('.live-updated-ago').forEach(el => el.textContent = label);
    }, 5000);
})();
</script>
@endonce
