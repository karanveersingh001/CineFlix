/**
 * Cinflix - Video Player Module
 * Full-featured HTML5 video player with Jellyfin integration
 */

const CinflixPlayer = (() => {
    let video, controls, progressContainer, progressBar, bufferedBar;
    let progressThumb, currentTimeEl, durationEl, playPauseBtn;
    let playIcon, pauseIcon, muteBtn, volumeSlider, fullscreenBtn;
    let fsIcon, fsExitIcon, speedBtn, speedMenu, clickArea;
    let videoLoading, errorOverlay, centerOverlay, playPauseAnim;
    let controls_timeout, progressDragging = false;
    let currentItem = null, progressInterval = null;
    let isFullscreen = false;

    function init(config) {
        const { url, itemId, item } = config;
        currentItem = item;

        // Cache DOM refs
        video              = document.getElementById('mainVideo');
        controls           = document.getElementById('controls');
        progressContainer  = document.getElementById('progressContainer');
        progressBar        = document.getElementById('progressBar');
        bufferedBar        = document.getElementById('bufferedBar');
        progressThumb      = document.getElementById('progressThumb');
        currentTimeEl      = document.getElementById('currentTime');
        durationEl         = document.getElementById('duration');
        playPauseBtn       = document.getElementById('playPauseBtn');
        playIcon           = document.getElementById('playIcon');
        pauseIcon          = document.getElementById('pauseIcon');
        muteBtn            = document.getElementById('muteBtn');
        volumeSlider       = document.getElementById('volumeSlider');
        fullscreenBtn      = document.getElementById('fullscreenBtn');
        fsIcon             = document.getElementById('fsIcon');
        fsExitIcon         = document.getElementById('fsExitIcon');
        speedBtn           = document.getElementById('speedBtn');
        speedMenu          = document.getElementById('speedMenu');
        clickArea          = document.getElementById('clickArea');
        videoLoading       = document.getElementById('videoLoading');
        errorOverlay       = document.getElementById('errorOverlay');
        centerOverlay      = document.getElementById('centerOverlay');
        playPauseAnim      = document.getElementById('playPauseAnim');

        // Set video source
        video.src = url;

        // Restore volume from localStorage
        const savedVol = parseFloat(localStorage.getItem('cf_volume') ?? '1');
        video.volume   = savedVol;
        if (volumeSlider) volumeSlider.value = savedVol;
        updateVolumeIcon(savedVol);

        // Restore position if available
        const savedPos = parseInt(localStorage.getItem(`cf_pos_${itemId}`) || '0');
        if (savedPos > 30) {
            video.currentTime = savedPos;
        }

        bindEvents(itemId);
        video.play().catch(() => {});
    }

    function bindEvents(itemId) {
        // ---- Video events ----
        video.addEventListener('loadedmetadata', () => {
            durationEl.textContent = formatTime(video.duration);
            videoLoading.classList.add('hidden');
        });

        video.addEventListener('waiting', () => videoLoading.classList.remove('hidden'));
        video.addEventListener('canplay',  () => videoLoading.classList.add('hidden'));

        video.addEventListener('error', () => {
            videoLoading.classList.add('hidden');
            errorOverlay.classList.remove('hidden');
            const code = video.error?.code;
            const msgs = { 1:'Playback aborted.', 2:'Network error.', 3:'Decoding error.', 4:'Format not supported.' };
            document.getElementById('errorMsg').textContent = msgs[code] || 'Unknown error.';
        });

        video.addEventListener('timeupdate', () => {
            if (!progressDragging && video.duration) {
                const pct = (video.currentTime / video.duration) * 100;
                progressBar.style.width          = pct + '%';
                progressThumb.style.left         = pct + '%';
                currentTimeEl.textContent        = formatTime(video.currentTime);
            }
            // Save local position every 5 seconds
            if (Math.floor(video.currentTime) % 5 === 0) {
                localStorage.setItem(`cf_pos_${itemId}`, Math.floor(video.currentTime));
            }
        });

        video.addEventListener('progress', () => {
            if (video.buffered.length > 0 && video.duration) {
                const buffered = video.buffered.end(video.buffered.length - 1);
                bufferedBar.style.width = (buffered / video.duration * 100) + '%';
            }
        });

        video.addEventListener('play',  () => { playIcon.classList.add('hidden'); pauseIcon.classList.remove('hidden'); });
        video.addEventListener('pause', () => { playIcon.classList.remove('hidden'); pauseIcon.classList.add('hidden'); });
        video.addEventListener('ended', () => { onEnded(itemId); });

        // ---- Controls ----
        playPauseBtn.addEventListener('click', togglePlay);

        document.getElementById('skipBackBtn').addEventListener('click', () => {
            video.currentTime = Math.max(0, video.currentTime - 10);
            flashCenter(false);
        });
        document.getElementById('skipFwdBtn').addEventListener('click', () => {
            video.currentTime = Math.min(video.duration, video.currentTime + 10);
            flashCenter(true);
        });

        muteBtn.addEventListener('click', () => {
            video.muted = !video.muted;
            updateVolumeIcon(video.muted ? 0 : video.volume);
        });

        volumeSlider.addEventListener('input', () => {
            video.volume   = parseFloat(volumeSlider.value);
            video.muted    = video.volume === 0;
            localStorage.setItem('cf_volume', volumeSlider.value);
            updateVolumeIcon(video.volume);
        });

        fullscreenBtn.addEventListener('click', toggleFullscreen);

        // Progress bar seeking
        progressContainer.addEventListener('mousedown', startSeek);
        progressContainer.addEventListener('touchstart', startSeek, { passive: true });
        document.addEventListener('mousemove', onSeek);
        document.addEventListener('touchmove', onSeek, { passive: true });
        document.addEventListener('mouseup',   endSeek);
        document.addEventListener('touchend',  endSeek);

        // Progress hover tooltip
        progressContainer.addEventListener('mousemove', (e) => {
            if (!video.duration) return;
            const rect = progressContainer.getBoundingClientRect();
            const pct  = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const tip  = document.getElementById('timeTooltip');
            tip.textContent = formatTime(pct * video.duration);
            tip.style.left  = (pct * 100) + '%';
            tip.classList.remove('hidden');
        });
        progressContainer.addEventListener('mouseleave', () => {
            document.getElementById('timeTooltip').classList.add('hidden');
        });

        // Click area (video) = toggle play
        clickArea.addEventListener('click', togglePlay);

        // Auto-hide controls
        const playerWrapper = document.getElementById('playerWrapper');
        playerWrapper.addEventListener('mousemove', showControls);
        playerWrapper.addEventListener('touchstart', showControls, { passive: true });
        showControls();

        // Keyboard shortcuts
        document.addEventListener('keydown', handleKeyboard);

        // Speed menu
        speedBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            speedMenu.classList.toggle('hidden');
        });
        document.querySelectorAll('.speed-opt').forEach(opt => {
            opt.addEventListener('click', () => {
                const speed      = parseFloat(opt.dataset.speed);
                video.playbackRate = speed;
                speedBtn.textContent = speed + '×';
                speedMenu.classList.add('hidden');
            });
        });
        document.addEventListener('click', () => speedMenu.classList.add('hidden'));

        // Fullscreen change detection
        document.addEventListener('fullscreenchange',       onFsChange);
        document.addEventListener('webkitfullscreenchange', onFsChange);

        // Report progress to Jellyfin every 15 seconds
        progressInterval = setInterval(() => reportProgress(itemId), 15000);
    }

    // ---- Play/Pause ----
    function togglePlay() {
        if (video.paused) {
            video.play();
            flashCenter(true, 'play');
        } else {
            video.pause();
            flashCenter(false, 'pause');
        }
    }

    function flashCenter(fwd, type) {
        const icon = document.getElementById('overlayPlayIcon');
        if (type === 'play') {
            icon.innerHTML = '<path d="M5 3l14 9-14 9V3z"/>';
        } else if (type === 'pause') {
            icon.innerHTML = '<path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>';
        } else {
            icon.innerHTML = fwd
                ? '<path d="M11.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4zm6.667 0a1 1 0 000-1.6l-5.333-4A1 1 0 0012 8v8a1 1 0 001.6.8l5.333-4z"/>'
                : '<path d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zm-6.666 0a1 1 0 000 1.6l5.333 4A1 1 0 0012 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>';
        }
        playPauseAnim.classList.remove('opacity-0');
        playPauseAnim.classList.add('opacity-100');
        setTimeout(() => {
            playPauseAnim.classList.remove('opacity-100');
            playPauseAnim.classList.add('opacity-0');
        }, 600);
    }

    // ---- Seeking ----
    function getSeekPos(e) {
        const rect  = progressContainer.getBoundingClientRect();
        const x     = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        return Math.max(0, Math.min(1, x / rect.width));
    }
    function startSeek(e) {
        progressDragging = true;
        const pct        = getSeekPos(e);
        progressBar.style.width  = (pct * 100) + '%';
        progressThumb.style.left = (pct * 100) + '%';
    }
    function onSeek(e) {
        if (!progressDragging) return;
        const pct = getSeekPos(e);
        progressBar.style.width  = (pct * 100) + '%';
        progressThumb.style.left = (pct * 100) + '%';
        currentTimeEl.textContent = formatTime(pct * video.duration);
    }
    function endSeek(e) {
        if (!progressDragging) return;
        progressDragging  = false;
        const pct         = getSeekPos(e);
        video.currentTime = pct * video.duration;
    }

    // ---- Volume icon ----
    function updateVolumeIcon(vol) {
        const volIcon  = document.getElementById('volumeIcon');
        const mutIcon  = document.getElementById('muteIcon');
        if (!volIcon || !mutIcon) return;
        if (vol === 0 || video?.muted) {
            volIcon.classList.add('hidden');
            mutIcon.classList.remove('hidden');
        } else {
            volIcon.classList.remove('hidden');
            mutIcon.classList.add('hidden');
        }
    }

    // ---- Fullscreen ----
    function toggleFullscreen() {
        const wrapper = document.getElementById('playerWrapper');
        if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            (wrapper.requestFullscreen || wrapper.webkitRequestFullscreen).call(wrapper);
        } else {
            (document.exitFullscreen || document.webkitExitFullscreen).call(document);
        }
    }
    function onFsChange() {
        isFullscreen = !!(document.fullscreenElement || document.webkitFullscreenElement);
        fsIcon.classList.toggle('hidden', isFullscreen);
        fsExitIcon.classList.toggle('hidden', !isFullscreen);
    }

    // ---- Auto-hide controls ----
    function showControls() {
        controls.style.opacity = '1';
        controls.style.pointerEvents = 'auto';
        clearTimeout(controls_timeout);
        controls_timeout = setTimeout(() => {
            if (!video?.paused) {
                controls.style.opacity       = '0';
                controls.style.pointerEvents = 'none';
            }
        }, 3000);
    }

    // ---- Keyboard shortcuts ----
    function handleKeyboard(e) {
        if (e.target.tagName === 'INPUT') return;
        switch (e.code) {
            case 'Space': case 'KeyK': e.preventDefault(); togglePlay(); break;
            case 'ArrowLeft':  e.preventDefault(); video.currentTime -= 10; flashCenter(false); break;
            case 'ArrowRight': e.preventDefault(); video.currentTime += 10; flashCenter(true); break;
            case 'ArrowUp':    e.preventDefault(); video.volume = Math.min(1, video.volume + 0.1); if(volumeSlider) volumeSlider.value = video.volume; updateVolumeIcon(video.volume); break;
            case 'ArrowDown':  e.preventDefault(); video.volume = Math.max(0, video.volume - 0.1); if(volumeSlider) volumeSlider.value = video.volume; updateVolumeIcon(video.volume); break;
            case 'KeyM':  e.preventDefault(); video.muted = !video.muted; updateVolumeIcon(video.muted ? 0 : video.volume); break;
            case 'KeyF':  e.preventDefault(); toggleFullscreen(); break;
        }
    }

    // ---- Report progress to Jellyfin ----
    async function reportProgress(itemId) {
        if (!video || video.paused || !video.currentTime) return;
        const ticks = Math.floor(video.currentTime * 10_000_000);
        try {
            await fetch(`/cinflix/api/media.php?action=progress`, {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json' },
                body:        JSON.stringify({ itemId, positionTicks: ticks }),
            });
        } catch {}
    }

    // ---- On ended ----
    async function onEnded(itemId) {
        clearInterval(progressInterval);
        localStorage.removeItem(`cf_pos_${itemId}`);
        try {
            await fetch(`/cinflix/api/media.php?action=mark_played`, {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json' },
                body:        JSON.stringify({ itemId }),
            });
        } catch {}
        // After 3 seconds, go back
        setTimeout(() => window.history.back(), 3000);
    }

    // ---- Format time ----
    function formatTime(secs) {
        if (!secs || isNaN(secs)) return '0:00';
        const h = Math.floor(secs / 3600);
        const m = Math.floor((secs % 3600) / 60);
        const s = Math.floor(secs % 60);
        if (h > 0) return `${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        return `${m}:${String(s).padStart(2,'0')}`;
    }

    return { init };
})();
