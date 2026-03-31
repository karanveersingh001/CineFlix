/**
 * Cinflix - Main App Module
 * Navigation, search, global event handlers
 */

document.addEventListener('DOMContentLoaded', () => {

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================
    const navbar = document.getElementById('navbar');
    if (navbar) {
        const updateNav = () => {
            if (window.scrollY > 60) {
                navbar.style.background = 'rgba(10,10,20,0.97)';
                navbar.style.backdropFilter = 'blur(20px)';
                navbar.style.borderBottom = '1px solid rgba(255,255,255,0.05)';
                navbar.style.boxShadow = '0 4px 30px rgba(0,0,0,0.5)';
            } else {
                navbar.style.background = 'transparent';
                navbar.style.backdropFilter = 'none';
                navbar.style.borderBottom = 'none';
                navbar.style.boxShadow = 'none';
            }
        };
        window.addEventListener('scroll', updateNav, { passive: true });
        updateNav();
    }

    // ============================================================
    // PROFILE DROPDOWN
    // ============================================================
    const profileToggle   = document.getElementById('profileToggle');
    const profileDropdown = document.getElementById('profileDropdown');

    if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            profileDropdown.classList.toggle('hidden');
        });
        document.addEventListener('click', () => profileDropdown.classList.add('hidden'));
    }

    // ============================================================
    // MOBILE MENU
    // ============================================================
    const mobileToggle = document.getElementById('mobileMenuToggle');
    const mobileMenu   = document.getElementById('mobileMenu');

    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
    }

    // ============================================================
    // SEARCH BAR TOGGLE
    // ============================================================
    const searchToggle = document.getElementById('searchToggle');
    const searchBar    = document.getElementById('searchBar');
    const searchInput  = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', () => {
            searchBar.classList.toggle('hidden');
            if (!searchBar.classList.contains('hidden') && searchInput) {
                searchInput.focus();
            }
        });

        // Close search bar on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') searchBar.classList.add('hidden');
        });
    }

    // ============================================================
    // INLINE SEARCH (navbar)
    // ============================================================
    if (searchInput && searchResults) {
        let searchTimer;

        searchInput.addEventListener('input', () => {
            const q = searchInput.value.trim();
            clearTimeout(searchTimer);

            if (!q) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }

            searchTimer = setTimeout(async () => {
                const data  = await API.get('search', { q });
                const items = (data?.Items || []).slice(0, 8);

                if (!items.length) {
                    searchResults.innerHTML = '<p class="text-gray-600 text-sm py-2 text-center">No results found</p>';
                    searchResults.classList.remove('hidden');
                    return;
                }

                searchResults.innerHTML = `
                    <div class="flex flex-wrap gap-3">
                        ${items.map(item => {
                            const imgUrl = `${window.CINFLIX.baseUrl}/Items/${item.Id}/Images/Primary?width=80&quality=80`;
                            return `
                            <a href="/cinflix/?page=detail&id=${item.Id}"
                               class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition-colors w-full sm:w-[calc(50%-6px)] cursor-pointer">
                                <img src="${imgUrl}" class="w-10 h-14 object-cover rounded-lg flex-shrink-0 bg-dark-700"
                                     onerror="this.src=''" alt="${item.Name}" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-200 truncate">${item.Name}</p>
                                    <p class="text-xs text-gray-500">${item.Type === 'Series' ? 'TV Show' : item.Type} ${item.ProductionYear ? '· ' + item.ProductionYear : ''}</p>
                                </div>
                            </a>`;
                        }).join('')}
                    </div>
                    <a href="/cinflix/?page=search&q=${encodeURIComponent(q)}" class="block mt-3 text-center text-xs text-brand-400 hover:text-brand-300 transition-colors py-2">
                        View all results for "${q}" →
                    </a>`;
                searchResults.classList.remove('hidden');
            }, 350);
        });

        // Close on outside click
        document.addEventListener('click', (e) => {
            if (!searchBar?.contains(e.target)) {
                searchResults.innerHTML = '';
                searchResults.classList.add('hidden');
            }
        });
    }

    // ============================================================
    // LOGOUT
    // ============================================================
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                await fetch('/cinflix/api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
            } catch {}
            localStorage.clear();
            window.location.href = '/cinflix/?page=login';
        });
    }

    // ============================================================
    // OFFLINE DETECTION
    // ============================================================
    function updateOnlineStatus() {
        if (!navigator.onLine) {
            UI_Toast.show('⚠️ You appear to be offline.', 'warning', 6000);
        }
    }
    window.addEventListener('offline', updateOnlineStatus);

    // ============================================================
    // PWA - Install Prompt
    // ============================================================
    let deferredPrompt;
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        // Show install banner after 5 seconds if not dismissed
        setTimeout(() => {
            if (!localStorage.getItem('cf_pwa_dismissed')) {
                showInstallBanner();
            }
        }, 5000);
    });

    function showInstallBanner() {
        const banner = document.createElement('div');
        banner.id    = 'installBanner';
        banner.className = 'fixed bottom-6 left-6 z-[99] glass rounded-2xl p-4 shadow-2xl flex items-center gap-3 max-w-sm animate-slide-up';
        banner.innerHTML = `
            <div class="w-10 h-10 bg-brand-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold">Install Cinflix</p>
                <p class="text-xs text-gray-400">Add to Home Screen for the best experience</p>
            </div>
            <div class="flex gap-2">
                <button id="installBtn" class="px-3 py-1.5 bg-brand-600 hover:bg-brand-500 rounded-lg text-xs font-medium transition-colors">Install</button>
                <button id="dismissInstall" class="p-1.5 hover:bg-white/10 rounded-lg text-gray-400 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>`;
        document.body.appendChild(banner);

        document.getElementById('installBtn').addEventListener('click', async () => {
            banner.remove();
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
        });

        document.getElementById('dismissInstall').addEventListener('click', () => {
            banner.remove();
            localStorage.setItem('cf_pwa_dismissed', '1');
        });
    }

    // ============================================================
    // SCROLL ROW - drag to scroll
    // ============================================================
    document.querySelectorAll('.scroll-row').forEach(row => {
        let isDown = false, startX, scrollLeft;
        row.addEventListener('mousedown',  (e) => { isDown = true; startX = e.pageX - row.offsetLeft; scrollLeft = row.scrollLeft; row.style.cursor = 'grabbing'; });
        row.addEventListener('mouseleave', () => { isDown = false; row.style.cursor = 'grab'; });
        row.addEventListener('mouseup',    () => { isDown = false; row.style.cursor = 'grab'; });
        row.addEventListener('mousemove',  (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x    = e.pageX - row.offsetLeft;
            const walk = (x - startX) * 2;
            row.scrollLeft = scrollLeft - walk;
        });
    });

    // ============================================================
    // ANALYTICS (simple page view tracking)
    // ============================================================
    function trackPageView() {
        const page = window.CINFLIX?.page || 'unknown';
        const views = JSON.parse(localStorage.getItem('cf_analytics') || '{}');
        views[page] = (views[page] || 0) + 1;
        views._lastVisit = new Date().toISOString();
        localStorage.setItem('cf_analytics', JSON.stringify(views));
    }
    trackPageView();

});
