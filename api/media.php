<?php
/**
 * Cinflix — Media API (Browser-Proxy Architecture)
 *
 * Hostinger cannot reach Jellyfin directly (private IP / firewall).
 * This endpoint acts as a PROXY: it receives requests from the browser
 * and forwards them to Jellyfin using the stored session token.
 *
 * If the direct cURL also fails (timeout), we return the Jellyfin URL
 * and let the browser call it directly via the client-side API module.
 */

require_once __DIR__ . '/../config.php';

// CORS
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

if (!isAuthenticated()) {
    jsonResponse(['error' => 'Unauthorized', 'redirect' => '/cinflix/?page=login'], 401);
}

$userId = $_SESSION['user_id'];
$token  = $_SESSION['access_token'];
$action = sanitize($_GET['action'] ?? '');

// ── Build Jellyfin base URL and auth header ──────────────────
$jelly     = JELLYFIN_URL;
$authHdr   = 'MediaBrowser Client="Cinflix Web", Device="Browser", DeviceId="cinflix-browser-001", Version="1.0.0", Token="' . $token . '"';

/**
 * Make a proxied request to Jellyfin.
 * Returns ['ok'=>bool, 'data'=>array, 'code'=>int]
 */
function jellyProxy(string $path, array $params = [], string $method = 'GET', array $body = []): array {
    global $jelly, $authHdr;

    $url = $jelly . $path;
    if ($params) $url .= '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 12,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . $authHdr,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body ? json_encode($body) : '');
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }

    $raw   = curl_exec($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err   = curl_error($ch);
    curl_close($ch);

    if ($err || $code === 0) {
        // Server can't reach Jellyfin — tell the browser to call directly
        return ['ok' => false, 'proxy_failed' => true, 'curl_error' => $err, 'code' => 0];
    }

    $data = json_decode($raw, true);
    return ['ok' => ($code >= 200 && $code < 300), 'data' => $data ?? [], 'code' => $code, 'raw' => $raw];
}

// Helper: if proxy failed, return a special response so JS calls Jellyfin directly
function proxyFailed(): void {
    global $jelly, $token;
    jsonResponse([
        'proxy_failed' => true,
        'jellyfin_url' => $jelly,
        'token'        => $token,
        'message'      => 'PHP cannot reach Jellyfin. Use client-side API.',
    ], 503);
}

