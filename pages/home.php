<?php
/**
 * Cinflix - Home Page
 */
?>

<!-- Hero Section -->
<section id="heroSection" class="relative min-h-[70vh] lg:min-h-[80vh] overflow-hidden">
    <!-- Skeleton loader -->
    <div id="heroSkeleton" class="absolute inset-0 skeleton-bg"></div>

    <!-- Hero Content (populated by JS) -->
    <div id="heroContent" class="hidden relative h-full min-h-[70vh] lg:min-h-[80vh]">
        <!-- Backdrop image -->
        <div id="heroBackdrop" class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-all duration-700">
            <div class="absolute inset-0 bg-gradient-to-r from-dark-950 via-dark-950/80 to-transparent"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-transparent to-dark-950/30"></div>
        </div>

        <!-- Hero Text -->
        <div class="relative z-10 flex flex-col justify-end h-full min-h-[70vh] lg:min-h-[80vh] pb-16 px-4 sm:px-6 lg:px-16 max-w-screen-2xl mx-auto">
            <!-- Badge -->
            <div id="heroBadge" class="inline-flex items-center gap-2 px-3 py-1.5 bg-brand-600/20 border border-brand-500/30 rounded-full text-brand-400 text-xs font-medium mb-4 w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                Featured
            </div>

            <h2 id="heroTitle" class="font-display text-4xl sm:text-5xl lg:text-7xl font-bold mb-3 max-w-2xl leading-tight"></h2>

            <div id="heroMeta" class="flex items-center gap-4 text-sm text-gray-400 mb-4"></div>

            <p id="heroOverview" class="text-gray-300 text-sm lg:text-base max-w-lg mb-6 line-clamp-3 leading-relaxed"></p>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <button id="heroPlay" class="btn-primary px-6 py-3 text-base font-semibold flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M5 3l14 9-14 9V3z"/>
                    </svg>
                    Play
                </button>
                <button id="heroInfo" class="px-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2 backdrop-blur-sm border border-white/10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    More Info
                </button>
                <button id="heroFavorite" class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors backdrop-blur-sm border border-white/10" data-id="" data-fav="false">
                    <svg class="w-5 h-5" id="heroFavIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
            </div>

            <!-- Carousel Dots -->
            <div id="heroDots" class="flex gap-2 mt-6"></div>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 relative z-10 space-y-12 pb-10">

    <!-- Continue Watching -->
    <section id="continueSection" class="hidden">
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-title">
                <span class="text-brand-500">▶</span> Continue Watching
            </h3>
        </div>
        <div id="continueGrid" class="scroll-row"></div>
    </section>

    <!-- Recently Added -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-title">Recently Added</h3>
            <a href="/cinflix/?page=movies" class="text-sm text-gray-500 hover:text-brand-400 transition-colors">See all →</a>
        </div>
        <div id="latestGrid" class="scroll-row">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="card-skeleton flex-shrink-0 w-36 sm:w-44 lg:w-48"></div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Movies -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-title">Movies</h3>
            <a href="/cinflix/?page=movies" class="text-sm text-gray-500 hover:text-brand-400 transition-colors">See all →</a>
        </div>
        <div id="moviesGrid" class="scroll-row">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="card-skeleton flex-shrink-0 w-36 sm:w-44 lg:w-48"></div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- TV Shows -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h3 class="section-title">TV Shows</h3>
            <a href="/cinflix/?page=shows" class="text-sm text-gray-500 hover:text-brand-400 transition-colors">See all →</a>
        </div>
        <div id="showsGrid" class="scroll-row">
            <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="card-skeleton flex-shrink-0 w-36 sm:w-44 lg:w-48"></div>
            <?php endfor; ?>
        </div>
    </section>

</div>

