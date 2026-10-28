<?php
require_once __DIR__ . '/includes/functions.php';

if (current_driver()) redirect('driver_view.php');

$error = '';
$name  = '';

if (is_post()) {
    csrf_check();
    $name  = trim($_POST['driver_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // As described in the report: drivers log in with their name and phone number.
    $driver = q_one('SELECT * FROM drivers WHERE LOWER(driver_name) = LOWER(?) AND phone = ?', [$name, $phone]);

    if ($driver) {
        login_as('driver', ['driver_id' => (int) $driver['driver_id'], 'driver_name' => $driver['driver_name']]);
        redirect('driver_view.php');
    }
    $error = 'Name or phone number is not correct.';
}

$pageTitle  = 'Driver login';
$activeNav  = 'driver';
$sideTitle  = 'Ready for the road?';
$sideText   = 'See your assigned trips and the passengers you will pick up.';
$sidePoints = ['Today\'s and upcoming trips', 'Passenger names and phone numbers', 'Pickup locations'];
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="auth">
        <?php require __DIR__ . '/includes/auth_side.php'; ?>
        <div class="auth-form">
            <span class="eyebrow">Drivers</span>
            <h1>Driver login</h1>
            <p class="muted">Use your full name and the phone number registered with the transport office.</p>

            <?php if ($error): ?><div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?> <?= e($error) ?></div><?php endif; ?>

            <form method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="driver_name">Full name</label>
                    <div class="input-icon"><?= icon('steering') ?>
                        <input type="text" id="driver_name" name="driver_name" value="<?= e($name) ?>" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone number</label>
                    <div class="input-icon"><?= icon('phone') ?>
                        <input type="password" id="phone" name="phone" inputmode="numeric" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
            </form>

            <div class="demo-box">Demo driver: <code>Salim Al-Balushi</code> / <code>95123456</code></div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
