<?php
/**
 * Cinflix - Layout Wrapper
 * HTML shell with nav, included on every page
 */
$currentPage = $page ?? 'home';
$userId      = $_SESSION['user_id'] ?? '';
$userName    = $_SESSION['user_name'] ?? '';
$token       = $_SESSION['access_token'] ?? '';
$isLoggedIn  = isAuthenticated();
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#0a0a0f" />
    <meta name="description" content="Cinflix – Stream movies and TV shows from your personal Jellyfin library." />
    <title>Cinflix<?php echo $currentPage !== 'home' ? ' · ' . ucfirst($currentPage) : ''; ?></title>

    <!-- PWA Manifest -->
    <link rel="manifest" href="/cinflix/manifest.json" />
    <link rel="apple-touch-icon" href="/cinflix/assets/images/icon-192.png" />

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fff0f0',
                            100: '#ffe0e0',
                            200: '#ffc5c5',
                            300: '#ff9999',
                            400: '#ff5c5c',
                            500: '#ff2626',
                            600: '#e80000',
                            700: '#c20000',
                            800: '#9e0303',
                            900: '#830808',
                        },
                        dark: {
                            950: '#03030a',
                            900: '#0a0a14',
                            800: '#111120',
                            700: '#1a1a2e',
                            600: '#22223b',
                        }
                    },
                    fontFamily: {
                        display: ['"Playfair Display"', 'serif'],
                        body:    ['"DM Sans"', 'sans-serif'],
                        mono:    ['"JetBrains Mono"', 'monospace'],
                    },
                    backgroundImage: {
                        'radial-dark': 'radial-gradient(ellipse at center, #1a1a2e 0%, #0a0a14 70%)',
                        'hero-glow':   'radial-gradient(ellipse 80% 60% at 50% 0%, rgba(255,38,38,0.15) 0%, transparent 70%)',
                    },
                    animation: {
                        'fade-in':     'fadeIn 0.4s ease forwards',
                        'slide-up':    'slideUp 0.4s ease forwards',
                        'skeleton':    'skeleton 1.5s ease-in-out infinite',
                        'spin-slow':   'spin 3s linear infinite',
                    },
                    keyframes: {
                        fadeIn:  { from: { opacity: 0 }, to: { opacity: 1 } },
                        slideUp: { from: { opacity: 0, transform: 'translateY(20px)' }, to: { opacity: 1, transform: 'translateY(0)' } },
                        skeleton: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%':      { backgroundPosition: '100% 50%' },
                        },
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/cinflix/assets/css/cinflix.css" />
</head>
<body class="bg-dark-950 text-gray-100 font-body antialiased min-h-screen overflow-x-hidden">

