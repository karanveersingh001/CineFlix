/**
 * CineFlix - Enhanced Player Module
 * Version: v1.1 – Implementation of HLS adaptive streaming and multi-track audio
 */

const CineFlixPlayer = (() => {
    let video, hls, currentItemId;
    const token = window.CINFLIX.token;
    const baseUrl = window.CINFLIX.baseUrl;

    async function init(itemId) {
        currentItemId = itemId;
        video = document.getElementById('mainVideo');
        const externalBtn = document.getElementById('externalPlayerBtn');

        // External Player URL: Static stream with token
        externalBtn.href = `${baseUrl}/Videos/${itemId}/stream?Static=true&api_key=${token}`;

        // Adaptive Streaming URL: master.m3u8
        const manifestUrl = `${baseUrl}/Videos/${itemId}/master.m3u8?api_key=${token}&DeviceId=cineflix-web-001`;

        if (Hls.isSupported()) {
            hls = new Hls();
            hls.loadSource(manifestUrl);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                setupQualityMenu();
                video.play();
            });
        } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
            video.src = manifestUrl;
            video.addEventListener('loadedmetadata', () => video.play());
        }

        setupAudioMenu(itemId);
    }

    function setupQualityMenu() {
        const menu = document.getElementById('qualityMenu');
        const levels = hls.levels;
        
        let html = `<button class="w-full px-4 py-2 text-xs hover:bg-white/10 text-left" onclick="CineFlixPlayer.setQuality(-1)">Auto</button>`;
        levels.forEach((level, index) => {
            html += `<button class="w-full px-4 py-2 text-xs hover:bg-white/10 text-left" onclick="CineFlixPlayer.setQuality(${index})">${level.height}p</button>`;
        });
        menu.innerHTML = html;
        document.getElementById('qualityBtn').onclick = () => menu.classList.toggle('hidden');
    }

    async function setupAudioMenu(itemId) {
        const menu = document.getElementById('audioMenu');
        // Fetch media streams to identify audio tracks
        const res = await fetch(`${baseUrl}/Items/${itemId}/PlaybackInfo?api_key=${token}`, { method: 'POST' });
        const data = await res.json();
        const streams = data.MediaSources?.[0]?.MediaStreams || [];
        const audioTracks = streams.filter(s => s.Type === 'Audio');

        let html = '';
        audioTracks.forEach(track => {
            const label = track.Language ? track.Language.toUpperCase() : 'Unknown';
            html += `<button class="w-full px-4 py-2 text-xs hover:bg-white/10 text-left" onclick="CineFlixPlayer.setAudio(${track.Index})">${label} (${track.Codec})</button>`;
        });
        menu.innerHTML = html;
        document.getElementById('audioBtn').onclick = () => menu.classList.toggle('hidden');
    }

    window.CineFlixPlayer = {
        setQuality: (idx) => { 
            hls.currentLevel = idx; 
            document.getElementById('qualityMenu').classList.add('hidden');
        },
        setAudio: (index) => {
            // Reload stream with specific AudioStreamIndex
            const newUrl = `${baseUrl}/Videos/${currentItemId}/master.m3u8?api_key=${token}&AudioStreamIndex=${index}`;
            hls.loadSource(newUrl);
            video.play();
            document.getElementById('audioMenu').classList.add('hidden');
        }
    };

    init(window.CINFLIX.itemId);
})();
