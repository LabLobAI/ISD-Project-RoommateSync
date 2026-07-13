<?php

declare(strict_types=1);

function auth_user(): ?array
{
    if (isset($_SESSION['auth_user']) && is_array($_SESSION['auth_user'])) {
        return $_SESSION['auth_user'];
    }

    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;
    if ($userId <= 0 && isset($_COOKIE[RM_REMEMBER_COOKIE])) {
        $userId = auth_bootstrap_from_cookie();
    }

    if ($userId <= 0) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT id, full_name, email, city, password_hash, remember_token_hash, remember_token_expires_at
         FROM users
         WHERE id = :id
         LIMIT 1'
    );
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch();

    if (!$user) {
        return null;
    }

    $_SESSION['auth_user'] = $user;

    return $user;
}

function auth_user_id(): int
{
    $user = auth_user();
    return $user ? (int) $user['id'] : 0;
}

function auth_is_logged_in(): bool
{
    return auth_user_id() > 0;
}

function auth_bootstrap_from_cookie(): int
{
    if (!isset($_COOKIE[RM_REMEMBER_COOKIE])) {
        return 0;
    }

    $cookie = (string) $_COOKIE[RM_REMEMBER_COOKIE];
    if (!preg_match('/^[a-f0-9]{64}:[a-f0-9]{64}$/i', $cookie)) {
        return 0;
    }

    [$selector, $token] = explode(':', $cookie, 2);
    $tokenHash = hash('sha256', $token);

    $stmt = db()->prepare(
        'SELECT id, full_name, email, city
         FROM users
         WHERE remember_token_hash = :token_hash
           AND remember_token_expires_at IS NOT NULL
           AND remember_token_expires_at > NOW()
         LIMIT 1'
    );
    $stmt->execute(['token_hash' => $selector . ':' . $tokenHash]);
    $user = $stmt->fetch();

    if (!$user) {
        auth_forget_cookie();
        return 0;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['auth_user'] = $user;

    return (int) $user['id'];
}

function auth_login(string $email, string $password, bool $remember = false): array
{
    $stmt = db()->prepare(
        'SELECT id, full_name, email, city, password_hash
         FROM users
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !isset($user['password_hash']) || $user['password_hash'] === '' || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    session_regenerate_id(true);
    auth_store_session((int) $user['id'], [
        'id' => (int) $user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'city' => $user['city'],
    ]);

    if ($remember) {
        auth_issue_remember_cookie((int) $user['id']);
    } else {
        auth_clear_token((int) $user['id']);
        auth_forget_cookie();
    }

    return ['success' => true, 'user' => auth_user()];
}

function auth_register(string $fullName, string $email, string $city, string $password, bool $remember = true): array
{
    $fullName = trim($fullName);
    $email = trim($email);
    $city = trim($city);

    if ($fullName === '' || $email === '' || $city === '' || strlen($password) < 8) {
        return ['success' => false, 'message' => 'Please provide your name, email, city, and an 8 character password.'];
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'An account already exists for that email address.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = db()->prepare(
        'INSERT INTO users (full_name, email, city, password_hash)
         VALUES (:full_name, :email, :city, :password_hash)'
    );
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'city' => $city,
        'password_hash' => $passwordHash,
    ]);

    $userId = (int) db()->lastInsertId();
    session_regenerate_id(true);
    auth_store_session($userId, [
        'id' => $userId,
        'full_name' => $fullName,
        'email' => $email,
        'city' => $city,
    ]);

    if ($remember) {
        auth_issue_remember_cookie($userId);
    }

    return ['success' => true, 'user' => auth_user()];
}

function auth_logout(): void
{
    if (isset($_SESSION['user_id'])) {
        auth_clear_token((int) $_SESSION['user_id']);
    }

    unset($_SESSION['user_id'], $_SESSION['auth_user']);
    auth_forget_cookie();
    session_regenerate_id(true);
}

function auth_store_session(int $userId, array $user): void
{
    $_SESSION['user_id'] = $userId;
    $_SESSION['auth_user'] = $user;
}

function auth_issue_remember_cookie(int $userId): void
{
    $selector = bin2hex(random_bytes(32));
    $token = bin2hex(random_bytes(32));
    $tokenHash = $selector . ':' . hash('sha256', $token);
    $expiresAt = (new DateTimeImmutable())->modify('+' . (RM_REMEMBER_COOKIE_TTL / 86400) . ' days')->format('Y-m-d H:i:s');

    $stmt = db()->prepare(
        'UPDATE users
         SET remember_token_hash = :token_hash,
             remember_token_expires_at = :expires_at
         WHERE id = :id'
    );
    $stmt->execute([
        'token_hash' => $tokenHash,
        'expires_at' => $expiresAt,
        'id' => $userId,
    ]);

    setcookie(
        RM_REMEMBER_COOKIE,
        $selector . ':' . $token,
        [
            'expires' => time() + RM_REMEMBER_COOKIE_TTL,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function auth_clear_token(int $userId): void
{
    $stmt = db()->prepare(
        'UPDATE users
         SET remember_token_hash = NULL,
             remember_token_expires_at = NULL
         WHERE id = :id'
    );
    $stmt->execute(['id' => $userId]);
}

function auth_forget_cookie(): void
{
    setcookie(
        RM_REMEMBER_COOKIE,
        '',
        [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function auth_require_login(): array
{
    $user = auth_user();
    if (!$user) {
        header('Location: /auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
    return $user;
}

function auth_get_role(int $userId): string
{
    $stmt = db()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    $row = $stmt->fetch();
    return $row && $row['role'] ? $row['role'] : 'tenant';
}

function auth_user_role(): string
{
    $user = auth_user();
    if (!$user) {
        return 'guest';
    }
    return auth_get_role((int) $user['id']);
}

function auth_has_role(string $role): bool
{
    $userRole = auth_user_role();
    $hierarchy = ['guest' => 0, 'tenant' => 1, 'landlord' => 2, 'admin' => 3];
    return ($hierarchy[$userRole] ?? 0) >= ($hierarchy[$role] ?? 0);
}

function auth_require_role(string $role): void
{
    if (!auth_has_role($role)) {
        http_response_code(403);
        echo 'Forbidden: requires ' . $role . ' role or higher';
        exit;
    }
}

function auth_is_landlord(int $userId): bool
{
    return auth_get_role($userId) === 'landlord' || auth_get_role($userId) === 'admin';
}

function auth_can_manage_listing(int $userId, int $listingId): bool
{
    $role = auth_get_role($userId);
    if ($role === 'admin') {
        return true;
    }
    if ($role !== 'landlord') {
        return false;
    }
    $stmt = db()->prepare('SELECT 1 FROM listings WHERE id = :id AND landlord_id = :uid LIMIT 1');
    $stmt->execute(['id' => $listingId, 'uid' => $userId]);
    return (bool) $stmt->fetchColumn();
}

function auth_can_access_social(int $userA, int $userB): bool
{
    $stmt = db()->prepare("SELECT 1 FROM connection_requests WHERE status = 'ACCEPTED' AND ((sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)) LIMIT 1");
    $stmt->execute(['a' => $userA, 'b' => $userB]);
    return (bool) $stmt->fetchColumn();
}