<?php
// Secure configuration and DB connection for Wheel module
// - Secrets are NOT hardcoded here
// - Load from external env file or environment variables
// - Disable error display to visitors and log internally

declare(strict_types=1);

// Toggle for development; override in your entrypoint if needed
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', false);
}

if (!APP_DEBUG) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Load secrets from external file or environment
function wheel_load_env(): array {
    $candidates = [
        // Preferred: outside web root
        dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env.wheel.php',
        // Fallback: parent of this folder (public_html)
        dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env.wheel.php',
        // Last resort: same folder (avoid in production)
        __DIR__ . DIRECTORY_SEPARATOR . '.env.wheel.php',
    ];
    foreach ($candidates as $file) {
        if (is_file($file)) {
            $cfg = include $file;
            if (is_array($cfg)) {
                return $cfg;
            }
        }
    }
    return [
        'DB_HOST' => getenv('WHEEL_DB_HOST') ?: null,
        'DB_USER' => getenv('WHEEL_DB_USER') ?: null,
        'DB_PASS' => getenv('WHEEL_DB_PASS') ?: null,
        'DB_NAME' => getenv('WHEEL_DB_NAME') ?: null,
    ];
}

$env = wheel_load_env();

// Define constants (empty values will cause connection failure with generic message)
if (!defined('DB_HOST')) define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
if (!defined('DB_USER')) define('DB_USER', $env['DB_USER'] ?? '');
if (!defined('DB_PASS')) define('DB_PASS', $env['DB_PASS'] ?? '');
if (!defined('DB_NAME')) define('DB_NAME', $env['DB_NAME'] ?? '');

// Centralized DB connection with safe error handling
function getConnection(): mysqli {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_errno) {
        // Log internal error without exposing credentials or SQL
        error_log('Wheel DB connection failed: ' . $conn->connect_error);
        http_response_code(500);
        exit('Lỗi hệ thống. Vui lòng thử lại sau.');
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Week helpers
function getWeekNumber($date = null) {
    $date = $date ?: date('Y-m-d');
    return date('W', strtotime($date));
}

function getYear($date = null) {
    $date = $date ?: date('Y-m-d');
    return date('Y', strtotime($date));
}

function getDayMapping() {
    return [
        'monday' => 'Thứ 2',
        'tuesday' => 'Thứ 3',
        'wednesday' => 'Thứ 4',
        'thursday' => 'Thứ 5',
        'friday' => 'Thứ 6',
        'saturday' => 'Thứ 7',
        'sunday' => 'Chủ nhật'
    ];
}
