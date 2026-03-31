<?php
/**
 * Cinflix - Item Detail Page
 */
$itemId = sanitize($_GET['id'] ?? '');
if (empty($itemId)) {
    redirect('/cinflix/?page=home');
}
?>
<div id="detailPage">
    <!-- Hero Backdrop -->
    <div id="detailBackdrop" class="relative h-[50vh] lg:h-[65vh] bg-cover bg-center bg-no-repeat overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-dark-950 via-dark-950/70 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-dark-950 via-transparent to-transparent"></div>
        <!-- Skeleton -->
        <div id="backdropSkeleton" class="absolute inset-0 skeleton-bg"></div>
    </div>

    <!-- Detail Content -->
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 -mt-32 relative z-10">
        <div class="flex flex-col lg:flex-row gap-8">

            <!-- Poster -->
            <div class="flex-shrink-0">
                <div id="detailPosterWrapper" class="w-40 sm:w-48 lg:w-56 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10">
                    <div id="detailPosterSkeleton" class="aspect-[2/3] skeleton-bg"></div>
                    <img id="detailPoster" class="hidden w-full aspect-[2/3] object-cover" alt="Poster" loading="lazy" />
                </div>
            </div>

            <!-- Info -->
            <div class="flex-1 min-w-0 pb-8">
                <!-- Badge + Title -->
                <div id="detailTypeBadge" class="inline-flex items-center gap-1.5 px-3 py-1 bg-brand-600/20 border border-brand-500/30 rounded-full text-brand-400 text-xs font-medium mb-3"></div>
                <h1 id="detailTitle" class="font-display text-3xl lg:text-5xl font-bold mb-2 leading-tight"></h1>
                <div id="detailTagline" class="text-gray-500 italic text-sm mb-4"></div>

                <!-- Meta Row -->
                <div id="detailMeta" class="flex flex-wrap items-center gap-3 text-sm text-gray-400 mb-5"></div>

                <!-- Rating + Genres -->
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <div id="detailRating" class="flex items-center gap-1.5 text-yellow-400 font-medium"></div>
                    <div id="detailGenres" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- Overview -->
                <p id="detailOverview" class="text-gray-300 text-sm lg:text-base leading-relaxed max-w-2xl mb-6"></p>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <button id="playBtn" class="btn-primary px-7 py-3 text-base font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
                        Play
                    </button>
                    <button id="trailerBtn" class="hidden px-7 py-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Trailer
                    </button>
                    <button id="favoriteBtn" class="p-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl transition-colors" data-id="<?= htmlspecialchars($itemId) ?>" data-fav="false">
                        <svg class="w-5 h-5" id="favIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Extra Info Grid -->
                <div id="detailExtras" class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-w-xl"></div>
            </div>
        </div>

        <!-- Seasons & Episodes (for Series) -->
        <div id="seriesSection" class="hidden mt-10">
            <h2 class="section-title mb-4">Episodes</h2>
            <!-- Season Tabs -->
            <div id="seasonTabs" class="flex flex-wrap gap-2 mb-6"></div>
            <!-- Episodes Grid -->
            <div id="episodesGrid" class="space-y-3"></div>
        </div>

        <!-- Cast -->
        <div id="castSection" class="hidden mt-10">
            <h2 class="section-title mb-4">Cast</h2>
            <div id="castGrid" class="scroll-row"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const itemId = '<?= htmlspecialchars($itemId) ?>';

    // Load item details
    const data = await API.get('item', { id: itemId });
    if (!data?.Id) {
        document.getElementById('detailPage').innerHTML = '<div class="text-center py-40 text-gray-600"><p class="text-4xl mb-3">😕</p><p>Item not found.</p></div>';
        return;
    }

    // Backdrop
    const backdropUrl = `${window.CINFLIX.baseUrl}/Items/${data.Id}/Images/Backdrop?width=1920&quality=85`;
    const backdrop    = document.getElementById('detailBackdrop');
    backdrop.style.backgroundImage = `url('${backdropUrl}')`;
    document.getElementById('backdropSkeleton').classList.add('hidden');

    // Poster
    const posterImg = document.getElementById('detailPoster');
    const posterUrl = `${window.CINFLIX.baseUrl}/Items/${data.Id}/Images/Primary?width=400&quality=90`;
    posterImg.src   = posterUrl;
    posterImg.onload = () => {
        document.getElementById('detailPosterSkeleton').classList.add('hidden');
        posterImg.classList.remove('hidden');
    };
    posterImg.onerror = () => {
        document.getElementById('detailPosterSkeleton').classList.add('hidden');
    };

    // Type badge
    const type = data.Type === 'Series' ? 'TV Show' : 'Movie';
    document.getElementById('detailTypeBadge').textContent = type;

    // Title + tagline
    document.getElementById('detailTitle').textContent   = data.Name || '';
    document.getElementById('detailTagline').textContent = data.Taglines?.[0] || '';

    // Meta
    const year    = data.ProductionYear || '';
    const rating  = data.OfficialRating || '';
    const runtime = data.RunTimeTicks ? Math.round(data.RunTimeTicks / 600000000) + 'm' : '';
    document.getElementById('detailMeta').innerHTML = [year, rating, runtime]
        .filter(Boolean)
        .map(v => `<span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full">${v}</span>`)
        .join('');

    // Rating
    const cr = data.CommunityRating;
    if (cr) {
        document.getElementById('detailRating').innerHTML =
            `<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>${cr.toFixed(1)} / 10`;
    }

    // Genres
    document.getElementById('detailGenres').innerHTML = (data.Genres || []).slice(0, 5).map(g =>
        `<span class="px-3 py-1 bg-white/5 border border-white/10 rounded-full text-xs text-gray-400">${g}</span>`
    ).join('');

    // Overview
    document.getElementById('detailOverview').textContent = data.Overview || 'No description available.';

    // Extras
    const extras = [];
    if (data.Studios?.[0]) extras.push({ label: 'Studio', value: data.Studios[0].Name });
    if (data.Status)        extras.push({ label: 'Status', value: data.Status });
    if (data.ProductionYear) extras.push({ label: 'Year',  value: data.ProductionYear });

    document.getElementById('detailExtras').innerHTML = extras.map(e =>
        `<div><p class="text-xs text-gray-600 uppercase tracking-wider mb-0.5">${e.label}</p><p class="text-sm font-medium text-gray-300">${e.value}</p></div>`
    ).join('');

    // Favorite
    const isFav    = data.UserData?.IsFavorite || false;
    const favBtn   = document.getElementById('favoriteBtn');
    favBtn.dataset.fav = isFav;
    updateFavIcon('favIcon', isFav);
    favBtn.addEventListener('click', () => handleFavToggle(favBtn, 'favIcon'));

    // Play button
    document.getElementById('playBtn').addEventListener('click', () => playItem(itemId, data.Type));

    // Series episodes
    if (data.Type === 'Series') {
        document.getElementById('playBtn').textContent = '▶ Play First Episode';
        document.getElementById('seriesSection').classList.remove('hidden');
        await loadSeasons(itemId);
    }

    // Cast
    const cast = (data.People || []).filter(p => p.Type === 'Actor').slice(0, 12);
    if (cast.length) {
        document.getElementById('castSection').classList.remove('hidden');
        document.getElementById('castGrid').innerHTML = cast.map(actor => `
            <div class="flex-shrink-0 w-28 text-center">
                <div class="w-20 h-20 mx-auto rounded-full overflow-hidden bg-dark-700 mb-2 ring-2 ring-white/10">
                    ${actor.PrimaryImageTag
                        ? `<img src="${window.CINFLIX.baseUrl}/Items/${actor.Id}/Images/Primary?width=120&quality=80" class="w-full h-full object-cover" loading="lazy" onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-2xl\'>👤</div>'" />`
                        : '<div class="w-full h-full flex items-center justify-center text-2xl">👤</div>'}
                </div>
                <p class="text-xs font-medium text-gray-300 truncate">${actor.Name}</p>
                <p class="text-xs text-gray-600 truncate">${actor.Role || ''}</p>
            </div>
        `).join('');
    }
});

async function loadSeasons(seriesId) {
    const data   = await API.get('seasons', { seriesId });
    const seasons = data?.Items || [];
    const tabsEl  = document.getElementById('seasonTabs');

    if (!seasons.length) {
        // Fallback: load all episodes directly
        await loadEpisodes(seriesId, '');
        return;
    }

    tabsEl.innerHTML = seasons.map((s, i) =>
        `<button class="season-tab px-4 py-2 rounded-xl text-sm font-medium transition-colors ${i === 0 ? 'bg-brand-600 text-white' : 'bg-white/5 hover:bg-white/10 text-gray-400'}" data-season-id="${s.Id}">${s.Name}</button>`
    ).join('');

    tabsEl.querySelectorAll('.season-tab').forEach(tab => {
        tab.addEventListener('click', async () => {
            tabsEl.querySelectorAll('.season-tab').forEach(t => {
                t.className = 'season-tab px-4 py-2 rounded-xl text-sm font-medium transition-colors bg-white/5 hover:bg-white/10 text-gray-400';
            });
            tab.className = 'season-tab px-4 py-2 rounded-xl text-sm font-medium transition-colors bg-brand-600 text-white';
            await loadEpisodes(seriesId, tab.dataset.seasonId);
        });
    });

    // Load first season
    await loadEpisodes(seriesId, seasons[0]?.Id || '');
}

async function loadEpisodes(seriesId, seasonId) {
    const grid = document.getElementById('episodesGrid');
    grid.innerHTML = '<div class="card-skeleton h-20 rounded-xl"></div>'.repeat(4);

    const data    = await API.get('episodes', { seriesId, seasonId });
    const episodes = data?.Items || [];

    if (!episodes.length) {
        grid.innerHTML = '<p class="text-gray-600 text-sm">No episodes found.</p>';
        return;
    }

    grid.innerHTML = episodes.map((ep, i) => {
        const thumbUrl = ep.ImageTags?.Primary
            ? `${window.CINFLIX.baseUrl}/Items/${ep.Id}/Images/Primary?width=300&quality=80`
            : (ep.BackdropImageTags?.length ? `${window.CINFLIX.baseUrl}/Items/${ep.Id}/Images/Backdrop?width=300&quality=80` : '');
        const runtime = ep.RunTimeTicks ? Math.round(ep.RunTimeTicks / 600000000) + 'm' : '';
        const progress = ep.UserData?.PlayedPercentage || 0;

        return `
        <div class="episode-card flex gap-4 p-3 rounded-xl bg-white/3 hover:bg-white/8 transition-colors cursor-pointer group" data-item-id="${ep.Id}">
            <div class="flex-shrink-0 w-40 sm:w-48 rounded-lg overflow-hidden bg-dark-700 relative aspect-video">
                ${thumbUrl ? `<img src="${thumbUrl}" class="w-full h-full object-cover" loading="lazy" />` : '<div class="w-full h-full flex items-center justify-center text-2xl">🎬</div>'}
                <!-- Progress bar -->
                ${progress > 0 ? `<div class="absolute bottom-0 left-0 right-0 h-1 bg-black/50"><div class="h-full bg-brand-500" style="width:${progress}%"></div></div>` : ''}
                <!-- Play overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center">
                        <svg class="w-5 h-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-0 py-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono text-gray-600">E${ep.IndexNumber || i + 1}</span>
                    <h4 class="text-sm font-semibold text-gray-200 truncate">${ep.Name || 'Episode ' + (i + 1)}</h4>
                    ${runtime ? `<span class="flex-shrink-0 text-xs text-gray-500">${runtime}</span>` : ''}
                </div>
                <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">${ep.Overview || ''}</p>
            </div>
        </div>
        `;
    }).join('');

    // Bind click to play
    grid.querySelectorAll('.episode-card').forEach(card => {
        card.addEventListener('click', () => {
            playItem(card.dataset.itemId, 'Episode');
        });
    });
}
</script>
