/**
 * Cinflix - UI Module
 * Reusable UI components and helpers
 */

// ============================================================
// Media Card HTML Generator
// ============================================================
const UI = (() => {

    /**
     * Generate a media card HTML string
     * @param {Object} item  - Jellyfin item
     * @param {boolean} showProgress - Show progress bar (for continue watching)
     */
    function mediaCard(item, showProgress = false) {
        const id       = item.Id;
        const title    = item.Name || 'Unknown';
        const type     = item.Type === 'Series' ? 'Series' : item.Type || 'Movie';
        const year     = item.ProductionYear || '';
        const rating   = item.CommunityRating ? item.CommunityRating.toFixed(1) : '';
        const isFav    = item.UserData?.IsFavorite || false;
        const progress = showProgress ? (item.UserData?.PlayedPercentage || 0) : 0;
        const imgUrl   = `${window.CINFLIX.baseUrl}/Items/${id}/Images/Primary?width=300&quality=85&fillWidth=300`;

        return `
        <div class="media-card group relative flex-shrink-0 w-36 sm:w-44 lg:w-48 cursor-pointer animate-fade-in"
             data-item-id="${id}" data-item-type="${type}">

            <!-- Poster -->
            <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-dark-700 ring-1 ring-white/5 group-hover:ring-brand-500/50 transition-all duration-300 group-hover:scale-[1.03] group-hover:shadow-2xl group-hover:shadow-black/60">

                <!-- Image with lazy load -->
                <img
                    class="w-full h-full object-cover opacity-0 transition-opacity duration-500"
                    data-src="${imgUrl}"
                    alt="${title}"
                    loading="lazy"
                    onerror="this.closest('.media-card').querySelector('.poster-fallback').classList.remove('hidden'); this.classList.add('hidden');"
                />

                <!-- Fallback -->
                <div class="poster-fallback hidden absolute inset-0 flex flex-col items-center justify-center bg-dark-700 text-center p-3">
                    <span class="text-4xl mb-2">${type === 'Series' ? '📺' : '🎬'}</span>
                    <p class="text-xs text-gray-400 line-clamp-2">${title}</p>
                </div>

                <!-- Progress bar -->
                ${progress > 0 ? `
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-black/50">
                    <div class="h-full bg-brand-500" style="width:${progress}%"></div>
                </div>` : ''}

                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col justify-between p-3">
                    <!-- Favorite btn -->
                    <div class="flex justify-end">
                        <button class="fav-btn p-1.5 rounded-full bg-black/50 hover:bg-black/70 transition-colors"
                                data-id="${id}" data-fav="${isFav}"
                                onclick="event.stopPropagation(); handleFavToggle(this, null)">
                            <svg class="w-4 h-4 ${isFav ? 'text-brand-500 fill-brand-500' : 'text-white'}" fill="${isFav ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Play Button -->
                    <div class="flex flex-col gap-1.5">
                        <button class="play-btn w-full py-1.5 bg-brand-600 hover:bg-brand-500 rounded-lg text-xs font-semibold transition-colors flex items-center justify-center gap-1"
                                data-id="${id}" data-type="${type}">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
                            Play
                        </button>
                        <button class="info-btn w-full py-1.5 bg-white/10 hover:bg-white/20 rounded-lg text-xs font-medium transition-colors"
                                data-id="${id}">
                            More Info
                        </button>
                    </div>
                </div>

                <!-- Type Badge -->
                ${type === 'Series' ? `<div class="absolute top-2 left-2 px-1.5 py-0.5 bg-black/60 backdrop-blur-sm rounded text-[10px] text-gray-300 font-medium">TV</div>` : ''}
            </div>

            <!-- Title + Meta (below card) -->
            <div class="mt-2 px-0.5">
                <p class="text-xs font-medium text-gray-300 truncate group-hover:text-white transition-colors">${title}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    ${year ? `<span class="text-[10px] text-gray-600">${year}</span>` : ''}
                    ${rating ? `<span class="text-[10px] text-yellow-500">★ ${rating}</span>` : ''}
                </div>
            </div>
        </div>`;
    }

    /**
     * Bind click events to media cards inside a container
     */
    function bindCardEvents(container) {
        if (!container) return;

        // Lazy load images
        const imgs = container.querySelectorAll('img[data-src]');
        if ('IntersectionObserver' in window) {
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img  = entry.target;
                        img.src    = img.dataset.src;
                        img.onload = () => img.classList.remove('opacity-0');
                        obs.unobserve(img);
                    }
                });
            }, { rootMargin: '100px' });
            imgs.forEach(img => obs.observe(img));
        } else {
            imgs.forEach(img => {
                img.src    = img.dataset.src;
                img.onload = () => img.classList.remove('opacity-0');
            });
        }

        // Play buttons
        container.querySelectorAll('.play-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                playItem(btn.dataset.id, btn.dataset.type);
            });
        });

        // Info buttons → detail page
        container.querySelectorAll('.info-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (typeof openDetailModal === 'function') {
                    openDetailModal(btn.dataset.id);
                } else {
                    window.location.href = `/cinflix/?page=detail&id=${btn.dataset.id}`;
                }
            });
        });

        // Card click → detail page
        container.querySelectorAll('.media-card').forEach(card => {
            card.addEventListener('click', () => {
                window.location.href = `/cinflix/?page=detail&id=${card.dataset.itemId}`;
            });
        });
    }

    /**
     * Skeleton loader HTML
     */
    function skeletonCard() {
        return `<div class="card-skeleton flex-shrink-0 w-36 sm:w-44 lg:w-48"></div>`;
    }

    return { mediaCard, bindCardEvents, skeletonCard };
})();

