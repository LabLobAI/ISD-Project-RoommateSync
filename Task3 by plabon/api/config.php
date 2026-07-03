<?php
// Database config — update with real credentials before use
define('DB_DSN', 'mysql:host=127.0.0.1;dbname=stl_db;charset=utf8mb4');
define('DB_USER', 'db_user');
define('DB_PASS', 'db_pass');

function get_db() {
    $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];
    return new PDO(DB_DSN, DB_USER, DB_PASS, $opts);
}
