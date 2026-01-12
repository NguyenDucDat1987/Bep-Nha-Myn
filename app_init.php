<?php
// Wheel bootstrap: secure runtime, session, CSRF utilities, and common helpers

declare(strict_types=1);

// Set debug default (override before include if needed)
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Session hardening
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config.php';

// CSRF utilities
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_token" value="' . $t . '">';
}

function csrf_validate(): bool {
    $token = $_POST['_token'] ?? '';
    return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

// Simple view variables handler for templates
$GLOBALS['__wheel_view'] = [
    'title' => 'Bếp Nhà Myn',
    'bodyClass' => '',
    'scripts' => [],
];

function view_set(string $key, $value): void {
    $GLOBALS['__wheel_view'][$key] = $value;
}

function view_get(string $key, $default = null) {
    return $GLOBALS['__wheel_view'][$key] ?? $default;
}

// Escape helper
function e($str): string {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}