// ============================================================
// GLOBAL: Toggle Favorite
// ============================================================
window.updateFavIcon = function(iconId, isFav) {
    const icon = document.getElementById(iconId);
    if (!icon) return;
    if (isFav) {
        icon.setAttribute('fill', 'currentColor');
        icon.classList.add('text-brand-500');
    } else {
        icon.setAttribute('fill', 'none');
        icon.classList.remove('text-brand-500');
    }
};

window.handleFavToggle = async function(btn, iconId) {
    const itemId = btn.dataset.id;
    const isFav  = btn.dataset.fav === 'true';
    const newFav = !isFav;

    btn.dataset.fav = newFav;

    // Update button icon if inline svg
    const svg = btn.querySelector('svg');
    if (svg) {
        svg.setAttribute('fill', newFav ? 'currentColor' : 'none');
        svg.classList.toggle('text-brand-500', newFav);
    }

    // Update external icon if id provided
    if (iconId) updateFavIcon(iconId, newFav);

    const result = await API.toggleFavorite(itemId, newFav);
    if (!result?.success) {
        // Revert on failure
        btn.dataset.fav = isFav;
        if (svg) {
            svg.setAttribute('fill', isFav ? 'currentColor' : 'none');
            svg.classList.toggle('text-brand-500', isFav);
        }
        if (iconId) updateFavIcon(iconId, isFav);
        UI_Toast.show('Failed to update favorites', 'error');
    } else {
        UI_Toast.show(newFav ? 'Added to My List ❤️' : 'Removed from My List', 'success');
    }
};

// ============================================================
// GLOBAL: Play Item
// ============================================================
window.playItem = function(itemId, type) {
    // Navigate to player page
    window.location.href = `/cinflix/?page=player&id=${itemId}`;
};

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================
const UI_Toast = (() => {
    function show(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const colors = {
            success: 'bg-green-900/80 border-green-500/30 text-green-300',
            error:   'bg-red-900/80 border-red-500/30 text-red-300',
            info:    'bg-dark-700/80 border-white/10 text-gray-300',
            warning: 'bg-yellow-900/80 border-yellow-500/30 text-yellow-300',
        };

        const toast = document.createElement('div');
        toast.className = `flex items-center gap-2 px-4 py-3 rounded-xl border backdrop-blur-sm text-sm shadow-lg animate-slide-up ${colors[type] || colors.info}`;
        toast.textContent = message;

        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    return { show };
})();
