/**
 * Cinflix — API Module
 *
 * Two-tier architecture:
 *   Tier 1: Ask PHP proxy (/cinflix/api/media.php)
 *   Tier 2: If PHP can't reach Jellyfin (proxy_failed:true),
 *           call Jellyfin directly from the browser.
 */

const API = (() => {
    const phpBase    = '/cinflix/api/media.php';

    // Read stored values (set at login)
    function getToken()   { return localStorage.getItem('cf_token')   || window.CINFLIX?.token   || ''; }
    function getUserId()  { return localStorage.getItem('cf_userId')  || window.CINFLIX?.userId  || ''; }
    function getBaseUrl() { return localStorage.getItem('cf_jelly')   || window.CINFLIX?.baseUrl || 'https://karan.ptgn.in:8920'; }

    function jellyAuthHdr(token) {
        return `MediaBrowser Client="Cinflix Web", Device="Browser", DeviceId="cinflix-browser-001", Version="1.0.0", Token="${token}"`;
    }

    // ── Call Jellyfin directly from browser ──────────────────
    async function jellyDirect(path, params = {}, method = 'GET', body = null) {
        const token   = getToken();
        const baseUrl = getBaseUrl();
        let url       = baseUrl + path;
        if (Object.keys(params).length) url += '?' + new URLSearchParams(params);

        const opts = {
            method,
            headers: {
                'Authorization':      jellyAuthHdr(token),
                'X-Emby-Authorization': jellyAuthHdr(token),
                'Content-Type':       'application/json',
                'Accept':             'application/json',
            },
        };
        if (body) opts.body = JSON.stringify(body);

        try {
            const res  = await fetch(url, opts);
            if (!res.ok) return null;
            // Some endpoints return empty body (204)
            const text = await res.text();
            return text ? JSON.parse(text) : {};
        } catch (err) {
            console.error('[API direct]', path, err.message);
            return null;
        }
    }

    // ── Call PHP proxy first, fall back to direct ────────────
    async function get(action, params = {}) {
        const query = new URLSearchParams({ action, ...params });
        try {
            const res = await fetch(`${phpBase}?${query}`, {
                credentials: 'include',
                signal: AbortSignal.timeout(10000),
            });

            if (res.status === 401) {
                localStorage.clear();
                window.location.href = '/cinflix/?page=login';
                return null;
            }

            const data = await res.json();

            // PHP proxy couldn't reach Jellyfin → call directly
            if (data?.proxy_failed) {
                console.warn('[API] PHP proxy failed for', action, '— calling Jellyfin directly');
                return await jellyDirect_action(action, params);
            }

            return data;

        } catch (err) {
            // Network error to PHP itself or timeout
            console.warn('[API] PHP request failed for', action, '— trying direct:', err.message);
            return await jellyDirect_action(action, params);
        }
    }

    // ── Map action names to direct Jellyfin calls ────────────
    async function jellyDirect_action(action, params) {
        const userId = getUserId();

        switch (action) {
            case 'movies':
                return jellyDirect(`/Users/${userId}/Items`, {
                    UserId: userId, IncludeItemTypes: 'Movie', Recursive: 'true',
                    Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
                    SortBy: params.SortBy || 'DateCreated', SortOrder: params.SortOrder || 'Descending',
                    Limit: params.Limit || 20, StartIndex: params.StartIndex || 0,
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Backdrop,Thumb',
                    ...(params.GenreIds ? { GenreIds: params.GenreIds } : {}),
                });
            case 'shows':
                return jellyDirect(`/Users/${userId}/Items`, {
                    UserId: userId, IncludeItemTypes: 'Series', Recursive: 'true',
                    Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
                    SortBy: params.SortBy || 'DateCreated', SortOrder: params.SortOrder || 'Descending',
                    Limit: params.Limit || 20, StartIndex: params.StartIndex || 0,
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Backdrop,Thumb',
                });
            case 'latest':
                return jellyDirect(`/Users/${userId}/Items/Latest`, {
                    Limit: 16, Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Backdrop,Thumb',
                });
            case 'featured':
                return jellyDirect(`/Users/${userId}/Items`, {
                    UserId: userId, IncludeItemTypes: 'Movie,Series', Recursive: 'true',
                    SortBy: 'Random', Limit: 8, HasOverview: 'true',
                    Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,OfficialRating,UserData',
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Backdrop,Thumb',
                });
            case 'search':
                return jellyDirect(`/Users/${userId}/Items`, {
                    UserId: userId, SearchTerm: params.q || '', Recursive: 'true',
                    IncludeItemTypes: 'Movie,Series,Episode',
                    Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Backdrop,Thumb', Limit: 30,
                });
            case 'item':
                return jellyDirect(`/Users/${userId}/Items/${params.id}`, {
                    Fields: 'Overview,Genres,People,MediaStreams,RunTimeTicks,CommunityRating,OfficialRating,Taglines,Studios,UserData',
                });
            case 'episodes':
                return jellyDirect(`/Shows/${params.seriesId}/Episodes`, {
                    UserId: userId, Fields: 'Overview,RunTimeTicks,UserData',
                    ImageTypeLimit: 1, EnableImageTypes: 'Primary,Thumb',
                    ...(params.seasonId ? { SeasonId: params.seasonId } : {}),
                });
            case 'seasons':
                return jellyDirect(`/Shows/${params.seriesId}/Seasons`, { UserId: userId, Fields: 'Overview' });
            case 'resume':
                return jellyDirect(`/Users/${userId}/Items/Resume`, {
                    Recursive: 'true', Fields: 'Overview,RunTimeTicks,UserData',
                    EnableImageTypes: 'Primary,Backdrop,Thumb', ImageTypeLimit: 1, Limit: 12,
                });
            case 'genres':
                return jellyDirect('/Genres', { UserId: userId, SortBy: 'SortName', Recursive: 'true' });
            case 'favorites':
                return jellyDirect(`/Users/${userId}/Items`, {
                    UserId: userId, IsFavorite: 'true', Recursive: 'true',
                    IncludeItemTypes: 'Movie,Series',
                    Fields: 'Overview,Genres,RunTimeTicks,CommunityRating,UserData',
                    ImageTypeLimit: 1,
                });
            case 'stream_url': {
                const token = getToken();
                const url   = `${getBaseUrl()}/Videos/${params.id}/stream?Static=true&api_key=${token}&DeviceId=cinflix-browser-001`;
                return { url, itemId: params.id, token, baseUrl: getBaseUrl() };
            }
            case 'profile':
                return jellyDirect(`/Users/${userId}`);
            default:
                console.error('[API] Unknown action for direct call:', action);
                return null;
        }
    }

    // ── POST (favorites, progress etc.) ─────────────────────
    async function post(action, body = {}) {
        try {
            const res = await fetch(`${phpBase}?action=${action}`, {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json' },
                body:        JSON.stringify(body),
                signal:      AbortSignal.timeout(8000),
            });
            const data = await res.json();
            if (data?.proxy_failed) {
                // Handle direct fallback for POST actions
                return await postDirect(action, body);
            }
            return data;
        } catch {
            return await postDirect(action, body);
        }
    }

    async function postDirect(action, body) {
        const userId = getUserId();
        switch (action) {
            case 'favorites':
                return jellyDirect(`/Users/${userId}/FavoriteItems/${body.itemId}`, {}, body.favorite ? 'POST' : 'DELETE');
            case 'progress':
                return jellyDirect('/Sessions/Playing/Progress', {}, 'POST', { ItemId: body.itemId, PositionTicks: body.positionTicks });
            case 'mark_played':
                return jellyDirect(`/Users/${userId}/PlayedItems/${body.itemId}`, {}, 'POST');
            default:
                return null;
        }
    }

    async function toggleFavorite(itemId, isFavorite) {
        return post('favorites', { itemId, favorite: isFavorite });
    }

    async function reportProgress(itemId, positionTicks) {
        return post('progress', { itemId, positionTicks });
    }

    async function getStreamUrl(itemId) {
        return get('stream_url', { id: itemId });
    }

    return { get, post, toggleFavorite, reportProgress, getStreamUrl };
})();
