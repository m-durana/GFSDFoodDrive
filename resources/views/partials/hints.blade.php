{{-- Popup hints system — shows contextual help tips that users can dismiss --}}
@if(\App\Models\Setting::get('hints_enabled', '1') === '1')
<style>
    .hint-bubble {
        position: relative;
        display: inline-block;
    }
    .hint-bubble .hint-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #dc2626;
        color: white;
        font-size: 11px;
        font-weight: bold;
        cursor: pointer;
        vertical-align: middle;
        margin-left: 4px;
    }
    .hint-bubble .hint-popup {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: #e5e7eb;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 13px;
        line-height: 1.5;
        width: max-content;
        min-width: 280px;
        max-width: 450px;
        white-space: normal;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 50;
    }
    .hint-bubble .hint-popup::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 6px solid transparent;
        border-bottom-color: #1f2937;
    }
    .hint-bubble:hover .hint-popup,
    .hint-bubble .hint-icon:focus + .hint-popup {
        display: block;
    }
    .hint-bubble .hint-dismiss {
        float: right;
        font-size: 10px;
        color: #9ca3af;
        cursor: pointer;
        margin-left: 8px;
    }
    .hint-bubble .hint-dismiss:hover { color: white; }
</style>
<script>
    function dismissHint(key, el) {
        el.closest('.hint-bubble').style.display = 'none';
        const dismissed = JSON.parse(localStorage.getItem('dismissedHints') || '[]');
        if (!dismissed.includes(key)) {
            dismissed.push(key);
            localStorage.setItem('dismissedHints', JSON.stringify(dismissed));
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const dismissed = JSON.parse(localStorage.getItem('dismissedHints') || '[]');
        document.querySelectorAll('.hint-bubble').forEach(el => {
            if (dismissed.includes(el.dataset.hintKey)) {
                el.style.display = 'none';
            }
            // Reposition tooltip if it overflows viewport
            el.addEventListener('mouseenter', function() {
                const popup = el.querySelector('.hint-popup');
                if (!popup) return;
                popup.style.left = '50%';
                popup.style.transform = 'translateX(-50%)';
                requestAnimationFrame(() => {
                    const rect = popup.getBoundingClientRect();
                    if (rect.right > window.innerWidth - 8) {
                        popup.style.left = 'auto';
                        popup.style.right = '0';
                        popup.style.transform = 'none';
                    } else if (rect.left < 8) {
                        popup.style.left = '0';
                        popup.style.transform = 'none';
                    }
                });
            });
        });
    });
</script>
@endif
