<?php
/**
 * CineFlix - Item Detail Page
 * Version: v1.1 – Fixed poster loading with token and added fallback
 */
$itemId = sanitize($_GET['id'] ?? '');
if (empty($itemId)) redirect('/cinflix/?page=home');
?>
<div id="detailPage">
    <div id="detailBackdrop" class="relative h-[50vh] lg:h-[65vh] bg-cover bg-center overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-dark-950 via-dark-950/70 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-dark-950 to-transparent"></div>
        <div id="backdropSkeleton" class="absolute inset-0 skeleton-bg"></div>
    </div>

    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 -mt-32 relative z-10">
        <div class="flex flex-col lg:flex-row gap-8">
            <div class="flex-shrink-0">
                <div id="detailPosterWrapper" class="w-40 sm:w-48 lg:w-56 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10 bg-dark-700">
                    <img id="detailPoster" class="w-full aspect-[2/3] object-cover hidden" alt="Poster" />
                    <div id="posterFallback" class="hidden aspect-[2/3] flex flex-col items-center justify-center text-center p-4">
                        <span id="fallbackIcon" class="text-4xl mb-2">🎬</span>
                        <p id="fallbackTitle" class="text-xs text-gray-500"></p>
                    </div>
                </div>
            </div>

            <div class="flex-1 min-w-0 pb-8">
                <div id="detailTypeBadge" class="inline-flex items-center gap-1.5 px-3 py-1 bg-brand-600/20 border border-brand-500/30 rounded-full text-brand-400 text-xs font-medium mb-3"></div>
                <h1 id="detailTitle" class="font-display text-3xl lg:text-5xl font-bold mb-2 leading-tight"></h1>
                <p id="detailOverview" class="text-gray-300 text-sm lg:text-base max-w-2xl mb-6"></p>

                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <button id="playBtn" class="btn-primary px-7 py-3 text-base font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg> Play
                    </button>
                    <button id="favoriteBtn" class="p-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl">
                        <svg class="w-5 h-5" id="favIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="seriesSection" class="hidden mt-10">
            <h2 class="section-title mb-4">Episodes</h2>
            <div id="seasonTabs" class="flex flex-wrap gap-2 mb-6"></div>
            <div id="episodesGrid" class="space-y-3"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const itemId = '<?= htmlspecialchars($itemId) ?>';
    const data = await API.get('item', { id: itemId });
    if (!data?.Id) return;

    const token = window.CINFLIX.token;
    const baseUrl = window.CINFLIX.baseUrl;

    // Backdrop
    document.getElementById('detailBackdrop').style.backgroundImage = `url('${baseUrl}/Items/${data.Id}/Images/Backdrop?api_key=${token}')`;
    document.getElementById('backdropSkeleton').classList.add('hidden');

    // Poster Fix: Correct usage of Primary API with token
    const posterImg = document.getElementById('detailPoster');
    const fallback = document.getElementById('posterFallback');
    posterImg.src = `${baseUrl}/Items/${data.Id}/Images/Primary?api_key=${token}&width=400`;
    
    posterImg.onload = () => posterImg.classList.remove('hidden');
    posterImg.onerror = () => {
        posterImg.classList.add('hidden');
        fallback.classList.remove('hidden');
        document.getElementById('fallbackIcon').textContent = data.Type === 'Series' ? '📺' : '🎬';
        document.getElementById('fallbackTitle').textContent = data.Name;
    };

    document.getElementById('detailTypeBadge').textContent = data.Type === 'Series' ? 'Web Series' : 'Movie';
    document.getElementById('detailTitle').textContent = data.Name;
    document.getElementById('detailOverview').textContent = data.Overview || 'No description available.';
    document.getElementById('playBtn').addEventListener('click', () => playItem(itemId, data.Type));

    if (data.Type === 'Series') {
        document.getElementById('seriesSection').classList.remove('hidden');
        loadSeasons(itemId);
    }
});
</script>
