<?php
/**
 * Cinflix — Login Page
 * Browser calls Jellyfin directly (PHP server can't reach it).
 * On success, sends token to PHP session endpoint.
 */
?>
<div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-dark-950">

    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-hero-glow opacity-60"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="absolute inset-0 opacity-[0.03]" style="background-image:linear-gradient(rgba(255,255,255,.1) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.1) 1px,transparent 1px);background-size:60px 60px;"></div>
    </div>

    <div class="relative z-10 w-full max-w-md mx-4 animate-slide-up">

        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-brand-600 rounded-2xl flex items-center justify-center shadow-lg shadow-brand-900/50">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h1 class="font-display text-4xl font-bold tracking-tight">Cin<span class="text-brand-500">flix</span></h1>
            </div>
            <p class="text-gray-500 text-sm">Your personal cinema, anywhere.</p>
        </div>

        <div class="glass rounded-3xl p-8 shadow-2xl border border-white/10">
            <h2 class="text-xl font-semibold mb-6 text-gray-100">Sign in to continue</h2>

            <div id="loginError" class="hidden mb-5 px-4 py-3 bg-red-900/30 border border-red-500/30 rounded-xl text-red-300 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span id="loginErrorText"></span>
            </div>

            <form id="loginForm" class="space-y-5" novalidate>
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <input id="username" type="text" autocomplete="username" required
                            class="input-field pl-10" placeholder="Enter your username" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <input id="password" type="password" autocomplete="current-password" required
                            class="input-field pl-10 pr-10" placeholder="Enter your password" />
                        <button type="button" id="togglePassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="loginBtn"
                    class="w-full btn-primary py-3.5 text-base font-semibold mt-2 flex items-center justify-center gap-2">
                    <span id="loginBtnText">Sign In</span>
                    <svg id="loginSpinner" class="hidden w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-gray-600">Use your Jellyfin server credentials.</p>
        </div>

        <div class="mt-4 text-center">
            <span id="serverStatus" class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 animate-pulse"></span>
                Checking server…
            </span>
        </div>
    </div>
</div>

<script>
(function() {
    // ── Config (browser talks to Jellyfin directly) ──────────
    const JELLYFIN = 'https://karan.ptgn.in:8920';
    const AUTH_HDR = 'MediaBrowser Client="Cinflix Web", Device="Browser", DeviceId="cinflix-browser-001", Version="1.0.0"';

    // ── DOM refs ─────────────────────────────────────────────
    const form       = document.getElementById('loginForm');
    const errDiv     = document.getElementById('loginError');
    const errText    = document.getElementById('loginErrorText');
    const btn        = document.getElementById('loginBtn');
    const btnText    = document.getElementById('loginBtnText');
    const spinner    = document.getElementById('loginSpinner');
    const pwdInput   = document.getElementById('password');
    const statusEl   = document.getElementById('serverStatus');

    // ── Check if Jellyfin is reachable from browser ──────────
    async function checkServer() {
        try {
            const r = await fetch(JELLYFIN + '/System/Info/Public', { signal: AbortSignal.timeout(5000) });
            if (r.ok) {
                statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span> Jellyfin connected';
            } else {
                statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Server returned ' + r.status;
            }
        } catch (e) {
            statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Cannot reach Jellyfin server';
        }
    }
    checkServer();

    // ── Toggle password ───────────────────────────────────────
    document.getElementById('togglePassword').addEventListener('click', () => {
        pwdInput.type = pwdInput.type === 'password' ? 'text' : 'password';
    });

    // ── Form submit ───────────────────────────────────────────
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = pwdInput.value;
        if (!username || !password) { showError('Please fill in all fields.'); return; }

        setLoading(true);
        hideError();

        try {
            // ── STEP 1: Browser → Jellyfin (direct) ──────────
            let jellyRes;
            try {
                jellyRes = await fetch(JELLYFIN + '/Users/AuthenticateByName', {
                    method:  'POST',
                    headers: {
                        'Content-Type':  'application/json',
                        'Authorization': AUTH_HDR,
                        'X-Emby-Authorization': AUTH_HDR,
                    },
                    body: JSON.stringify({ Username: username, Pw: password }),
                    signal: AbortSignal.timeout(15000),
                });
            } catch (netErr) {
                showError('Cannot reach Jellyfin server. Make sure you\'re on the same network. (' + netErr.message + ')');
                setLoading(false);
                return;
            }

            if (jellyRes.status === 401) {
                showError('Wrong username or password.');
                setLoading(false);
                return;
            }
            if (!jellyRes.ok) {
                showError('Jellyfin error: HTTP ' + jellyRes.status);
                setLoading(false);
                return;
            }

            const jellyData = await jellyRes.json();
            const token    = jellyData.AccessToken;
            const userId   = jellyData.User?.Id;
            const userName = jellyData.User?.Name;

            if (!token || !userId) {
                showError('Jellyfin returned an unexpected response. No token received.');
                setLoading(false);
                return;
            }

            // ── STEP 2: Send token to PHP to store in session ─
            const sessionRes = await fetch('/cinflix/api/auth.php?action=login', {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json' },
                body:        JSON.stringify({
                    token:    token,
                    userId:   userId,
                    userName: userName,
                    userData: jellyData.User ?? {},
                }),
            });

            const sessionData = await sessionRes.json();

            if (sessionData.success) {
                // Also persist to localStorage as fallback
                localStorage.setItem('cf_token',    token);
                localStorage.setItem('cf_userId',   userId);
                localStorage.setItem('cf_userName', userName);

                // Redirect after short delay to allow session cookie to set
                setTimeout(() => window.location.replace('/cinflix/?page=home'), 200);
            } else {
                showError('Session error: ' + (sessionData.error || 'Could not save session.'));
                setLoading(false);
            }

        } catch (err) {
            showError('Unexpected error: ' + err.message);
            setLoading(false);
        }
    });

    function setLoading(on) {
        btn.disabled         = on;
        btnText.textContent  = on ? 'Signing in…' : 'Sign In';
        spinner.classList.toggle('hidden', !on);
        btn.classList.toggle('opacity-70', on);
    }
    function showError(msg) {
        errText.textContent = msg;
        errDiv.classList.remove('hidden');
    }
    function hideError() {
        errDiv.classList.add('hidden');
    }
})();
</script>
