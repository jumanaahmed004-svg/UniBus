<?php
require_once __DIR__ . '/includes/functions.php';

if (current_user()) redirect('welcome.php');

$errors = [];
$old = ['username' => '', 'email' => '', 'phone' => ''];

if (is_post()) {
    csrf_check();
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email'] ?? '');
    $old['phone']    = trim($_POST['phone'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirm         = $_POST['confirm'] ?? '';

    if (mb_strlen($old['username']) < 3) {
        $errors[] = 'Name must be at least 3 characters.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!preg_match('/^[0-9]{8}$/', $old['phone'])) {
        $errors[] = 'Phone number must be 8 digits (e.g. 9XXXXXXX).';
    }
    if ($problem = password_problem($password)) {
        $errors[] = $problem;
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }
    if (!$errors && q_val('SELECT 1 FROM users WHERE email = ?', [$old['email']])) {
        $errors[] = 'This email is already registered. Try logging in.';
    }

    if (!$errors) {
        q('INSERT INTO users (username, email, password, phone) VALUES (?, ?, ?, ?)', [
            $old['username'],
            $old['email'],
            password_hash($password, PASSWORD_DEFAULT),
            $old['phone'],
        ]);
        flash('success', 'Registration successful! You can now log in.');
        redirect('login.php');
    }
}

$pageTitle  = 'Create account';
$activeNav  = 'login';
$sideTitle  = 'Join UniBus today';
$sideText   = 'Create your free account and start booking your university bus seats online.';
$sidePoints = ['Free for students and staff', 'Your password is stored encrypted', 'Takes less than a minute'];
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="auth">
        <?php require __DIR__ . '/includes/auth_side.php'; ?>
        <div class="auth-form">
            <span class="eyebrow">New account</span>
            <h1>Create your account</h1>
            <p class="muted">Fill in your details to get started.</p>

            <?php if ($errors): ?>
                <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?>
                    <span><?= implode('<br>', array_map('e', $errors)) ?></span>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="username">Full name</label>
                    <input type="text" id="username" name="username" value="<?= e($old['username']) ?>" placeholder="e.g. Jumana Al-Balushi" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" placeholder="name@utas.edu.om" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?= e($old['phone']) ?>" placeholder="9XXXXXXX" maxlength="8" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm">Confirm password</label>
                        <input type="password" id="confirm" name="confirm" required>
                    </div>
                </div>
                <span class="hint mb-2">At least 8 characters, with uppercase, lowercase, a number and a special character.</span>
                <button type="submit" class="btn btn-accent btn-block btn-lg mt-2">Create account</button>
            </form>

            <p class="auth-switch">Already have an account? <a href="login.php"><b>Log in here</b></a></p>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
