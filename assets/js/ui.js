/**
 * CineFlix - UI Module
 * Version: v1.1 – Content label update to Web Series and poster token fix
 */

const UI = (() => {
    function mediaCard(item, showProgress = false) {
        const id = item.Id;
        const title = item.Name || 'Unknown';
        const type = item.Type === 'Series' ? 'Web Series' : 'Movie';
        const token = window.CINFLIX.token;
        const baseUrl = window.CINFLIX.baseUrl;
        
        // Correct usage of Primary API with token
        const imgUrl = `${baseUrl}/Items/${id}/Images/Primary?api_key=${token}&width=300`;

        return `
        <div class="media-card group relative flex-shrink-0 w-36 sm:w-44 lg:w-48 cursor-pointer"
             onclick="window.location.href='/cinflix/?page=detail&id=${id}'">
            <div class="relative aspect-[2/3] rounded-xl overflow-hidden bg-dark-700 ring-1 ring-white/5 group-hover:ring-brand-500/50">
                <img class="w-full h-full object-cover opacity-0 transition-opacity duration-500"
                     data-src="${imgUrl}" alt="${title}" loading="lazy"
                     onload="this.classList.remove('opacity-0')" />
                
                ${item.Type === 'Series' ? `<div class="absolute top-2 left-2 px-1.5 py-0.5 bg-black/60 rounded text-[10px] text-gray-300">WEB SERIES</div>` : ''}
            </div>
            <div class="mt-2">
                <p class="text-xs font-medium text-gray-300 truncate">${title}</p>
            </div>
        </div>`;
    }

    return { mediaCard, bindCardEvents: (container) => {
        const imgs = container.querySelectorAll('img[data-src]');
        imgs.forEach(img => { img.src = img.dataset.src; });
    }};
})();
