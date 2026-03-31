<?php
/**
 * Cinflix - Favorites / My List Page
 */
?>
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="font-display text-3xl lg:text-4xl font-bold mb-1">My List</h1>
        <p class="text-gray-500 text-sm" id="favCount">Loading...</p>
    </div>

    <div id="favoritesGrid" class="media-grid">
        <?php for ($i = 0; $i < 8; $i++): ?>
        <div class="card-skeleton"></div>
        <?php endfor; ?>
    </div>

    <div id="emptyFav" class="hidden text-center py-32">
        <p class="text-6xl mb-4">❤️</p>
        <h3 class="text-xl font-semibold text-gray-400 mb-2">Your list is empty</h3>
        <p class="text-gray-600 text-sm mb-6">Heart movies and shows to save them here</p>
        <a href="/cinflix/?page=home" class="btn-primary px-6 py-3 text-sm">Browse Content</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const data  = await API.get('favorites');
    const items = data?.Items || [];
    const grid  = document.getElementById('favoritesGrid');

    document.getElementById('favCount').textContent = `${data?.TotalRecordCount || 0} items`;

    if (!items.length) {
        grid.innerHTML = '';
        document.getElementById('emptyFav').classList.remove('hidden');
        return;
    }

    grid.innerHTML = items.map(item => UI.mediaCard(item)).join('');
    UI.bindCardEvents(grid);
});
</script>