// ── Routes ──────────────────────────────────────────────────
switch ($action) {

    case 'movies':
        $limit  = (int)($_GET['Limit']     ?? 20);
        $start  = (int)($_GET['StartIndex'] ?? 0);
        $sortBy = sanitize($_GET['SortBy']    ?? 'DateCreated');
        $sortOr = sanitize($_GET['SortOrder'] ?? 'Descending');
        $genre  = sanitize($_GET['GenreIds']  ?? '');
        $params = [
            'UserId'           => $userId,
            'IncludeItemTypes' => 'Movie',
            'Recursive'        => 'true',
            'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
            'SortBy'           => $sortBy,
            'SortOrder'        => $sortOr,
            'Limit'            => $limit,
            'StartIndex'       => $start,
            'ImageTypeLimit'   => 1,
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
        ];
        if ($genre) $params['GenreIds'] = $genre;
        $r = jellyProxy("/Users/$userId/Items", $params);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'shows':
        $limit  = (int)($_GET['Limit']      ?? 20);
        $start  = (int)($_GET['StartIndex'] ?? 0);
        $sortBy = sanitize($_GET['SortBy']   ?? 'DateCreated');
        $sortOr = sanitize($_GET['SortOrder'] ?? 'Descending');
        $r = jellyProxy("/Users/$userId/Items", [
            'UserId'           => $userId,
            'IncludeItemTypes' => 'Series',
            'Recursive'        => 'true',
            'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
            'SortBy'           => $sortBy, 'SortOrder' => $sortOr,
            'Limit'            => $limit,  'StartIndex' => $start,
            'ImageTypeLimit'   => 1,
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'latest':
        $r = jellyProxy("/Users/$userId/Items/Latest", [
            'Limit'            => 16,
            'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
            'ImageTypeLimit'   => 1,
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'featured':
        $r = jellyProxy("/Users/$userId/Items", [
            'UserId'           => $userId,
            'IncludeItemTypes' => 'Movie,Series',
            'Recursive'        => 'true',
            'SortBy'           => 'Random',
            'Limit'            => 8,
            'HasOverview'      => 'true',
            'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
            'ImageTypeLimit'   => 1,
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'search':
        $q = sanitize($_GET['q'] ?? '');
        if ($q === '') { jsonResponse(['Items' => [], 'TotalRecordCount' => 0]); }
        $r = jellyProxy("/Users/$userId/Items", [
            'UserId'           => $userId,
            'SearchTerm'       => $q,
            'Recursive'        => 'true',
            'IncludeItemTypes' => 'Movie,Series,Episode',
            'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
            'ImageTypeLimit'   => 1,
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
            'Limit'            => 30,
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'item':
        $id = sanitize($_GET['id'] ?? '');
        if (!$id) jsonResponse(['error' => 'id required'], 400);
        $r = jellyProxy("/Users/$userId/Items/$id", [
            'Fields' => 'Overview,Genres,People,MediaStreams,RunTimeTicks,CommunityRating,OfficialRating,Taglines,Studios,UserData',
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'episodes':
        $seriesId = sanitize($_GET['seriesId'] ?? '');
        $seasonId = sanitize($_GET['seasonId'] ?? '');
        if (!$seriesId) jsonResponse(['error' => 'seriesId required'], 400);
        $params = ['UserId' => $userId, 'Fields' => 'Overview,RunTimeTicks,UserData', 'ImageTypeLimit' => 1, 'EnableImageTypes' => 'Primary,Thumb'];
        if ($seasonId) $params['SeasonId'] = $seasonId;
        $r = jellyProxy("/Shows/$seriesId/Episodes", $params);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'seasons':
        $seriesId = sanitize($_GET['seriesId'] ?? '');
        if (!$seriesId) jsonResponse(['error' => 'seriesId required'], 400);
        $r = jellyProxy("/Shows/$seriesId/Seasons", ['UserId' => $userId, 'Fields' => 'Overview']);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'resume':
        $r = jellyProxy("/Users/$userId/Items/Resume", [
            'Recursive'        => 'true',
            'Fields'           => 'Overview,RunTimeTicks,UserData',
            'EnableImageTypes' => 'Primary,Backdrop,Thumb',
            'ImageTypeLimit'   => 1,
            'Limit'            => 12,
        ]);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'genres':
        $r = jellyProxy('/Genres', ['UserId' => $userId, 'SortBy' => 'SortName', 'Recursive' => 'true']);
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'favorites':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input  = json_decode(file_get_contents('php://input'), true) ?? [];
            $itemId = sanitize($input['itemId'] ?? '');
            $fav    = (bool)($input['favorite'] ?? false);
            if (!$itemId) jsonResponse(['error' => 'itemId required'], 400);
            $r = jellyProxy("/Users/$userId/FavoriteItems/$itemId", [], $fav ? 'POST' : 'DELETE');
            if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
            jsonResponse(['success' => true]);
        } else {
            $r = jellyProxy("/Users/$userId/Items", [
                'UserId'           => $userId,
                'IsFavorite'       => 'true',
                'Recursive'        => 'true',
                'IncludeItemTypes' => 'Movie,Series',
                'Fields'           => 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
                'ImageTypeLimit'   => 1,
            ]);
            if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
            jsonResponse($r['data'] ?? []);
        }
        break;

    case 'stream_url':
        // Just build and return the URL — browser fetches the video directly
        $id = sanitize($_GET['id'] ?? '');
        if (!$id) jsonResponse(['error' => 'id required'], 400);
        jsonResponse([
            'url'      => JELLYFIN_URL . "/Videos/$id/stream?Static=true&api_key=$token&DeviceId=cinflix-browser-001",
            'itemId'   => $id,
            'token'    => $token,
            'baseUrl'  => JELLYFIN_URL,
        ]);
        break;

    case 'profile':
        $r = jellyProxy("/Users/$userId");
        if (!$r['ok'] && ($r['proxy_failed'] ?? false)) { proxyFailed(); }
        jsonResponse($r['data'] ?? []);
        break;

    case 'progress':
        // Silently fail — not critical
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $itemId = sanitize($input['itemId'] ?? '');
        $ticks  = (int)($input['positionTicks'] ?? 0);
        if ($itemId) {
            jellyProxy('/Sessions/Playing/Progress', [], 'POST', ['ItemId' => $itemId, 'PositionTicks' => $ticks]);
        }
        jsonResponse(['success' => true]);
        break;

    case 'mark_played':
        $input  = json_decode(file_get_contents('php://input'), true) ?? [];
        $itemId = sanitize($input['itemId'] ?? '');
        if ($itemId) jellyProxy("/Users/$userId/PlayedItems/$itemId", [], 'POST');
        jsonResponse(['success' => true]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
