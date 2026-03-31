<?php
/**
 * Cinflix - Main Entry Point
 */

require_once __DIR__ . '/config.php';

$page = sanitize($_GET['page'] ?? 'home');
$id   = sanitize($_GET['id'] ?? '');

// If not authenticated, only allow the login page
if (!isAuthenticated() && $page !== 'login') {
    redirect('/cinflix/?page=login');
}

// Already logged in — don't show login page again
if (isAuthenticated() && $page === 'login') {
    redirect('/cinflix/?page=home');
}

// Player page has its own standalone HTML, bypass layout
if ($page === 'player' && isAuthenticated()) {
    include __DIR__ . '/pages/player.php';
    exit;
}

// Validate page file exists
$pageFile = __DIR__ . "/pages/{$page}.php";
if (!file_exists($pageFile)) {
    $page     = '404';
    $pageFile = __DIR__ . '/pages/404.php';
}

include __DIR__ . '/components/layout.php';
