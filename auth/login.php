<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/layout.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $remember = isset($_POST['remember']);

    $result = auth_login($email, $password, $remember);
    if ($result['success']) {
        header('Location: ../index.php?signed_in=1');
        exit;
    }

    $errors[] = $result['message'] ?? 'Login failed.';
}

layout_header('Sign in - RoommateSync', [
    'description' => 'Sign in to your RoommateSync account.',
    'noindex' => true,
]);
?>

    <main class="shell auth-shell">
        <section class="auth-card">
            <p class="eyebrow">RoommateSync</p>
            <h1>Sign in</h1>
            <p class="lede">Use your account to keep your session active across visits with a remember-me cookie.</p>

            <?php if ($errors): ?>
                <div class="flash-message error"><?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <label>
                    Email
                    <input type="email" name="email" required autocomplete="email">
                </label>
                <label>
                    Password
                    <input type="password" name="password" required autocomplete="current-password">
                </label>
                <label class="checkbox-row">
                    <input type="checkbox" name="remember" checked>
                    Remember me on this device
                </label>
                <button type="submit" class="primary-button">Sign in</button>
            </form>

            <p class="auth-footnote">New here? <a href="register.php">Create an account</a> or <a href="../index.php">return home</a>.</p>
        </section>
    </main>

<?php
layout_footer();