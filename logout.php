<?php
require_once __DIR__ . '/includes/functions.php';

$wasRole = current_role();

// Remove all session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
session_start();

$loginPage = ['admin' => 'admin_login.php', 'driver' => 'driver_login.php'][$wasRole] ?? 'login.php';

$pageTitle = 'Logged out';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <div class="card text-center">
        <div class="success-mark"><?= icon('logout') ?></div>
        <h1>You have logged out</h1>
        <p class="muted">You have been successfully logged out of UniBus. We look forward to serving you again soon.</p>
        <div class="hero-actions" style="justify-content:center">
            <a href="index.php" class="btn btn-primary"><?= icon('home', 'icon icon-sm') ?> Return to home</a>
            <a href="<?= e($loginPage) ?>" class="btn btn-ghost">Log in again</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
