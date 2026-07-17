<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/layout.php';

auth_logout();
header('Location: ' . rm_url('index.php') . '?logged_out=1');
exit;