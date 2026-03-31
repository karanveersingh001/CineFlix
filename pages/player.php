<?php
/**
 * Cinflix - Player Page
 */
$itemId = sanitize($_GET['id'] ?? '');
if (empty($itemId)) redirect('/cinflix/?page=home');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Cinflix Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { colors: { brand: { 500: '#ff2626', 600: '#e80000' } } } }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/cinflix/assets/css/cinflix.css" />
    <style>
        body { font-family: 'DM Sans', sans-serif; background: #000; }
        #player-container:fullscreen { background: #000; }
        .player-btn { @apply p-2 hover:bg-white/20 rounded-lg transition-colors text-white; }
        video::-webkit-media-controls { display: none; }
        video::-webkit-media-controls-enclosure { display: none; }
    </style>
</head>
<body class="bg-black text-white h-screen overflow-hidden">

<div id="playerWrapper" class="relative w-full h-screen flex flex-col bg-black group">

    <!-- Video Element -->
    <div id="videoContainer" class="flex-1 relative flex items-center justify-center bg-black overflow-hidden">
        <video id="mainVideo" class="w-full h-full object-contain" preload="auto" crossorigin="anonymous">
            Your browser does not support HTML5 video.
        </video>

        <!-- Loading Spinner (inside video) -->
        <div id="videoLoading" class="absolute inset-0 flex items-center justify-center bg-black/60">
            <div class="flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full border-4 border-brand-700 border-t-brand-500 animate-spin"></div>
                <p class="text-sm text-gray-400">Loading video...</p>
            </div>
        </div>

        <!-- Center Play/Pause overlay -->
        <div id="centerOverlay" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div id="playPauseAnim" class="opacity-0 w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300">
                <svg id="overlayPlayIcon" class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
            </div>
        </div>

        <!-- Click area for play/pause -->
        <div id="clickArea" class="absolute inset-0 cursor-pointer"></div>

        <!-- Error overlay -->
        <div id="errorOverlay" class="hidden absolute inset-0 flex items-center justify-center bg-black/80">
            <div class="text-center max-w-sm">
                <p class="text-4xl mb-3">⚠️</p>
                <h3 class="font-semibold text-lg mb-2">Playback Error</h3>
                <p class="text-sm text-gray-400 mb-4" id="errorMsg">Unable to play this content.</p>
                <button onclick="window.history.back()" class="px-5 py-2.5 bg-white/10 hover:bg-white/20 rounded-xl text-sm transition-colors">
                    ← Go Back
                </button>
            </div>
        </div>
    </div>

    <!-- Controls Bar -->
    <div id="controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent p-4 transition-opacity duration-300">
        <!-- Progress Bar -->
        <div class="mb-3">
            <div id="progressContainer" class="relative h-1 bg-white/20 rounded-full cursor-pointer group/prog hover:h-2 transition-all">
                <div id="bufferedBar" class="absolute inset-y-0 left-0 bg-white/30 rounded-full"></div>
                <div id="progressBar" class="absolute inset-y-0 left-0 bg-brand-500 rounded-full"></div>
                <div id="progressThumb" class="hidden group-hover/prog:block absolute top-1/2 -translate-y-1/2 -translate-x-1/2 w-4 h-4 bg-brand-500 rounded-full shadow-lg ring-2 ring-white/30 cursor-grab" style="left:0%"></div>
            </div>
            <!-- Time tooltip -->
            <div id="timeTooltip" class="hidden absolute bottom-8 -translate-x-1/2 bg-black/80 text-xs px-2 py-1 rounded whitespace-nowrap"></div>
        </div>

        <!-- Controls Row -->
        <div class="flex items-center justify-between gap-3">
            <!-- Left -->
            <div class="flex items-center gap-2">
                <!-- Play/Pause -->
                <button id="playPauseBtn" class="player-btn" aria-label="Play/Pause">
                    <svg id="playIcon" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg>
                    <svg id="pauseIcon" class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                </button>

                <!-- Skip Back 10s -->
                <button id="skipBackBtn" class="player-btn" aria-label="Rewind 10s">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
                    </svg>
                </button>

                <!-- Skip Forward 10s -->
                <button id="skipFwdBtn" class="player-btn" aria-label="Skip 10s">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6L6.6 7.2A1 1 0 005 8v8a1 1 0 001.6.8l5.333-4z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.933 12.8a1 1 0 000-1.6l-5.333-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.333-4z"/>
                    </svg>
                </button>

                <!-- Volume -->
                <div class="flex items-center gap-1 group/vol">
                    <button id="muteBtn" class="player-btn" aria-label="Mute">
                        <svg id="volumeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m0 0l-4-4H4V12h4l4-4"/>
                        </svg>
                        <svg id="muteIcon" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                        </svg>
                    </button>
                    <input id="volumeSlider" type="range" min="0" max="1" step="0.05" value="1"
                        class="w-0 group-hover/vol:w-20 transition-all duration-200 accent-brand-500 cursor-pointer" />
                </div>

                <!-- Time Display -->
                <div class="text-xs text-gray-300 font-mono ml-1">
                    <span id="currentTime">0:00</span>
                    <span class="text-gray-600 mx-1">/</span>
                    <span id="duration">0:00</span>
                </div>
            </div>

            <!-- Center - Title -->
            <div class="hidden sm:block flex-1 text-center">
                <p id="playerTitle" class="text-sm font-medium text-gray-300 truncate"></p>
            </div>

            <!-- Right -->
            <div class="flex items-center gap-2">
                <!-- Playback Speed -->
                <div class="relative">
                    <button id="speedBtn" class="player-btn text-xs font-mono px-2">1×</button>
                    <div id="speedMenu" class="hidden absolute bottom-full right-0 mb-2 bg-black/90 border border-white/10 rounded-xl overflow-hidden min-w-[80px]">
                        <?php foreach ([0.5, 0.75, 1, 1.25, 1.5, 2] as $s): ?>
                        <button class="speed-opt w-full px-4 py-2 text-sm hover:bg-white/10 transition-colors text-left" data-speed="<?= $s ?>"><?= $s ?>×</button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Subtitles -->
                <button id="subBtn" class="player-btn hidden" aria-label="Subtitles">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                    </svg>
                </button>

                <!-- Fullscreen -->
                <button id="fullscreenBtn" class="player-btn" aria-label="Fullscreen">
                    <svg id="fsIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                    <svg id="fsExitIcon" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9V4.5M9 9H4.5M9 9L3.75 3.75M9 15v4.5M9 15H4.5M9 15l-5.25 5.25M15 9h4.5M15 9V4.5M15 9l5.25-5.25M15 15h4.5M15 15v4.5m0-4.5l5.25 5.25"/>
                    </svg>
                </button>

                <!-- Back button -->
                <button onclick="window.history.back()" class="player-btn" aria-label="Go back">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.CINFLIX = {
    userId:  '<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>',
    token:   '<?= htmlspecialchars($_SESSION['access_token'] ?? '') ?>',
    baseUrl: '<?= JELLYFIN_URL ?>',
    apiBase: '/cinflix/api',
    itemId:  '<?= htmlspecialchars($itemId) ?>',
};
</script>
<script src="/cinflix/assets/js/player.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const itemId = window.CINFLIX.itemId;

    // Fetch item details + stream URL
    const [itemRes, streamRes] = await Promise.all([
        fetch(`/cinflix/api/media.php?action=item&id=${itemId}`, { credentials: 'include' }),
        fetch(`/cinflix/api/media.php?action=stream_url&id=${itemId}`, { credentials: 'include' }),
    ]);

    const item      = await itemRes.json();
    const streamData = await streamRes.json();

    document.getElementById('playerTitle').textContent = item.Name || 'Playing...';
    document.title = `Playing: ${item.Name || 'Cinflix'} · Cinflix`;

    if (streamData.url) {
        CinflixPlayer.init({
            url:    streamData.url,
            itemId: itemId,
            item:   item,
        });
    } else {
        document.getElementById('videoLoading').classList.add('hidden');
        document.getElementById('errorOverlay').classList.remove('hidden');
        document.getElementById('errorMsg').textContent = 'Could not fetch stream URL.';
    }
});
</script>
</body>
</html>
