<?php
require_once __DIR__ . '/includes/functions.php';

if (current_admin()) redirect('dashboard.php');

$error = '';
$name  = '';

if (is_post()) {
    csrf_check();
    $name     = trim($_POST['admin_name'] ?? '');
    $password = $_POST['password'] ?? '';

    $admin = q_one('SELECT * FROM admin WHERE admin_name = ?', [$name]);

    if ($admin && password_verify($password, $admin['password'])) {
        login_as('admin', ['admin_id' => (int) $admin['admin_id'], 'admin_name' => $admin['admin_name']]);
        redirect('dashboard.php');
    }
    $error = 'Incorrect username or password.';
}

$pageTitle  = 'Admin login';
$activeNav  = 'admin';
$sideTitle  = 'Transport office';
$sideText   = 'Manage trips, monitor bookings and read passenger feedback.';
$sidePoints = ['Add, update and delete trips', 'See every booking', 'Live seat statistics'];
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="auth">
        <?php require __DIR__ . '/includes/auth_side.php'; ?>
        <div class="auth-form">
            <span class="eyebrow">Administrator</span>
            <h1>Admin login</h1>
            <p class="muted">Log in to open the admin dashboard.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?> <?= e($error) ?></div><?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="admin_name">Username</label>
                    <div class="input-icon"><?= icon('shield') ?>
                        <input type="text" id="admin_name" name="admin_name" value="<?= e($name) ?>" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
            </form>

            <div class="demo-box">Demo admin: <code>admin</code> / <code>Admin@123</code></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
