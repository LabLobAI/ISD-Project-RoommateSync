<?php

declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/helpers.php';
require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/layout.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $result = auth_register($fullName, $email, $city, $password, true);
    if ($result['success']) {
        header('Location: ' . rm_url('index.php') . '?registered=1');
        exit;
    }

    $errors[] = $result['message'] ?? 'Registration failed.';
}

layout_header('Create account - RoommateSync', [
    'description' => 'Create a new RoommateSync account.',
    'noindex' => true,
]);
?>

    <div class="shell auth-shell">
        <section class="auth-card">
            <p class="eyebrow">Get started</p>
            <h1>Create your account</h1>
            <p class="lede">Join the community and start finding your perfect roommate.</p>

            <?php if ($errors): ?>
                <div class="flash-message error"><?= h(implode(' ', $errors)) ?></div>
            <?php endif; ?>

            <form method="post" class="auth-form">
                <label>
                    Full name
                    <input type="text" name="full_name" required autocomplete="name">
                </label>
                <label>
                    Email
                    <input type="email" name="email" required autocomplete="email">
                </label>
                <label>
                    City
                    <input type="text" name="city" required autocomplete="address-level2">
                </label>
                <label>
                    Password
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                </label>
                <button type="submit" class="primary-button">Create account</button>
            </form>

            <p class="auth-footnote">Already have an account? <a href="<?= rm_url('auth/login.php') ?>">Sign in</a> or <a href="<?= rm_url('index.php') ?>">return home</a>.</p>
        </section>
    </div>

<?php
layout_footer();