<!-- Item Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" id="modalBackdrop"></div>
    <div class="relative w-full max-w-3xl glass rounded-3xl overflow-hidden shadow-2xl animate-slide-up">
        <!-- Backdrop -->
        <div id="modalBackdropImg" class="h-48 sm:h-64 bg-cover bg-center relative">
            <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
            <!-- Close -->
            <button id="modalClose" class="absolute top-4 right-4 p-2 bg-black/50 rounded-full hover:bg-black/70 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-3">
                <div>
                    <h3 id="modalTitle" class="font-display text-2xl font-bold mb-1"></h3>
                    <div id="modalMeta" class="flex flex-wrap gap-3 text-sm text-gray-400"></div>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button id="modalPlay" class="btn-primary px-5 py-2.5 text-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
                        Play
                    </button>
                    <button id="modalFav" class="p-2.5 bg-white/10 hover:bg-white/20 rounded-xl transition-colors" data-id="" data-fav="false">
                        <svg class="w-5 h-5" id="modalFavIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
            <p id="modalOverview" class="text-gray-400 text-sm leading-relaxed line-clamp-4 mb-4"></p>
            <div id="modalGenres" class="flex flex-wrap gap-2"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let featuredItems = [];
    let currentHeroIdx = 0;
    let heroTimer;

    // ----------------------------------------
    // Load Hero / Featured
    // ----------------------------------------
    async function loadFeatured() {
        const data = await API.get('featured');
        if (!data?.Items?.length) return;
        featuredItems = data.Items;
        renderHero(0);
        startHeroCarousel();
        document.getElementById('heroSkeleton').classList.add('hidden');
        document.getElementById('heroContent').classList.remove('hidden');
    }

    function renderHero(idx) {
        const item  = featuredItems[idx];
        if (!item) return;

        const title    = item.Name || '';
        const year     = item.ProductionYear || '';
        const rating   = item.OfficialRating || '';
        const runtime  = item.RunTimeTicks ? Math.round(item.RunTimeTicks / 600000000) + 'm' : '';
        const overview = item.Overview || '';
        const type     = item.Type === 'Series' ? 'TV Show' : 'Movie';
        const isFav    = item.UserData?.IsFavorite || false;

        // Backdrop
        const backdropUrl = `${window.CINFLIX.baseUrl}/Items/${item.Id}/Images/Backdrop?width=1920&quality=85`;
        document.getElementById('heroBackdrop').style.backgroundImage = `url('${backdropUrl}')`;

        document.getElementById('heroTitle').textContent    = title;
        document.getElementById('heroOverview').textContent = overview;

        const metaEl = document.getElementById('heroMeta');
        metaEl.innerHTML = [
            year ? `<span>${year}</span>` : '',
            rating ? `<span class="px-2 py-0.5 border border-white/20 rounded text-xs">${rating}</span>` : '',
            runtime ? `<span>${runtime}</span>` : '',
            `<span class="px-2 py-0.5 bg-brand-800/40 text-brand-300 rounded text-xs">${type}</span>`,
        ].filter(Boolean).join('<span class="text-gray-600">·</span>');

        // Buttons
        document.getElementById('heroPlay').onclick = () => playItem(item.Id, item.Type);
        document.getElementById('heroInfo').onclick = () => openDetailModal(item.Id);
        const favBtn = document.getElementById('heroFavorite');
        favBtn.dataset.id  = item.Id;
        favBtn.dataset.fav = isFav;
        updateFavIcon('heroFavIcon', isFav);

        // Dots
        const dotsEl = document.getElementById('heroDots');
        dotsEl.innerHTML = featuredItems.map((_, i) =>
            `<button class="hero-dot ${i === idx ? 'active' : ''}" data-idx="${i}"></button>`
        ).join('');
        dotsEl.querySelectorAll('.hero-dot').forEach(dot => {
            dot.addEventListener('click', () => {
                clearInterval(heroTimer);
                currentHeroIdx = parseInt(dot.dataset.idx);
                renderHero(currentHeroIdx);
                startHeroCarousel();
            });
        });
    }

    function startHeroCarousel() {
        clearInterval(heroTimer);
        heroTimer = setInterval(() => {
            currentHeroIdx = (currentHeroIdx + 1) % featuredItems.length;
            renderHero(currentHeroIdx);
        }, 6000);
    }

    // ----------------------------------------
    // Load Media Rows
    // ----------------------------------------
    async function loadLatest() {
        const data = await API.get('latest');
        const grid = document.getElementById('latestGrid');
        if (!data?.length) { grid.innerHTML = '<p class="text-gray-600 text-sm">Nothing here yet.</p>'; return; }
        grid.innerHTML = data.map(item => UI.mediaCard(item)).join('');
        UI.bindCardEvents(grid);
    }

    async function loadMovies() {
        const data = await API.get('movies', { limit: 20 });
        const grid = document.getElementById('moviesGrid');
        if (!data?.Items?.length) { grid.innerHTML = '<p class="text-gray-600 text-sm">No movies found.</p>'; return; }
        grid.innerHTML = data.Items.map(item => UI.mediaCard(item)).join('');
        UI.bindCardEvents(grid);
    }

    async function loadShows() {
        const data = await API.get('shows', { limit: 20 });
        const grid = document.getElementById('showsGrid');
        if (!data?.Items?.length) { grid.innerHTML = '<p class="text-gray-600 text-sm">No shows found.</p>'; return; }
        grid.innerHTML = data.Items.map(item => UI.mediaCard(item)).join('');
        UI.bindCardEvents(grid);
    }

    async function loadContinue() {
        const data = await API.get('resume');
        if (!data?.Items?.length) return;
        const section = document.getElementById('continueSection');
        const grid    = document.getElementById('continueGrid');
        section.classList.remove('hidden');
        grid.innerHTML = data.Items.map(item => UI.mediaCard(item, true)).join('');
        UI.bindCardEvents(grid);
    }

    // ----------------------------------------
    // Detail Modal
    // ----------------------------------------
    window.openDetailModal = async (itemId) => {
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');

        const data = await API.get('item', { id: itemId });
        if (!data?.Id) return;

        const backdropUrl = `${window.CINFLIX.baseUrl}/Items/${data.Id}/Images/Backdrop?width=800&quality=85`;
        document.getElementById('modalBackdropImg').style.backgroundImage = `url('${backdropUrl}')`;
        document.getElementById('modalTitle').textContent    = data.Name || '';
        document.getElementById('modalOverview').textContent = data.Overview || 'No description available.';

        const year    = data.ProductionYear || '';
        const rating  = data.OfficialRating || '';
        const runtime = data.RunTimeTicks ? Math.round(data.RunTimeTicks / 600000000) + 'm' : '';
        const type    = data.Type === 'Series' ? 'TV Show' : 'Movie';

        document.getElementById('modalMeta').innerHTML = [
            year, rating, runtime,
            `<span class="px-2 py-0.5 bg-brand-800/40 text-brand-300 rounded-full text-xs">${type}</span>`,
        ].filter(Boolean).join(' · ');

        const genres = (data.Genres || []).slice(0, 4);
        document.getElementById('modalGenres').innerHTML = genres.map(g =>
            `<span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-xs text-gray-400">${g}</span>`
        ).join('');

        document.getElementById('modalPlay').onclick = () => {
            modal.classList.add('hidden');
            playItem(data.Id, data.Type);
        };

        const isFav = data.UserData?.IsFavorite || false;
        const favBtn = document.getElementById('modalFav');
        favBtn.dataset.id  = data.Id;
        favBtn.dataset.fav = isFav;
        updateFavIcon('modalFavIcon', isFav);
        favBtn.onclick = () => handleFavToggle(favBtn, 'modalFavIcon');
    };

    document.getElementById('modalClose').addEventListener('click', () => {
        document.getElementById('detailModal').classList.add('hidden');
    });
    document.getElementById('modalBackdrop').addEventListener('click', () => {
        document.getElementById('detailModal').classList.add('hidden');
    });

    // ----------------------------------------
    // Hero Favorite Button
    // ----------------------------------------
    document.getElementById('heroFavorite').addEventListener('click', function() {
        handleFavToggle(this, 'heroFavIcon');
    });

    // ----------------------------------------
    // INIT
    // ----------------------------------------
    loadFeatured();
    loadContinue();
    loadLatest();
    loadMovies();
    loadShows();
});
</script>