<?php if ($isLoggedIn && $currentPage !== 'login'): ?>
<!-- ============================================================
     NAVIGATION BAR
     ============================================================ -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

            <!-- Logo -->
            <a href="/cinflix/?page=home" class="flex items-center gap-2 group">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center group-hover:bg-brand-500 transition-colors">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-display text-2xl font-bold tracking-tight">
                    Cin<span class="text-brand-500">flix</span>
                </span>
            </a>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex items-center gap-6">
                <a href="/cinflix/?page=home" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
                <a href="/cinflix/?page=movies" class="nav-link <?= $currentPage === 'movies' ? 'active' : '' ?>">Movies</a>
                <a href="/cinflix/?page=shows" class="nav-link <?= $currentPage === 'shows' ? 'active' : '' ?>">TV Shows</a>
                <a href="/cinflix/?page=favorites" class="nav-link <?= $currentPage === 'favorites' ? 'active' : '' ?>">My List</a>
            </div>

            <!-- Right: Search + Profile -->
            <div class="flex items-center gap-3">
                <!-- Search Toggle -->
                <button id="searchToggle" class="p-2 rounded-lg hover:bg-white/10 transition-colors" aria-label="Search">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <!-- Profile Dropdown -->
                <div class="relative" id="profileMenu">
                    <button id="profileToggle" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/10 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-brand-700 flex items-center justify-center text-sm font-semibold">
                            <?= strtoupper(substr($userName, 0, 1)) ?>
                        </div>
                        <span class="hidden lg:block text-sm font-medium"><?= htmlspecialchars($userName) ?></span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-52 glass rounded-xl shadow-2xl overflow-hidden z-50">
                        <a href="/cinflix/?page=profile" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Profile
                        </a>
                        <a href="/cinflix/?page=favorites" class="flex items-center gap-3 px-4 py-3 hover:bg-white/10 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                            My List
                        </a>
                        <div class="border-t border-white/10"></div>
                        <button id="logoutBtn" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-white/10 transition-colors text-red-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </div>
                </div>

                <!-- Mobile Menu Toggle -->
                <button id="mobileMenuToggle" class="md:hidden p-2 rounded-lg hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Search Bar (slide down) -->
    <div id="searchBar" class="hidden border-t border-white/10 bg-dark-900/95 backdrop-blur-xl">
        <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="searchInput" type="text" placeholder="Search movies, shows, episodes..." autocomplete="off"
                    class="w-full bg-white/10 border border-white/20 rounded-xl pl-10 pr-4 py-3 text-sm placeholder-gray-500 focus:outline-none focus:border-brand-500 focus:bg-white/15 transition-all" />
            </div>
            <div id="searchResults" class="mt-3 hidden"></div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/10 bg-dark-900/95 backdrop-blur-xl">
        <div class="px-4 py-4 flex flex-col gap-1">
            <a href="/cinflix/?page=home" class="mobile-nav-link">🏠 Home</a>
            <a href="/cinflix/?page=movies" class="mobile-nav-link">🎬 Movies</a>
            <a href="/cinflix/?page=shows" class="mobile-nav-link">📺 TV Shows</a>
            <a href="/cinflix/?page=favorites" class="mobile-nav-link">❤️ My List</a>
            <a href="/cinflix/?page=profile" class="mobile-nav-link">👤 Profile</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main id="main-content" class="<?= $isLoggedIn && $currentPage !== 'login' ? 'pt-16 lg:pt-20' : '' ?>">
    <?php include $pageFile; ?>
</main>

<?php if ($isLoggedIn && $currentPage !== 'login' && $currentPage !== 'player'): ?>
<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="mt-20 border-t border-white/5 py-10">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-brand-600 rounded flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="font-display font-bold text-lg">Cin<span class="text-brand-500">flix</span></span>
            </div>
            <p class="text-gray-600 text-sm">Powered by Jellyfin · Personal Media Server</p>
            <p class="text-gray-700 text-xs">© <?= date('Y') ?> Cinflix</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<!-- Toast Notification Container -->
<div id="toastContainer" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2"></div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="hidden fixed inset-0 bg-dark-950/80 backdrop-blur-sm z-[200] flex items-center justify-center">
    <div class="flex flex-col items-center gap-4">
        <div class="w-12 h-12 rounded-full border-4 border-brand-700 border-t-brand-500 animate-spin"></div>
        <p class="text-gray-300 text-sm">Loading...</p>
    </div>
</div>

<!-- Session Data for JS -->
<script>
    window.CINFLIX = {
        userId:  '<?= htmlspecialchars($userId) ?>',
        token:   '<?= htmlspecialchars($token) ?>',
        baseUrl: '<?= JELLYFIN_URL ?>',
        apiBase: '/cinflix/api',
        page:    '<?= $currentPage ?>',
        itemId:  '<?= htmlspecialchars($id) ?>',
    };
</script>

<!-- Core JS -->
<script src="/cinflix/assets/js/api.js"></script>
<script src="/cinflix/assets/js/ui.js"></script>
<script src="/cinflix/assets/js/player.js"></script>
<script src="/cinflix/assets/js/app.js"></script>

<!-- PWA Service Worker -->
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/cinflix/sw.js').catch(() => {});
        });
    }
</script>
</body>
</html>
