<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$b = q_one(
    'SELECT b.*, t.destination, t.date, t.time, t.bus_number, d.driver_name, d.phone AS driver_phone
       FROM booking b JOIN trips t ON t.trip_id = b.trip_id
       LEFT JOIN drivers d ON d.driver_id = t.driver_id
      WHERE b.booking_id = ? AND b.user_id = ?',
    [(int) ($_GET['id'] ?? 0), $user['user_id']]
);
if (!$b) redirect('history.php');

$pageTitle = 'Booking confirmed';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <div class="steps">
        <div class="step done">Choose trip</div>
        <div class="step done">Your details</div>
        <div class="step done">Payment</div>
        <div class="step current">Done</div>
    </div>

    <div class="card text-center">
        <div class="success-mark"><?= icon('check') ?></div>
        <h1>Thank you, <?= e($user['username']) ?>!</h1>
        <p class="muted">Your booking is confirmed. Be ready, the driver will contact you soon.</p>

        <div class="ticket">
            <dl class="details">
                <div><dt>Booking ID</dt><dd>#<?= (int) $b['booking_id'] ?></dd></div>
                <div><dt>Destination</dt><dd><?= e($b['destination']) ?></dd></div>
                <div><dt>Date</dt><dd><?= e(fmt_date($b['date'])) ?></dd></div>
                <div><dt>Time</dt><dd><?= e(fmt_time($b['time'])) ?></dd></div>
                <div><dt>Bus</dt><dd><?= e($b['bus_number']) ?></dd></div>
                <div><dt>Seats</dt><dd><?= (int) $b['seats'] ?></dd></div>
                <div><dt>Driver</dt><dd><?= e($b['driver_name'] ?? 'To be assigned') ?></dd></div>
                <div><dt>Driver phone</dt><dd><?= e($b['driver_phone'] ?? '-') ?></dd></div>
                <div><dt>Payment</dt><dd><?= $b['payment_method'] === 'card' ? 'Card / Apple Pay' : 'Cash on the bus' ?></dd></div>
                <div><dt>Total</dt><dd><?= e(fmt_price((float) $b['total_price'])) ?></dd></div>
            </dl>
        </div>

        <p>We hope you enjoy your trip with UniBus! 🚌</p>
        <div class="hero-actions" style="justify-content:center">
            <a href="history.php" class="btn btn-primary"><?= icon('ticket', 'icon icon-sm') ?> My bookings</a>
            <a href="view_trip.php" class="btn btn-ghost">Book another trip</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
