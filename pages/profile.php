<?php
/**
 * Cinflix - Profile Page
 */
$userName = sanitize($_SESSION['user_name'] ?? '');
?>
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Profile Header -->
    <div class="glass rounded-3xl p-6 sm:p-8 mb-8 flex flex-col sm:flex-row items-center sm:items-start gap-6">
        <!-- Avatar -->
        <div class="relative">
            <div id="avatarWrapper" class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-brand-800 flex items-center justify-center text-4xl sm:text-5xl font-bold ring-4 ring-brand-700/50 overflow-hidden">
                <span id="avatarInitial"><?= strtoupper(substr($userName, 0, 1)) ?></span>
                <img id="avatarImg" class="hidden absolute inset-0 w-full h-full object-cover" alt="Avatar" />
            </div>
        </div>

        <!-- Info -->
        <div class="flex-1 text-center sm:text-left">
            <h1 class="font-display text-3xl sm:text-4xl font-bold mb-1"><?= htmlspecialchars($userName) ?></h1>
            <p class="text-gray-500 text-sm mb-4" id="userEmail"></p>

            <!-- Stats -->
            <div class="flex flex-wrap justify-center sm:justify-start gap-6">
                <div class="text-center">
                    <p class="text-2xl font-bold text-brand-400" id="statMovies">–</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Movies</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-brand-400" id="statShows">–</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Shows</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-brand-400" id="statFavorites">–</p>
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Favorites</p>
                </div>
            </div>
        </div>

        <!-- Logout -->
        <button id="logoutBtnProfile"
            class="flex-shrink-0 px-5 py-2.5 bg-red-900/20 hover:bg-red-900/40 border border-red-500/20 text-red-400 rounded-xl text-sm font-medium transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Sign Out
        </button>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-white/10">
        <button class="profile-tab active" data-tab="favorites">❤️ My List</button>
        <button class="profile-tab" data-tab="continue">▶ Continue Watching</button>
    </div>

    <!-- Tab Content: Favorites -->
    <div id="tab-favorites" class="tab-panel">
        <div id="favoritesGrid" class="media-grid">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="card-skeleton"></div>
            <?php endfor; ?>
        </div>
        <div id="favEmpty" class="hidden text-center py-20 text-gray-600">
            <p class="text-4xl mb-3">❤️</p>
            <p>No favorites yet. Heart items to add them here.</p>
        </div>
    </div>

    <!-- Tab Content: Continue Watching -->
    <div id="tab-continue" class="tab-panel hidden">
        <div id="continueGrid" class="media-grid">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="card-skeleton"></div>
            <?php endfor; ?>
        </div>
        <div id="continueEmpty" class="hidden text-center py-20 text-gray-600">
            <p class="text-4xl mb-3">▶</p>
            <p>Nothing to continue watching yet.</p>
        </div>
    </div>
</div>

<style>
    .profile-tab {
        padding: 0.75rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .profile-tab.active {
        color: #fff;
        border-bottom-color: #e80000;
    }
    .profile-tab:hover:not(.active) { color: #d1d5db; }
</style>

<script>
document.addEventListener('DOMContentLoaded', async () => {

    // Load profile
    const profile = await API.get('profile');
    if (profile) {
        if (profile.PrimaryImageTag) {
            const imgUrl = `${window.CINFLIX.baseUrl}/Items/${profile.Id}/Images/Primary?width=200&quality=90`;
            const img    = document.getElementById('avatarImg');
            img.src      = imgUrl;
            img.onload   = () => {
                document.getElementById('avatarInitial').classList.add('hidden');
                img.classList.remove('hidden');
            };
        }
        if (profile.Configuration?.IsAdministrator !== undefined) {
            document.getElementById('userEmail').textContent = profile.Policy?.IsAdministrator ? 'Administrator' : 'User';
        }
    }

    // Load favorites
    const favData = await API.get('favorites');
    const favItems = favData?.Items || [];
    document.getElementById('statFavorites').textContent = favItems.length;

    const favGrid = document.getElementById('favoritesGrid');
    if (favItems.length === 0) {
        favGrid.innerHTML = '';
        document.getElementById('favEmpty').classList.remove('hidden');
    } else {
        favGrid.innerHTML = favItems.map(item => UI.mediaCard(item)).join('');
        UI.bindCardEvents(favGrid);
    }

    // Load continue watching
    const resumeData  = await API.get('resume');
    const resumeItems = resumeData?.Items || [];
    const contGrid    = document.getElementById('continueGrid');
    if (resumeItems.length === 0) {
        contGrid.innerHTML = '';
        document.getElementById('continueEmpty').classList.remove('hidden');
    } else {
        contGrid.innerHTML = resumeItems.map(item => UI.mediaCard(item, true)).join('');
        UI.bindCardEvents(contGrid);
    }

    // Load stats
    const [movRes, tvRes] = await Promise.all([
        fetch('/cinflix/api/media.php?action=movies&Limit=1', { credentials: 'include' }),
        fetch('/cinflix/api/media.php?action=shows&Limit=1', { credentials: 'include' }),
    ]);
    const movData = await movRes.json();
    const tvData  = await tvRes.json();
    document.getElementById('statMovies').textContent   = movData?.TotalRecordCount || 0;
    document.getElementById('statShows').textContent    = tvData?.TotalRecordCount || 0;

    // Tabs
    document.querySelectorAll('.profile-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
            tab.classList.add('active');
            document.getElementById('tab-' + tab.dataset.tab).classList.remove('hidden');
        });
    });

    // Logout
    document.getElementById('logoutBtnProfile').addEventListener('click', async () => {
        await fetch('/cinflix/api/auth.php?action=logout', { method: 'POST', credentials: 'include' });
        localStorage.clear();
        window.location.href = '/cinflix/?page=login';
    });
});
</script>
