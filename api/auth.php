<?php
/**
 * Cinflix — Auth API (Client-Side Token Architecture)
 *
 * Since Hostinger cannot reach the Jellyfin server directly,
 * the browser handles the Jellyfin API call and sends us
 * back the token. PHP only manages the session.
 */

require_once __DIR__ . '/../config.php';

// CORS for same-origin fetch with credentials
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$action = sanitize($_GET['action'] ?? '');

switch ($action) {

    // ── LOGIN ──────────────────────────────────────────────────
    // Browser already authenticated with Jellyfin directly.
    // It sends us the token + user info to store in session.
    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input     = json_decode(file_get_contents('php://input'), true) ?? [];
        $token     = trim($input['token']     ?? '');
        $userId    = trim($input['userId']    ?? '');
        $userName  = trim($input['userName']  ?? '');
        $userData  = $input['userData']  ?? [];

        if ($token === '' || $userId === '') {
            jsonResponse(['error' => 'Token and userId are required'], 400);
        }

        // Basic sanity: token should be alphanumeric
        if (!preg_match('/^[a-zA-Z0-9]+$/', $token)) {
            jsonResponse(['error' => 'Invalid token format'], 400);
        }

        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        session_regenerate_id(true);

        $_SESSION['access_token'] = $token;
        $_SESSION['user_id']      = sanitize($userId);
        $_SESSION['user_name']    = sanitize($userName);
        $_SESSION['user_data']    = $userData;
        $_SESSION['logged_in_at'] = time();

        session_write_close();

        jsonResponse(['success' => true]);
        break;

    // ── LOGOUT ────────────────────────────────────────────────
    case 'logout':
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        jsonResponse(['success' => true]);
        break;

    // ── CHECK ─────────────────────────────────────────────────
    case 'check':
        jsonResponse([
            'authenticated' => isAuthenticated(),
            'user_id'       => $_SESSION['user_id']      ?? '',
            'user_name'     => $_SESSION['user_name']    ?? '',
            'token'         => $_SESSION['access_token'] ?? '',
        ]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
