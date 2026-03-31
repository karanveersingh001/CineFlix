<?php
/**
 * CineFlix - Configuration File
 * Version: v1.1 – Rebranded to CineFlix
 */

// ============================================================
// JELLYFIN SERVER CONFIG
// ============================================================
define('JELLYFIN_URL',    'https://karan.ptgn.in:8920');
define('APP_NAME',        'CineFlix');
define('APP_VERSION',     '1.1.0');
define('APP_CLIENT',      'CineFlix Web');
define('APP_DEVICE',      'Web Browser');
define('APP_DEVICE_ID',   'cineflix-web-001');

define('SESSION_LIFETIME', 86400 * 30); // 30 days

// ============================================================
// API AUTH HEADER
// ============================================================
function getAuthHeader(?string $token = null): string {
    $parts = [
        'MediaBrowser Client="' . APP_CLIENT . '"',
        'Device="'             . APP_DEVICE   . '"',
        'DeviceId="'           . APP_DEVICE_ID . '"',
        'Version="'            . APP_VERSION   . '"',
    ];
    if ($token) {
        $parts[] = 'Token="' . $token . '"';
    }
    return 'MediaBrowser ' . implode(', ', $parts);
}

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    ini_set('session.gc_maxlifetime',   (string) SESSION_LIFETIME);
    ini_set('session.cookie_lifetime',  (string) SESSION_LIFETIME);
    ini_set('session.cookie_httponly',  '1');
    ini_set('session.cookie_path',      '/');
    ini_set('session.use_strict_mode',  '1');
    ini_set('session.cookie_secure',    $isHttps ? '1' : '0');
    ini_set('session.cookie_samesite',  'Lax');

    session_start();
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo json_encode($data);
    exit;
}

function isAuthenticated(): bool {
    return isset($_SESSION['access_token'], $_SESSION['user_id'])
        && $_SESSION['access_token'] !== ''
        && $_SESSION['user_id'] !== '';
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
