<?php

declare(strict_types=1);

session_start();

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'roommate_rental');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

define('VIEWING_SLOT_MINUTES', 30);

// Demo only: in a real project this should come from login/session authentication.
function current_user_id(): int
{
    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    if (isset($_GET['user_id'])) {
        return max(1, (int) $_GET['user_id']);
    }

    if (isset($_POST['user_id'])) {
        return max(1, (int) $_POST['user_id']);
    }

    return 1;
}
