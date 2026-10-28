<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) redirect('welcome.php');

$error = '';
$email = '';

if (is_post()) {
    csrf_check();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = q_one('SELECT * FROM users WHERE email = ?', [$email]);

    if ($user && password_verify($password, $user['password'])) {
        login_as('user', [
            'user_id'  => (int) $user['user_id'],
            'email'    => $user['email'],
            'username' => $user['username'],
        ]);
        redirect('welcome.php');
    }
    $error = 'Invalid email or password.';
}

$pageTitle  = 'Log in';
$activeNav  = 'login';
$sideTitle  = 'Welcome back!';
$sideText   = 'Log in to book seats and manage your university bus trips.';
$sidePoints = ['Book seats in three steps', 'See driver contact details', 'Cancel when plans change'];
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="auth">
        <?php require __DIR__ . '/includes/auth_side.php'; ?>
        <div class="auth-form">
            <span class="eyebrow">Students &amp; staff</span>
            <h1>Log in to UniBus</h1>
            <p class="muted">Enter your university email and password.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?> <?= e($error) ?></div><?php endif; ?>

            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-icon"><?= icon('user') ?>
                        <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="name@utas.edu.om" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon"><?= icon('shield') ?>
                        <input type="password" id="password" name="password" placeholder="Your password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
            </form>

            <p class="auth-switch">Don't have an account? <a href="register.php"><b>Register here</b></a></p>

            <div class="demo-box">Demo account: <code>student@unibus.test</code> / <code>Student@123</code></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
