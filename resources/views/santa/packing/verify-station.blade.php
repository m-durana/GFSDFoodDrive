<!DOCTYPE html>
<html lang="en" class="bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verification Station - GFSD Food Drive</title>
    <script>document.documentElement.classList.add('dark');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100 min-h-screen flex flex-col">

    <!-- Header -->
    <div class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-green-400">Verification Station</h1>
            <span class="text-sm text-gray-500">Scan completed packing list barcodes to verify</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-400" id="clock"></span>
            <a href="{{ route('packing.index') }}" class="text-sm text-gray-400 hover:text-gray-200 transition">Exit Station</a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="bg-gray-800/50 border-b border-gray-700 px-6 py-3">
        <div class="flex items-center justify-center gap-8">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-100" id="stat-total">{{ $counts['total'] }}</div>
                <div class="text-xs text-gray-500 uppercase tracking-wide">Total Boxes</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-400" id="stat-complete">{{ $counts['complete'] }}</div>
                <div class="text-xs text-blue-500 uppercase tracking-wide">Awaiting QA</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-green-400" id="stat-verified">{{ $counts['verified'] }}</div>
                <div class="text-xs text-green-500 uppercase tracking-wide">Verified</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-yellow-400" id="stat-remaining">{{ $counts['remaining'] }}</div>
                <div class="text-xs text-yellow-500 uppercase tracking-wide">Still Packing</div>
            </div>
            <div class="border-l border-gray-700 pl-8 text-center">
                <div class="text-2xl font-bold text-emerald-400" id="stat-today">{{ $verifiedTodayCount }}</div>
                <div class="text-xs text-emerald-500 uppercase tracking-wide">Verified Today</div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">

        <!-- Left: Scanner & Recent -->
        <div class="flex-1 flex flex-col p-6 space-y-6 overflow-y-auto">

            <!-- Scanner Input -->
            <div class="w-full max-w-2xl mx-auto">
                <div class="relative">
                    <input type="text" id="verify-input" autofocus autocomplete="off"
                        class="w-full text-center text-3xl py-6 rounded-xl bg-gray-800 border-2 border-gray-600 text-gray-100 placeholder-gray-500 focus:border-green-500 focus:ring-green-500 transition"
                        placeholder="Scan QR code or type family number...">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="w-8 h-8 text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625v2.625m3.375-2.625H13.5v3.375m0 0h3.375m0 0v-3.375m0 3.375h3.375" />
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2 text-center">Press Enter to verify. Accepts QR token (UUID) or family number.</p>
            </div>

            <!-- Feedback Area -->
            <div id="verify-feedback" class="w-full max-w-2xl mx-auto min-h-[80px] flex items-center justify-center">
                <div class="text-gray-600 text-lg">Ready to scan...</div>
            </div>

            <!-- Recently Verified Today -->
            <div class="w-full max-w-2xl mx-auto">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Recently Verified Today</h3>
                <div id="recent-list" class="space-y-2">
                    @forelse($recentlyVerified as $list)
                        <div class="flex items-center justify-between bg-gray-800 rounded-lg px-4 py-3 border border-gray-700">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <div>
                                    @if(auth()->user()?->canSeePii())
                                        <span class="font-medium text-gray-200">{{ $list->family?->family_name ?? 'Unknown' }}</span>
                                    @else
                                        <span class="font-medium text-gray-200">Family #{{ $list->family?->family_number ?? '---' }}</span>
                                    @endif
                                    <span class="text-gray-500 ml-2">#{{ $list->family?->family_number ?? '---' }}</span>
                                </div>
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $list->verified_at?->format('g:i A') }}
                                @if($list->verifier)
                                    <span class="ml-2 text-gray-600">by {{ $list->verifier->first_name }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-600 py-4">No verifications yet today.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Sidebar: Awaiting Verification -->
        <div class="w-80 bg-gray-800/50 border-l border-gray-700 flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-700">
                <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide">
                    Awaiting Verification
                    <span class="ml-1 text-blue-400" id="awaiting-count">({{ $awaitingVerification->count() }})</span>
                </h3>
            </div>
            <div id="awaiting-list" class="flex-1 overflow-y-auto divide-y divide-gray-700/50">
                @forelse($awaitingVerification as $list)
                    <div class="px-4 py-3 hover:bg-gray-700/30 transition cursor-pointer awaiting-item"
                         data-qr-token="{{ $list->qr_token }}"
                         data-list-id="{{ $list->id }}"
                         onclick="verifyByToken('{{ $list->qr_token }}')">
                        <div class="flex items-center justify-between">
                            <div>
                                @if(auth()->user()?->canSeePii())
                                    <div class="text-sm font-medium text-gray-200">{{ $list->family?->family_name ?? 'Unknown' }}</div>
                                @else
                                    <div class="text-sm font-medium text-gray-200">Family #{{ $list->family?->family_number ?? '---' }}</div>
                                @endif
                                <div class="text-xs text-gray-500">#{{ $list->family?->family_number ?? '---' }}</div>
                            </div>
                            <div class="text-xs text-gray-500">
                                @if($list->completed_at)
                                    {{ $list->completed_at->diffForHumans(null, true) }} ago
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-gray-600 text-sm">
                        No boxes awaiting verification.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Audio elements for feedback sounds -->
    <audio id="sound-success" preload="auto">
        <source src="data:audio/wav;base64,UklGRl4FAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YToFAAB/f39/f3+AgICAgICBgYKCgoKDg4SEhYWGhoaHh4iIiImJiYqKiouLi4yMjI2NjY6Ojo+Pj5CQkJGRkZGRkZKSkpKSkpOTk5OTk5OTk5OTk5OTk5OTk5KSkpKSkpKSkZGRkZGRkJCQj4+Pj46Ojo2NjYyMjIuLi4qKiomJiYiIiIeHh4aGhoWFhYSEhIODg4KCgoGBgYCAgH9/f35+fn19fXx8fHt7e3p6enl5eXh4eHd3d3Z2dnV1dXR0dHNzc3JycnFxcXBwcG9vb25ubm1tbWxsbGtrayoqKikpKSgoKCcnJyYmJiUlJSQkJCMjIyIiIiEhISAgIB8fHx4eHh0dHRwcHBsbGxoaGhkZGRgYGBcXFxYWFhUVFRQUFBMTExISEhERERAQEA8PDw4ODg0NDQwMDAsLCwoKCgkJCQgICAgICAgICAgICAgICAgICAgICAgICAgICAkJCQkJCQoKCgsLCwwMDA0NDQ4ODg8PDxAQEBERERISEhMTExQUFBUVFRYWFhcXFxgYGBkZGRoaGhsbGxwcHB0dHR4eHh8fHyAgICEhISIiIiMjIyQkJCUlJSYmJicnJygoKCkpKSoqKisrKywsLC0tLS4uLi8vLzAwMDExMTIyMjMzMzQ0NDU1NTY2Njc3Nzg4ODk5OTo6Ojt7e3x8fH19fX5+fn9/f4CAgIGBgYKCgoODg4SEhIWFhYaGhoeHh4iIiImJiYqKiouLi4yMjI2NjY6Ojo+Pj5CQkJCQkJGRkZGRkZKSkpKSkpKSkpKTk5OTk5OTk5OTk5OTk5OTk5OTk5OTk5KSkpKSkpKSkZGRkZGRkJCQkI+Pj4+Pjo6OjY2NjYyMjIuLi4uKioqJiYmJiIiIh4eHh4aGhoWFhYWEhISDg4ODgoKCgoGBgYGAgICAgH9/f39/f35+fn5+fn19fX19fX19fXx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fX19fX19fX19fn5+fn5+fn9/f39/f4CAgICAgIGBgYGBgYKCgoKCg4ODg4OEhISEhIWFhYWFhoaGhoeHh4eHiIiIiIiJiYmJiYqKioqKi4uLi4uLjIyMjIyNjY2NjY2Ojo6Ojo6Pj4+Pj4+QkJCQkJCQkJCRkZGRkZGRkZGRkZGRkZGRkZGRkQ==" type="audio/wav">
    </audio>
    <audio id="sound-error" preload="auto">
        <source src="data:audio/wav;base64,UklGRl4FAABXQVZFZm10IBAAAAABAAEARKwAAIhYAQACABAAZGF0YToFAACAf4B/gH+Af4B/gH+Af35/gICBgX6AgYF+f3+BgIGAf3+AgYGAfn+BgYB/f4CBgYB/f4GBgH5/gYGAf3+AgYGAfn+BgYB/f4CBgH9/gIGBf3+AgYF/f4CBgX9+gIGBf3+AgYF/f3+BgX9/gIGBf3+AgYF/fn+BgX9/gIGBf39/gYF/f3+BgX9/f4GBf39/gYF/f3+BgX9/f4GBgH9/gIGAf3+AgYGAfn+BgYB/f4CBgH5/gYGAf3+AgYGAfn+BgYB/f4CBgH9/gIGBf3+AgYF/f4CBgX9+gIGBf3+AgYF/fn+BgX9/gIGBf39/gYF/f3+BgX9/f4GBf39/gYF/f3+BgX9/f4GBf39/gYGAf3+AgYB/f4CBgH9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgIF/fn+BgX9/gIGBf39/gYF/f3+BgX9/f4GBf39/gYF/f3+BgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgH9/gIGAf3+AgYGAfn+BgYB/f4CBgH5/gIGBf3+AgYF/f3+BgX9/gIGBf3+AgYF/fn+BgX9/gIGBf39/gYF/f3+BgX9/f4GBf39/gYF/f3+BgX9/f4GBf39/gYGAf3+AgYB/f4CBgH9/gIGBf3+AgYF/f4CBgX9/gIGBf39/gYF/fn+BgX9/gIGBf39/gYF/f3+BgX9/f4GBf39/gYF/f3+BgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgH9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/fn+BgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgH9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBgX9/gIGBf3+AgYF/f4CBAA==" type="audio/wav">
    </audio>

    <script>
        // --- Clock ---
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- Sound ---
        function playSound(type) {
            const audio = document.getElementById('sound-' + type);
            if (audio) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
        }

        // --- Generate better sounds using Web Audio API ---
        // Lazy-init: Safari throws on AudioContext construction outside a user gesture,
        // and even desktop browsers warn about it. Build on first beep, fail silent.
        let audioCtx = null;
        function getAudioCtx() {
            if (audioCtx) return audioCtx;
            const Ctor = window.AudioContext || window.webkitAudioContext;
            if (!Ctor) return null;
            try { audioCtx = new Ctor(); } catch (_) { audioCtx = null; }
            return audioCtx;
        }

        function playBeep(frequency, duration, type = 'sine') {
            const ctx = getAudioCtx();
            if (!ctx) return;
            const oscillator = ctx.createOscillator();
            const gainNode = ctx.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(ctx.destination);
            oscillator.frequency.value = frequency;
            oscillator.type = type;
            gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + duration);
            oscillator.start(ctx.currentTime);
            oscillator.stop(ctx.currentTime + duration);
        }

        function playSuccessSound() {
            playBeep(880, 0.15);
            setTimeout(() => playBeep(1108, 0.15), 150);
            setTimeout(() => playBeep(1320, 0.25), 300);
        }

        function playErrorSound() {
            playBeep(330, 0.2, 'square');
            setTimeout(() => playBeep(262, 0.3, 'square'), 200);
        }

        // --- State ---
        let isProcessing = false;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- Input Handler ---
        const input = document.getElementById('verify-input');
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const value = this.value.trim();
                if (value && !isProcessing) {
                    processInput(value);
                }
            }
        });

        // Keep focus on input
        document.addEventListener('click', function(e) {
            if (e.target.tagName !== 'A' && !e.target.closest('a')) {
                input.focus();
            }
        });

        // --- Click-to-verify from sidebar ---
        function verifyByToken(qrToken) {
            if (!isProcessing) {
                processInput(qrToken);
            }
        }

        // --- Process Input ---
        async function processInput(value) {
            isProcessing = true;
            input.value = '';
            showFeedback('loading', 'Looking up: ' + value.substring(0, 20) + '...');

            try {
                // Determine if input is a UUID (QR token) or family number
                const isUuid = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value);

                let listId = null;

                if (isUuid) {
                    // Look up by QR token via the API
                    const lookupResp = await fetch('/api/packing/' + encodeURIComponent(value), {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!lookupResp.ok) {
                        throw new Error('Packing list not found for this QR code.');
                    }
                    const lookupData = await lookupResp.json();
                    listId = lookupData.id;
                } else {
                    // Look up by family number — search awaiting items first
                    const awaitingItems = document.querySelectorAll('.awaiting-item');
                    let found = false;
                    for (const item of awaitingItems) {
                        const familyNum = item.querySelector('.text-xs')?.textContent?.replace('#', '').trim();
                        if (familyNum === value) {
                            listId = item.dataset.listId;
                            found = true;
                            break;
                        }
                    }

                    if (!found) {
                        // Try API lookup by QR token as fallback (in case they typed something else)
                        const lookupResp = await fetch('/api/packing/' + encodeURIComponent(value), {
                            headers: { 'Accept': 'application/json' }
                        });
                        if (lookupResp.ok) {
                            const lookupData = await lookupResp.json();
                            listId = lookupData.id;
                        } else {
                            throw new Error('No packing list found for family number "' + value + '".');
                        }
                    }
                }

                if (!listId) {
                    throw new Error('Could not resolve packing list.');
                }

                // Verify the list
                const verifyResp = await fetch('/api/packing/' + listId + '/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const verifyData = await verifyResp.json();

                if (verifyData.success) {
                    playSuccessSound();
                    showFeedback('success', verifyData.message || 'Packing list verified!');
                    updateStatsAfterVerify();
                    addToRecentList(value, listId);
                    removeFromAwaitingList(listId);
                } else {
                    playErrorSound();
                    showFeedback('error', verifyData.message || 'Verification failed.');
                }
            } catch (err) {
                playErrorSound();
                showFeedback('error', err.message || 'An error occurred.');
            } finally {
                isProcessing = false;
                input.focus();
            }
        }

        // --- Feedback Display ---
        function showFeedback(type, message) {
            const el = document.getElementById('verify-feedback');
            const colors = {
                success: 'bg-green-900/50 border-green-500 text-green-300',
                error: 'bg-primary/30 border-primary text-primary-content/80',
                loading: 'bg-gray-800 border-gray-600 text-gray-400',
            };
            const icons = {
                success: '<svg class="w-8 h-8 text-green-400 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>',
                error: '<svg class="w-8 h-8 text-primary mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>',
                loading: '<svg class="w-8 h-8 text-gray-500 mr-3 shrink-0 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>',
            };

            el.innerHTML = `
                <div class="w-full max-w-2xl flex items-center justify-center p-4 rounded-xl border-2 ${colors[type]} transition-all">
                    ${icons[type]}
                    <span class="text-lg font-medium">${message}</span>
                </div>
            `;

            // Flash the entire page briefly
            if (type === 'success') {
                document.body.style.transition = 'background-color 0.3s';
                document.body.style.backgroundColor = '#064e3b';
                setTimeout(() => { document.body.style.backgroundColor = ''; }, 300);
            } else if (type === 'error') {
                document.body.style.transition = 'background-color 0.3s';
                document.body.style.backgroundColor = '#7f1d1d';
                setTimeout(() => { document.body.style.backgroundColor = ''; }, 300);
            }
        }

        // --- Update Stats ---
        function updateStatsAfterVerify() {
            const completeEl = document.getElementById('stat-complete');
            const verifiedEl = document.getElementById('stat-verified');
            const todayEl = document.getElementById('stat-today');
            const awaitingCountEl = document.getElementById('awaiting-count');

            let complete = parseInt(completeEl.textContent) || 0;
            let verified = parseInt(verifiedEl.textContent) || 0;
            let today = parseInt(todayEl.textContent) || 0;

            complete = Math.max(0, complete - 1);
            verified += 1;
            today += 1;

            completeEl.textContent = complete;
            verifiedEl.textContent = verified;
            todayEl.textContent = today;
            awaitingCountEl.textContent = '(' + complete + ')';
        }

        // --- Add to Recent List ---
        function addToRecentList(value, listId) {
            const recentList = document.getElementById('recent-list');
            const emptyMsg = recentList.querySelector('.text-center.text-gray-600');
            if (emptyMsg) emptyMsg.remove();

            const now = new Date();
            const timeStr = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });

            const entry = document.createElement('div');
            entry.className = 'flex items-center justify-between bg-gray-800 rounded-lg px-4 py-3 border border-green-700/50 animate-pulse';
            entry.innerHTML = `
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <div>
                        <span class="font-medium text-gray-200">List #${listId}</span>
                    </div>
                </div>
                <div class="text-sm text-gray-500">${timeStr}</div>
            `;

            recentList.insertBefore(entry, recentList.firstChild);
            setTimeout(() => entry.classList.remove('animate-pulse'), 2000);

            // Keep only last 10
            const items = recentList.children;
            while (items.length > 10) {
                recentList.removeChild(items[items.length - 1]);
            }
        }

        // --- Remove from Awaiting List ---
        function removeFromAwaitingList(listId) {
            const item = document.querySelector(`.awaiting-item[data-list-id="${listId}"]`);
            if (item) {
                item.style.transition = 'all 0.3s';
                item.style.opacity = '0';
                item.style.maxHeight = '0';
                item.style.overflow = 'hidden';
                setTimeout(() => item.remove(), 300);
            }
        }

        // --- Auto-refresh awaiting list periodically ---
        setInterval(async () => {
            try {
                const resp = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                // We just refresh the stats via the stats API
                const statsResp = await fetch('/api/packing/stats', {
                    headers: { 'Accept': 'application/json' }
                });
                if (statsResp.ok) {
                    const stats = await statsResp.json();
                    if (stats.status_counts) {
                        document.getElementById('stat-total').textContent = (stats.status_counts.pending || 0) + (stats.status_counts.in_progress || 0) + (stats.status_counts.complete || 0) + (stats.status_counts.verified || 0);
                        document.getElementById('stat-complete').textContent = stats.status_counts.complete || 0;
                        document.getElementById('stat-verified').textContent = stats.status_counts.verified || 0;
                        document.getElementById('stat-remaining').textContent = (stats.status_counts.pending || 0) + (stats.status_counts.in_progress || 0);
                        document.getElementById('awaiting-count').textContent = '(' + (stats.status_counts.complete || 0) + ')';
                    }
                }
            } catch (e) {
                // Silently ignore refresh errors
            }
        }, 30000); // Refresh every 30 seconds

        // Initial focus
        input.focus();
    </script>
</body>
</html>
