<?php
/**
 * CineFlix - Enhanced Player Page
 * Version: v1.1 – Added Quality, Audio, and External Playback controls
 */
$itemId = sanitize($_GET['id'] ?? '');
if (empty($itemId)) redirect('/cinflix/?page=home');
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>CineFlix Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <link rel="stylesheet" href="/cinflix/assets/css/cinflix.css" />
    <style>body { background: #000; font-family: 'DM Sans', sans-serif; overflow: hidden; }</style>
</head>
<body class="bg-black text-white h-screen">

<div id="playerWrapper" class="relative w-full h-screen flex flex-col group">
    <div id="videoContainer" class="flex-1 relative flex items-center justify-center bg-black overflow-hidden">
        <video id="mainVideo" class="w-full h-full object-contain" crossorigin="anonymous"></video>
        <div id="videoLoading" class="absolute inset-0 flex items-center justify-center bg-black/60">
            <div class="w-14 h-14 rounded-full border-4 border-brand-700 border-t-brand-500 animate-spin"></div>
        </div>
        <div id="clickArea" class="absolute inset-0 cursor-pointer"></div>
    </div>

    <div id="controls" class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/90 p-4 transition-opacity duration-300">
        <div id="progressContainer" class="relative h-1 bg-white/20 rounded-full mb-4 cursor-pointer hover:h-2 transition-all">
            <div id="progressBar" class="absolute inset-y-0 left-0 bg-brand-500 rounded-full"></div>
        </div>

        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <button id="playPauseBtn" class="p-2"><svg id="playIcon" class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M5 3l14 9-14 9V3z"/></svg></button>
                <div class="text-xs font-mono"><span id="currentTime">0:00</span> / <span id="duration">0:00</span></div>
            </div>

            <div class="flex items-center gap-3">
                <div class="relative group/menu">
                    <button id="qualityBtn" class="px-3 py-1 bg-white/10 rounded text-xs">Quality</button>
                    <div id="qualityMenu" class="hidden absolute bottom-full right-0 mb-2 w-32 glass rounded-lg overflow-hidden flex flex-col-reverse"></div>
                </div>

                <div class="relative group/menu">
                    <button id="audioBtn" class="px-3 py-1 bg-white/10 rounded text-xs">Audio</button>
                    <div id="audioMenu" class="hidden absolute bottom-full right-0 mb-2 w-40 glass rounded-lg overflow-hidden flex flex-col-reverse"></div>
                </div>

                <a id="externalPlayerBtn" href="#" target="_blank" class="px-3 py-1 bg-brand-600 rounded text-xs font-semibold hover:bg-brand-500">Download / External</a>

                <button id="fullscreenBtn" class="p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg></button>
                <button onclick="window.history.back()" class="p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
        </div>
    </div>
</div>

<script>
window.CINFLIX = {
    userId: '<?= htmlspecialchars($_SESSION['user_id'] ?? '') ?>',
    token: '<?= htmlspecialchars($_SESSION['access_token'] ?? '') ?>',
    baseUrl: '<?= JELLYFIN_URL ?>',
    itemId: '<?= htmlspecialchars($itemId) ?>',
};
</script>
<script src="/cinflix/assets/js/player.js"></script>
</body>
</html>
