<?php
/**
 * Cinflix - Movies Page
 */
?>
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Page Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl lg:text-4xl font-bold">Movies</h1>
            <p class="text-gray-500 text-sm mt-1" id="movieCount">Loading...</p>
        </div>
        <!-- Filters -->
        <div class="flex items-center gap-3">
            <select id="genreFilter" class="input-field text-sm py-2 px-3 w-auto">
                <option value="">All Genres</option>
            </select>
            <select id="sortFilter" class="input-field text-sm py-2 px-3 w-auto">
                <option value="DateCreated,Descending">Newest First</option>
                <option value="SortName,Ascending">A–Z</option>
                <option value="CommunityRating,Descending">Top Rated</option>
                <option value="Random,Descending">Random</option>
            </select>
        </div>
    </div>

    <!-- Grid -->
    <div id="moviesGrid" class="media-grid">
        <?php for ($i = 0; $i < 12; $i++): ?>
        <div class="card-skeleton"></div>
        <?php endfor; ?>
    </div>

    <!-- Load More -->
    <div class="text-center mt-10">
        <button id="loadMoreBtn" class="hidden px-8 py-3 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl text-sm font-medium transition-colors">
            Load More
        </button>
        <div id="loadMoreSpinner" class="hidden flex justify-center">
            <div class="w-8 h-8 rounded-full border-2 border-brand-700 border-t-brand-500 animate-spin"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let startIndex = 0;
    const limit    = 24;
    let totalItems = 0;
    let currentGenre = '';
    let currentSort  = 'DateCreated,Descending';

    async function loadGenres() {
        const data = await API.get('genres');
        const sel  = document.getElementById('genreFilter');
        (data?.Items || []).forEach(g => {
            const opt = document.createElement('option');
            opt.value       = g.Id;
            opt.textContent = g.Name;
            sel.appendChild(opt);
        });
    }

    async function loadMovies(reset = false) {
        if (reset) { startIndex = 0; }
        document.getElementById('loadMoreSpinner').classList.remove('hidden');
        document.getElementById('loadMoreBtn').classList.add('hidden');

        const [sortBy, sortOrder] = currentSort.split(',');
        const params = {
            limit:   limit,
            offset:  startIndex,
            genreIds: currentGenre,
        };
        // Pass sort to backend (custom endpoint logic)
        const url = `/cinflix/api/media.php?action=movies&Limit=${limit}&StartIndex=${startIndex}&SortBy=${sortBy}&SortOrder=${sortOrder}${currentGenre ? '&GenreIds=' + currentGenre : ''}`;
        const res  = await fetch(url, { credentials: 'include' });
        const data = await res.json();

        const grid = document.getElementById('moviesGrid');
        if (reset) grid.innerHTML = '';

        const items = data?.Items || [];
        totalItems  = data?.TotalRecordCount || 0;

        if (items.length === 0 && reset) {
            grid.innerHTML = '<div class="col-span-full text-center py-20 text-gray-600"><p class="text-4xl mb-3">🎬</p><p>No movies found.</p></div>';
        } else {
            grid.insertAdjacentHTML('beforeend', items.map(item => UI.mediaCard(item)).join(''));
            UI.bindCardEvents(grid);
        }

        startIndex += items.length;
        document.getElementById('movieCount').textContent = `${totalItems} movies`;

        document.getElementById('loadMoreSpinner').classList.add('hidden');
        if (startIndex < totalItems) {
            document.getElementById('loadMoreBtn').classList.remove('hidden');
        }
    }

    document.getElementById('loadMoreBtn').addEventListener('click', () => loadMovies(false));

    document.getElementById('genreFilter').addEventListener('change', function() {
        currentGenre = this.value;
        loadMovies(true);
    });

    document.getElementById('sortFilter').addEventListener('change', function() {
        currentSort = this.value;
        loadMovies(true);
    });

    loadGenres();
    loadMovies(true);
});
</script>
