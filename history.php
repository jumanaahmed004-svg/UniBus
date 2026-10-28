<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$rows = q_all(
    'SELECT b.*, t.destination, t.date, t.time, t.bus_number,
            d.driver_name, d.phone AS driver_phone
       FROM booking b
       JOIN trips t        ON t.trip_id = b.trip_id
       LEFT JOIN drivers d ON d.driver_id = t.driver_id
      WHERE b.user_id = ?
      ORDER BY t.date DESC, t.time DESC',
    [$user['user_id']]
);

$pageTitle = 'My bookings';
$activeNav = 'history';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <h1>My bookings</h1>
            <p>Your previous and upcoming trips.</p>
        </div>
        <a href="view_trip.php" class="btn btn-primary"><?= icon('plus', 'icon icon-sm') ?> New booking</a>
    </div>

    <?php if (!$rows): ?>
        <div class="card empty">
            <div class="feature-icon"><?= icon('ticket', 'icon icon-lg') ?></div>
            <h3>No bookings yet</h3>
            <p>When you book a trip it will appear here.</p>
            <a href="view_trip.php" class="btn btn-primary">Browse trips</a>
        </div>
    <?php endif; ?>

    <?php foreach ($rows as $r):
        $departed  = trip_departed($r);
        $cancelled = $r['status'] === 'cancelled';
        if ($cancelled)      { $badge = ['badge-danger', 'Cancelled']; }
        elseif ($departed)   { $badge = ['badge-neutral', 'Completed']; }
        else                 { $badge = ['badge-success', 'Upcoming']; }
        $ts = strtotime($r['date']);
    ?>
        <div class="card booking-card <?= $cancelled ? 'cancelled' : '' ?>">
            <div class="date-tile">
                <span class="d"><?= date('j', $ts) ?></span>
                <span class="m"><?= date('M', $ts) ?></span>
            </div>
            <div class="booking-info">
                <h3><?= e($r['destination']) ?> <span class="badge <?= $badge[0] ?>"><?= $badge[1] ?></span></h3>
                <div class="meta-line">
                    <span><?= icon('clock') ?> <?= e(fmt_time($r['time'])) ?></span>
                    <span><?= icon('bus') ?> <?= e($r['bus_number']) ?></span>
                    <span><?= icon('seat') ?> <?= (int) $r['seats'] ?> seat(s)</span>
                    <span><?= icon('pin') ?> <?= e($r['pickup_location'] ?: '-') ?></span>
                </div>
                <div class="meta-line mt-1">
                    <span><?= icon('steering') ?> <?= e($r['driver_name'] ?? 'To be assigned') ?></span>
                    <span><?= icon('phone') ?> <?= e($r['driver_phone'] ?? '-') ?></span>
                    <span><?= icon('ticket') ?> Booking #<?= (int) $r['booking_id'] ?> · booked <?= e(fmt_date($r['booking_date'])) ?></span>
                </div>
            </div>
            <div class="booking-actions">
                <?php if (!$cancelled && !$departed): ?>
                    <form method="post" action="cancel_booking.php" data-confirm="Cancel this booking? Your seats will be released.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="booking_id" value="<?= (int) $r['booking_id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm"><?= icon('x', 'icon icon-sm') ?> Cancel</button>
                    </form>
                <?php else: ?>
                    <span class="muted small"><?= e(fmt_price((float) $r['total_price'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
