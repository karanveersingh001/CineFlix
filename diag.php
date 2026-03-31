<?php
/**
 * Cinflix - Diagnostics Backend
 * Run this to find exactly what's broken.
 * DELETE THIS FILE after troubleshooting!
 */

// No auth required for diagnostics (it's the auth that's broken)
error_reporting(E_ALL);
ini_set('display_errors', '1');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('JELLYFIN_URL', 'https://karan.ptgn.in:8920');
define('APP_CLIENT',    'Cinflix Web');
define('APP_VERSION',   '1.0.0');
define('APP_DEVICE',    'Diagnostics');
define('APP_DEVICE_ID', 'cinflix-diag-001');

$action = $_GET['action'] ?? 'php';

// ============================================================
// 1. PHP ENVIRONMENT
// ============================================================
if ($action === 'php') {
    echo json_encode([
        'php_version'    => PHP_VERSION,
        'php_ok'         => version_compare(PHP_VERSION, '7.4', '>='),
        'curl'           => extension_loaded('curl'),
        'json'           => extension_loaded('json'),
        'session'        => extension_loaded('session'),
        'openssl'        => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : (extension_loaded('openssl') ? 'loaded' : 'missing'),
        'display_errors' => ini_get('display_errors'),
        'error_log'      => ini_get('error_log'),
    ]);
    exit;
}

// ============================================================
// 2. cURL RAW TEST
// ============================================================
if ($action === 'curl') {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => JELLYFIN_URL . '/System/Info/Public',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body  = curl_exec($ch);
    $info  = curl_getinfo($ch);
    $error = curl_error($ch);
    curl_close($ch);

    echo json_encode([
        'http_code'     => $info['http_code'],
        'curl_error'    => $error,
        'total_time'    => round($info['total_time'], 3),
        'redirect_url'  => $info['redirect_url'],
        'ssl_verified'  => false, // skipped intentionally
        'response_body' => $body,
    ]);
    exit;
}

// ============================================================
// 3. JELLYFIN PUBLIC INFO
// ============================================================
if ($action === 'jellyfin') {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => JELLYFIN_URL . '/System/Info/Public',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $body  = curl_exec($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $data = json_decode($body, true) ?? [];

    echo json_encode([
        'reachable'      => ($code === 200 && !$error),
        'http_code'      => $code,
        'curl_error'     => $error,
        'server_name'    => $data['ServerName'] ?? null,
        'server_version' => $data['Version'] ?? null,
        'os'             => $data['OperatingSystem'] ?? null,
        'raw'            => $body,
    ]);
    exit;
}

// ============================================================
// 4. LIVE LOGIN TEST
// ============================================================
if ($action === 'login') {
    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';

    $authHeader = 'MediaBrowser Client="' . APP_CLIENT . '", Device="' . APP_DEVICE
        . '", DeviceId="' . APP_DEVICE_ID . '", Version="' . APP_VERSION . '"';

    $body = json_encode(['Username' => $username, 'Pw' => $password]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => JELLYFIN_URL . '/Users/AuthenticateByName',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Emby-Authorization: ' . $authHeader,
            'Authorization: ' . $authHeader,
        ],
    ]);

    $raw   = curl_exec($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr  = curl_error($ch);
    curl_close($ch);

    $data  = json_decode($raw, true) ?? [];
    $token = $data['AccessToken'] ?? null;
    $uid   = $data['User']['Id'] ?? null;

    $errMsg = null;
    if ($cerr)       $errMsg = 'cURL error: ' . $cerr;
    elseif ($code === 401) $errMsg = '401 Unauthorized – wrong username/password';
    elseif ($code === 400) $errMsg = '400 Bad Request – malformed request body';
    elseif ($code === 0)   $errMsg = 'No response – server unreachable or SSL failure';
    elseif ($code >= 500)  $errMsg = 'Jellyfin server error (5xx)';
    elseif (!$token)       $errMsg = "HTTP $code but no AccessToken in response";

    echo json_encode([
        'success'      => ($code === 200 && $token),
        'http_code'    => $code,
        'curl_error'   => $cerr,
        'token'        => $token,
        'user_id'      => $uid,
        'error'        => $errMsg,
        'raw_response' => $raw,
    ]);
    exit;
}

// ============================================================
// 5. SESSION CHECK
// ============================================================
if ($action === 'session') {
    $savePath = session_save_path() ?: sys_get_temp_dir();
    $writable = is_writable($savePath);

    if (session_status() === PHP_SESSION_NONE) {
        $started = @session_start();
    } else {
        $started = true;
    }

    echo json_encode([
        'started'           => $started,
        'name'              => session_name(),
        'save_path'         => $savePath,
        'save_path_writable'=> $writable,
        'cookie_httponly'   => (bool)ini_get('session.cookie_httponly'),
        'logged_in'         => !empty($_SESSION['access_token']),
    ]);
    exit;
}

// ============================================================
// 6. RAW HEADER TEST
// ============================================================
if ($action === 'raw') {
    $authHeader = 'MediaBrowser Client="' . APP_CLIENT . '", Device="' . APP_DEVICE
        . '", DeviceId="' . APP_DEVICE_ID . '", Version="' . APP_VERSION . '"';

    $url  = JELLYFIN_URL . '/Users/AuthenticateByName';
    $body = json_encode(['Username' => '_diag_test_', 'Pw' => '_diag_test_']);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: ' . $authHeader,
        ],
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $cerr = curl_error($ch);
    curl_close($ch);

    echo json_encode([
        'url'         => $url,
        'auth_header' => $authHeader,
        'http_code'   => $code,
        'curl_error'  => $cerr,
        'raw'         => $raw,
    ]);
    exit;
}

echo json_encode(['error' => 'Unknown action']);
