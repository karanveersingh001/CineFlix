<?php
/**
 * CineFlix - Layout Wrapper
 * Version: v1.1 – Rebranding and white-label update
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
    <meta name="description" content="CineFlix – Stream movies and web series from your personal library." />
    <title>CineFlix<?php echo $currentPage !== 'home' ? ' · ' . ucfirst($currentPage) : ''; ?></title>
    <link rel="manifest" href="/cinflix/manifest.json" />
    <link rel="apple-touch-icon" href="/cinflix/assets/images/icon-192.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { 500: '#ff2626', 600: '#e80000' },
                        dark: { 950: '#03030a', 900: '#0a0a14', 800: '#111120', 700: '#1a1a2e', 600: '#22223b' }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/cinflix/assets/css/cinflix.css" />
</head>
<body class="bg-dark-950 text-gray-100 font-body antialiased min-h-screen">

<?php if ($isLoggedIn && $currentPage !== 'login'): ?>
<nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <a href="/cinflix/?page=home" class="flex items-center gap-2 group">
                <div class="w-8 h-8 bg-brand-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span class="font-display text-2xl font-bold tracking-tight">Cine<span class="text-brand-500">Flix</span></span>
            </a>

            <div class="hidden md:flex items-center gap-6">
                <a href="/cinflix/?page=home" class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>">Home</a>
                <a href="/cinflix/?page=movies" class="nav-link <?= $currentPage === 'movies' ? 'active' : '' ?>">Movies</a>
                <a href="/cinflix/?page=shows" class="nav-link <?= $currentPage === 'shows' ? 'active' : '' ?>">Web Series</a>
                <a href="/cinflix/?page=favorites" class="nav-link <?= $currentPage === 'favorites' ? 'active' : '' ?>">My List</a>
            </div>

            <div class="flex items-center gap-3">
                <button id="searchToggle" class="p-2 rounded-lg hover:bg-white/10"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></button>
                <div class="relative" id="profileMenu">
                    <button id="profileToggle" class="flex items-center gap-2 rounded-lg px-3 py-2 hover:bg-white/10">
                        <div class="w-7 h-7 rounded-full bg-brand-700 flex items-center justify-center text-sm font-semibold"><?= strtoupper(substr($userName, 0, 1)) ?></div>
                    </button>
                    <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-52 glass rounded-xl shadow-2xl z-50">
                        <a href="/cinflix/?page=profile" class="block px-4 py-3 hover:bg-white/10">Profile</a>
                        <button id="logoutBtn" class="w-full text-left px-4 py-3 hover:bg-white/10 text-red-400">Sign Out</button>
                    </div>
                </div>
                <button id="mobileMenuToggle" class="md:hidden p-2 rounded-lg hover:bg-white/10"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            </div>
        </div>
    </div>
    <div id="searchBar" class="hidden border-t border-white/10 bg-dark-900/95 backdrop-blur-xl">
        <div class="max-w-screen-2xl mx-auto px-4 py-3">
            <input id="searchInput" type="text" placeholder="Search movies, web series..." class="w-full bg-white/10 border border-white/20 rounded-xl pl-4 pr-4 py-3 text-sm focus:outline-none" />
            <div id="searchResults" class="mt-3 hidden"></div>
        </div>
    </div>
    <div id="mobileMenu" class="hidden md:hidden border-t border-white/10 bg-dark-900/95 backdrop-blur-xl">
        <div class="px-4 py-4 flex flex-col gap-1">
            <a href="/cinflix/?page=home" class="mobile-nav-link">🏠 Home</a>
            <a href="/cinflix/?page=movies" class="mobile-nav-link">🎬 Movies</a>
            <a href="/cinflix/?page=shows" class="mobile-nav-link">📺 Web Series</a>
            <a href="/cinflix/?page=favorites" class="mobile-nav-link">❤️ My List</a>
        </div>
    </div>
</nav>
<?php endif; ?>

<main id="main-content" class="<?= $isLoggedIn && $currentPage !== 'login' ? 'pt-16 lg:pt-20' : '' ?>">
    <?php include $pageFile; ?>
</main>

<?php if ($isLoggedIn && $currentPage !== 'login' && $currentPage !== 'player'): ?>
<footer class="mt-20 border-t border-white/5 py-10">
    <div class="max-w-screen-2xl mx-auto px-4 text-center">
        <p class="text-gray-600 text-sm">© <?= date('Y') ?> CineFlix Personal Cinema</p>
    </div>
</footer>
<?php endif; ?>

<div id="toastContainer" class="fixed bottom-6 right-6 z-[100] flex flex-col gap-2"></div>
<script>
    window.CINFLIX = {
        userId:  '<?= htmlspecialchars($userId) ?>',
        token:   '<?= htmlspecialchars($token) ?>',
        baseUrl: '<?= JELLYFIN_URL ?>',
        apiBase: '/cinflix/api',
        page:    '<?= $currentPage ?>',
    };
</script>
<script src="/cinflix/assets/js/api.js"></script>
<script src="/cinflix/assets/js/ui.js"></script>
<script src="/cinflix/assets/js/player.js"></script>
<script src="/cinflix/assets/js/app.js"></script>
</body>
</html>
