<?php
/**
 * Cinflix - Jellyfin API Wrapper
 * All cURL-based API calls to Jellyfin server
 */

require_once __DIR__ . '/../config.php';

// ============================================================
// CORE: Make API Request
// ============================================================
function jellyfinRequest(
    string $endpoint,
    string $method = 'GET',
    array $body = [],
    ?string $token = null,
    array $queryParams = []
): array {
    $url = JELLYFIN_URL . $endpoint;

    if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false, // Self-signed cert support
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Authorization: ' . getAuthHeader($token),
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Emby-Authorization: ' . getAuthHeader($token),
        ],
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    } elseif ($method !== 'GET') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if (!empty($body)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['error' => $error, 'code' => 0];
    }

    $decoded = json_decode($response, true);
    return ['data' => $decoded ?? [], 'code' => $httpCode, 'raw' => $response];
}

// ============================================================
// AUTH: Login User
// ============================================================
function loginUser(string $username, string $password): array {
    $result = jellyfinRequest('/Users/AuthenticateByName', 'POST', [
        'Username' => $username,
        'Pw'       => $password,
    ]);

    if ($result['code'] === 200 && !empty($result['data'])) {
        $data = $result['data'];
        return [
            'success'      => true,
            'access_token' => $data['AccessToken'] ?? '',
            'user_id'      => $data['User']['Id'] ?? '',
            'user_name'    => $data['User']['Name'] ?? '',
            'user_data'    => $data['User'] ?? [],
        ];
    }

    return [
        'success' => false,
        'error'   => $result['code'] === 401 ? 'Invalid credentials' : 'Server error',
    ];
}

