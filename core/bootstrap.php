<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

define('RM_DB_HOST', '127.0.0.1');
define('RM_DB_PORT', '3307');
define('RM_DB_NAME', 'roommate_rental');
define('RM_DB_USER', 'root');
define('RM_DB_PASS', '');
define('RM_DB_CHARSET', 'utf8mb4');

define('VIEWING_SLOT_MINUTES', 30);
define('RM_REMEMBER_COOKIE', 'roommatesync_remember');
define('RM_REMEMBER_COOKIE_TTL', 60 * 60 * 24 * 30);

function current_user_id(): int
{
    if (function_exists('auth_user_id')) {
        $userId = auth_user_id();
        if ($userId > 0) {
            return $userId;
        }
    }

    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    return 0;
}
