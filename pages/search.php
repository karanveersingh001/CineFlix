<?php
/**
 * Cinflix - Search Page
 */
$query = sanitize($_GET['q'] ?? '');
?>
<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Search Header -->
    <div class="mb-8">
        <h1 class="font-display text-3xl lg:text-4xl font-bold mb-4">Search</h1>
        <div class="relative max-w-2xl">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="pageSearch" type="text" value="<?= htmlspecialchars($query) ?>" placeholder="Search for movies, shows, episodes..."
                class="input-field pl-12 py-4 text-base w-full"
                autofocus autocomplete="off" />
            <button id="clearSearch" class="<?= $query ? '' : 'hidden' ?> absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Results Section -->
    <div id="searchSection">
        <?php if ($query): ?>
        <!-- Initial server-triggered search will be done via JS -->
        <div id="resultsHeader" class="mb-4">
            <p class="text-gray-500 text-sm">Searching for "<span class="text-gray-300"><?= htmlspecialchars($query) ?></span>"...</p>
        </div>
        <?php else: ?>
        <!-- Empty state -->
        <div id="emptyState" class="text-center py-24">
            <p class="text-6xl mb-4">🔍</p>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">Search your library</h3>
            <p class="text-gray-600 text-sm">Find movies, TV shows, and episodes</p>
        </div>
        <?php endif; ?>

        <!-- Results Grid -->
        <div id="searchResultsGrid" class="media-grid hidden"></div>

        <!-- No results -->
        <div id="noResults" class="hidden text-center py-24">
            <p class="text-6xl mb-4">😕</p>
            <h3 class="text-xl font-semibold text-gray-400 mb-2">No results found</h3>
            <p class="text-gray-600 text-sm">Try a different search term</p>
        </div>

        <!-- Spinner -->
        <div id="searchSpinner" class="hidden flex justify-center py-16">
            <div class="w-10 h-10 rounded-full border-2 border-brand-700 border-t-brand-500 animate-spin"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const input    = document.getElementById('pageSearch');
    const clearBtn = document.getElementById('clearSearch');
    const grid     = document.getElementById('searchResultsGrid');
    const noRes    = document.getElementById('noResults');
    const spinner  = document.getElementById('searchSpinner');
    const empty    = document.getElementById('emptyState');
    const header   = document.getElementById('resultsHeader');

    let debounceTimer;

    function setLoading(loading) {
        spinner.classList.toggle('hidden', !loading);
        grid.classList.add('hidden');
        noRes.classList.add('hidden');
        if (empty) empty.classList.add('hidden');
    }

    async function doSearch(q) {
        if (!q.trim()) {
            grid.classList.add('hidden');
            noRes.classList.add('hidden');
            spinner.classList.add('hidden');
            if (empty) empty.classList.remove('hidden');
            if (header) header.classList.add('hidden');
            return;
        }

        setLoading(true);
        if (header) {
            header.classList.remove('hidden');
            header.innerHTML = `<p class="text-gray-500 text-sm">Results for "<span class="text-gray-300">${q}</span>"</p>`;
        }

        // Update URL without reload
        const url = new URL(window.location);
        url.searchParams.set('q', q);
        window.history.replaceState({}, '', url);

        const data  = await API.get('search', { q });
        const items = data?.Items || [];

        spinner.classList.add('hidden');

        if (items.length === 0) {
            noRes.classList.remove('hidden');
            return;
        }

        grid.innerHTML = items.map(item => UI.mediaCard(item)).join('');
        UI.bindCardEvents(grid);
        grid.classList.remove('hidden');
    }

    input.addEventListener('input', () => {
        clearBtn.classList.toggle('hidden', !input.value);
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => doSearch(input.value), 400);
    });

    clearBtn.addEventListener('click', () => {
        input.value = '';
        clearBtn.classList.add('hidden');
        doSearch('');
        input.focus();
    });

    // If there's an initial query, search it
    const initialQ = '<?= addslashes($query) ?>';
    if (initialQ) doSearch(initialQ);
});
</script>