// ============================================================
// USER: Get User Profile
// ============================================================
function getUserData(string $userId, string $token): array {
    $result = jellyfinRequest("/Users/$userId", 'GET', [], $token);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Fetch Items
// ============================================================
function fetchMedia(
    string $userId,
    string $token,
    array $params = []
): array {
    $defaults = [
        'UserId'              => $userId,
        'Recursive'           => 'true',
        'Fields'              => 'Overview,Genres,People,MediaStreams,ProviderIds,RunTimeTicks,CommunityRating,OfficialRating',
        'ImageTypeLimit'      => 1,
        'EnableImageTypes'    => 'Primary,Backdrop,Thumb',
        'SortBy'              => 'SortName',
        'SortOrder'           => 'Ascending',
        'Limit'               => 20,
    ];

    $query  = array_merge($defaults, $params);
    $result = jellyfinRequest("/Users/$userId/Items", 'GET', [], $token, $query);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Fetch Latest Items
// ============================================================
function fetchLatest(string $userId, string $token, string $parentId = ''): array {
    $params = [
        'Limit'           => 16,
        'Fields'          => 'Overview,Genres,RunTimeTicks,CommunityRating',
        'ImageTypeLimit'  => 1,
        'EnableImageTypes'=> 'Primary,Backdrop,Thumb',
    ];
    if ($parentId) $params['ParentId'] = $parentId;

    $result = jellyfinRequest("/Users/$userId/Items/Latest", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Search Items
// ============================================================
function searchMedia(string $userId, string $token, string $query): array {
    $params = [
        'SearchTerm'          => $query,
        'Recursive'           => 'true',
        'IncludeItemTypes'    => 'Movie,Series,Episode',
        'Fields'              => 'Overview,Genres,RunTimeTicks,CommunityRating',
        'ImageTypeLimit'      => 1,
        'EnableImageTypes'    => 'Primary,Backdrop,Thumb',
        'Limit'               => 30,
    ];
    $result = jellyfinRequest("/Users/$userId/Items", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Get Item Details
// ============================================================
function getItemDetails(string $userId, string $token, string $itemId): array {
    $params = [
        'Fields' => 'Overview,Genres,People,MediaStreams,ProviderIds,RunTimeTicks,CommunityRating,OfficialRating,Taglines,Studios,ExternalUrls',
    ];
    $result = jellyfinRequest("/Users/$userId/Items/$itemId", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Get Episodes for Series
// ============================================================
function getEpisodes(string $userId, string $token, string $seriesId, string $seasonId = ''): array {
    $params = [
        'UserId'          => $userId,
        'Fields'          => 'Overview,RunTimeTicks,MediaStreams',
        'ImageTypeLimit'  => 1,
        'EnableImageTypes'=> 'Primary,Thumb',
    ];
    if ($seasonId) $params['SeasonId'] = $seasonId;

    $result = jellyfinRequest("/Shows/$seriesId/Episodes", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Get Seasons for Series
// ============================================================
function getSeasons(string $userId, string $token, string $seriesId): array {
    $params = ['UserId' => $userId, 'Fields' => 'Overview'];
    $result = jellyfinRequest("/Shows/$seriesId/Seasons", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Get Resume/Continue Watching Items
// ============================================================
function getResumeItems(string $userId, string $token): array {
    $params = [
        'Recursive'           => 'true',
        'Fields'              => 'Overview,RunTimeTicks,UserData',
        'EnableImageTypes'    => 'Primary,Backdrop,Thumb',
        'ImageTypeLimit'      => 1,
        'Limit'               => 12,
    ];
    $result = jellyfinRequest("/Users/$userId/Items/Resume", 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// MEDIA: Get Genres
// ============================================================
function getGenres(string $userId, string $token): array {
    $params = ['UserId' => $userId, 'SortBy' => 'SortName', 'Recursive' => 'true'];
    $result = jellyfinRequest('/Genres', 'GET', [], $token, $params);
    return $result['data'] ?? [];
}

// ============================================================
// FAVORITES: Get User Favorites
// ============================================================
function getFavorites(string $userId, string $token): array {
    return fetchMedia($userId, $token, [
        'IsFavorite'       => 'true',
        'IncludeItemTypes' => 'Movie,Series',
    ]);
}

// ============================================================
// FAVORITES: Toggle Favorite
// ============================================================
function toggleFavorite(string $userId, string $token, string $itemId, bool $isFavorite): array {
    $method = $isFavorite ? 'POST' : 'DELETE';
    $result = jellyfinRequest("/Users/$userId/FavoriteItems/$itemId", $method, [], $token);
    return ['success' => $result['code'] === 200, 'data' => $result['data'] ?? []];
}

// ============================================================
// PLAYBACK: Mark Item Played
// ============================================================
function markPlayed(string $userId, string $token, string $itemId): array {
    $result = jellyfinRequest("/Users/$userId/PlayedItems/$itemId", 'POST', [], $token);
    return ['success' => in_array($result['code'], [200, 204])];
}

// ============================================================
// PLAYBACK: Report Playback Progress
// ============================================================
function reportProgress(string $userId, string $token, string $itemId, int $positionTicks): array {
    $result = jellyfinRequest('/Sessions/Playing/Progress', 'POST', [
        'ItemId'        => $itemId,
        'PositionTicks' => $positionTicks,
    ], $token);
    return ['success' => in_array($result['code'], [200, 204])];
}

// ============================================================
// MEDIA: Get Stream URL (returns URL string, not API call)
// ============================================================
function getStreamUrl(string $itemId, string $token, string $mediaSourceId = ''): string {
    $params = [
        'Static'          => 'true',
        'api_key'         => $token,
        'DeviceId'        => APP_DEVICE_ID,
    ];
    if ($mediaSourceId) $params['mediaSourceId'] = $mediaSourceId;
    return JELLYFIN_URL . "/Videos/$itemId/stream?" . http_build_query($params);
}

// ============================================================
// MEDIA: Get Image URL
// ============================================================
function getImageUrl(string $itemId, string $type = 'Primary', int $width = 400): string {
    return JELLYFIN_URL . "/Items/$itemId/Images/$type?width=$width&quality=90&fillWidth=$width";
}
