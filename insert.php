<?php
require_once __DIR__ . '/includes/functions.php';
$admin = require_admin();

$drivers = q_all('SELECT * FROM drivers ORDER BY driver_name');
$booked  = 0;
$errors  = [];
$trip    = ['destination' => '', 'bus_number' => '', 'date' => date('Y-m-d', strtotime('+1 day')), 'time' => '08:00', 'driver_id' => null, 'seats_total' => 30];

if (is_post()) {
    csrf_check();
    [$trip, $errors] = trip_input();
    if (!$errors) {
        q('INSERT INTO trips (admin_id, bus_number, date, destination, driver_id, seats_available, seats_total, time)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [
            $admin['admin_id'], $trip['bus_number'], $trip['date'], $trip['destination'],
            $trip['driver_id'], $trip['seats_total'], $trip['seats_total'], $trip['time'],
        ]);
        flash('success', 'Trip #' . db()->lastInsertId() . ' to ' . $trip['destination'] . ' was added.');
        redirect('admin_view.php');
    }
}

$pageTitle = 'Insert trip';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <a href="admin_view.php" class="back-link"><?= icon('back', 'icon icon-sm') ?> Back to trips</a>
    <div class="card">
        <span class="eyebrow">New trip</span>
        <h1>Insert new trip</h1>
        <p class="muted">Fill in the form to add a new trip to the UniBus schedule.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?><span><?= implode('<br>', array_map('e', $errors)) ?></span></div>
        <?php endif; ?>

        <form method="post">
            <?php require __DIR__ . '/includes/trip_form.php'; ?>
            <button type="submit" class="btn btn-accent btn-lg"><?= icon('plus', 'icon icon-sm') ?> Insert trip</button>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
