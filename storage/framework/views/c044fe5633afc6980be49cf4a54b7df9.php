
<?php if(auth()->guard()->check()): ?>
<style>
    .tour-overlay {
        position: fixed; inset: 0; z-index: 9998;
        background: rgba(0,0,0,0.6);
        transition: opacity 0.2s;
    }
    .tour-spotlight {
        position: absolute; z-index: 9999;
        box-shadow: 0 0 0 9999px rgba(0,0,0,0.6);
        border-radius: 6px;
        transition: all 0.3s ease;
        pointer-events: none;
    }
    .tour-tooltip {
        position: absolute; z-index: 10000;
        background: white; border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        max-width: 340px; width: max-content; min-width: 280px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .dark .tour-tooltip { background: #1f2937; color: #e5e7eb; }
    .tour-tooltip h4 { font-size: 15px; font-weight: 700; margin-bottom: 6px; }
    .dark .tour-tooltip h4 { color: #f3f4f6; }
    .tour-tooltip p { font-size: 13px; color: #6b7280; line-height: 1.5; margin-bottom: 14px; }
    .dark .tour-tooltip p { color: #9ca3af; }
    .tour-tooltip .tour-footer { display: flex; align-items: center; justify-content: space-between; }
    .tour-tooltip .tour-dots { display: flex; gap: 5px; }
    .tour-tooltip .tour-dot { width: 7px; height: 7px; border-radius: 50%; background: #d1d5db; }
    .dark .tour-tooltip .tour-dot { background: #4b5563; }
    .tour-tooltip .tour-dot.active { background: #dc2626; }
    .tour-tooltip .tour-btns { display: flex; gap: 8px; }
    .tour-tooltip .tour-btn { padding: 6px 14px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; border: none; transition: background 0.15s; }
    .tour-tooltip .tour-btn-skip { background: transparent; color: #9ca3af; }
    .tour-tooltip .tour-btn-skip:hover { color: #6b7280; }
    .tour-tooltip .tour-btn-next { background: #dc2626; color: white; }
    .tour-tooltip .tour-btn-next:hover { background: #b91c1c; }
    .tour-tooltip .tour-arrow {
        position: absolute; width: 12px; height: 12px;
        background: white; transform: rotate(45deg);
    }
    .dark .tour-tooltip .tour-arrow { background: #1f2937; }
</style>


<div id="tour-welcome" class="fixed inset-0 z-[9999] hidden">
    <div class="fixed inset-0 bg-black/50"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-sm w-full p-6 text-center relative">
            <div class="text-4xl mb-3">&#127877;</div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Welcome to North Pole!</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
                Would you like a quick tour of the system? It only takes 30 seconds.
            </p>
            <div class="flex gap-3 justify-center">
                <button onclick="dismissTour()" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Skip for now
                </button>
                <button onclick="startTour()" class="px-4 py-2 bg-red-700 text-white rounded-md hover:bg-red-600 text-sm font-semibold transition">
                    Take the Tour
                </button>
            </div>
        </div>
    </div>
</div>

<div id="tour-spotlight" class="tour-spotlight" style="display:none;"></div>
<div id="tour-tooltip" class="tour-tooltip" style="display:none;"></div>

<script>
(function() {
    const userId = <?php echo e(auth()->id()); ?>;
    const storageKey = 'tour_completed_' + userId;
    const role = '<?php echo e(auth()->user()->isSanta() ? "santa" : (auth()->user()->isCoordinator() ? "coordinator" : "family")); ?>';

    // Tour steps - only show steps for elements that exist on the page
    const allSteps = [
        { target: '[data-tour="nav-home"]', title: 'Home', text: 'Click the logo to return to your dashboard at any time.' },
        { target: '[data-tour="nav-families"]', title: 'Families', text: 'View and manage registered families. Search, filter, and see family details including children and gift tags.' },
        { target: '[data-tour="nav-santa"]', title: 'Santa Dashboard', text: 'The admin hub. Import data, manage seasons, configure settings, generate documents, and oversee the entire operation.' },
        { target: '[data-tour="nav-delivery"]', title: 'Delivery Day', text: 'Manage delivery day operations. Assign drivers, track deliveries on a live map, and update statuses in real-time.' },
        { target: '[data-tour="nav-coordinator"]', title: 'Coordinator', text: 'Generate delivery sheets, manage volunteer assignments, and coordinate logistics.' },
        { target: '[data-tour="nav-warehouse"]', title: 'Warehouse', text: 'Scan donations, track inventory by category, and manage food box assembly.' },
        { target: '[data-tour="nav-help"]', title: 'Help & Wiki', text: 'Detailed guides for every feature. Click the ? icon anytime you need help.' },
    ];

    let steps = [];
    let currentStep = 0;

    function getVisibleSteps() {
        return allSteps.filter(s => {
            const el = document.querySelector(s.target);
            return el && el.offsetParent !== null;
        });
    }

    // Show welcome prompt on first visit
    if (!localStorage.getItem(storageKey)) {
        setTimeout(() => {
            const welcome = document.getElementById('tour-welcome');
            if (welcome) welcome.classList.remove('hidden');
        }, 800);
    }

    window.dismissTour = function() {
        localStorage.setItem(storageKey, 'true');
        document.getElementById('tour-welcome').classList.add('hidden');
    };

    window.startTour = function() {
        document.getElementById('tour-welcome').classList.add('hidden');
        steps = getVisibleSteps();
        if (steps.length === 0) { dismissTour(); return; }
        currentStep = 0;
        showStep(currentStep);
    };

    window.restartTour = function() {
        steps = getVisibleSteps();
        if (steps.length === 0) return;
        currentStep = 0;
        showStep(currentStep);
    };

    function showStep(idx) {
        const step = steps[idx];
        const el = document.querySelector(step.target);
        if (!el) { nextStep(); return; }

        const spotlight = document.getElementById('tour-spotlight');
        const tooltip = document.getElementById('tour-tooltip');

        // Position spotlight
        const rect = el.getBoundingClientRect();
        const pad = 6;
        spotlight.style.display = 'block';
        spotlight.style.left = (rect.left - pad + window.scrollX) + 'px';
        spotlight.style.top = (rect.top - pad + window.scrollY) + 'px';
        spotlight.style.width = (rect.width + pad * 2) + 'px';
        spotlight.style.height = (rect.height + pad * 2) + 'px';

        // Build tooltip content
        const dots = steps.map((_, i) =>
            `<span class="tour-dot ${i === idx ? 'active' : ''}"></span>`
        ).join('');

        const isLast = idx === steps.length - 1;
        tooltip.innerHTML = `
            <div class="tour-arrow" id="tour-arrow"></div>
            <h4>${step.title}</h4>
            <p>${step.text}</p>
            <div class="tour-footer">
                <div class="tour-dots">${dots}</div>
                <div class="tour-btns">
                    <button class="tour-btn tour-btn-skip" onclick="endTour()">Skip</button>
                    <button class="tour-btn tour-btn-next" onclick="${isLast ? 'endTour()' : 'nextStep()'}">
                        ${isLast ? 'Done' : 'Next'}
                    </button>
                </div>
            </div>
        `;

        // Position tooltip below the element
        tooltip.style.display = 'block';
        const tooltipRect = tooltip.getBoundingClientRect();
        let tooltipLeft = rect.left + rect.width / 2 - tooltipRect.width / 2;
        let tooltipTop = rect.bottom + 12 + window.scrollY;

        // Keep tooltip in viewport
        if (tooltipLeft < 10) tooltipLeft = 10;
        if (tooltipLeft + tooltipRect.width > window.innerWidth - 10) {
            tooltipLeft = window.innerWidth - tooltipRect.width - 10;
        }

        tooltip.style.left = tooltipLeft + 'px';
        tooltip.style.top = tooltipTop + 'px';

        // Position arrow
        const arrow = document.getElementById('tour-arrow');
        const arrowLeft = (rect.left + rect.width / 2) - tooltipLeft - 6;
        arrow.style.left = Math.max(16, Math.min(arrowLeft, tooltipRect.width - 28)) + 'px';
        arrow.style.top = '-6px';

        // Scroll into view if needed
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    window.nextStep = function() {
        currentStep++;
        if (currentStep >= steps.length) {
            endTour();
        } else {
            showStep(currentStep);
        }
    };

    window.endTour = function() {
        localStorage.setItem(storageKey, 'true');
        document.getElementById('tour-spotlight').style.display = 'none';
        document.getElementById('tour-tooltip').style.display = 'none';
    };

    // Allow keyboard navigation
    document.addEventListener('keydown', function(e) {
        const tooltip = document.getElementById('tour-tooltip');
        if (!tooltip || tooltip.style.display === 'none') return;
        if (e.key === 'Escape') endTour();
        if (e.key === 'ArrowRight' || e.key === 'Enter') nextStep();
    });
})();
</script>
<?php endif; ?>
<?php /**PATH C:\Users\mirod\Documents\Code\JetBrains\GFSDFoodDrive\resources\views/partials/guided-tour.blade.php ENDPATH**/ ?>