<?php
require_once __DIR__ . '/includes/functions.php';
$driver = require_driver();

$showPast = ($_GET['when'] ?? '') === 'past';
$today    = date('Y-m-d');

$trips = q_all(
    'SELECT * FROM trips WHERE driver_id = ? AND date ' . ($showPast ? '<' : '>=') . ' ?
      ORDER BY date ' . ($showPast ? 'DESC' : 'ASC') . ', time',
    [$driver['driver_id'], $today]
);

// Passengers for all these trips in one query
$passengers = [];
if ($trips) {
    $ids = array_column($trips, 'trip_id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    foreach (q_all(
        "SELECT b.trip_id, b.seats, b.pickup_location, u.username, u.phone
           FROM booking b JOIN users u ON u.user_id = b.user_id
          WHERE b.status = 'confirmed' AND b.trip_id IN ($in)
          ORDER BY u.username",
        $ids
    ) as $p) {
        $passengers[$p['trip_id']][] = $p;
    }
}
$todayCount = count(array_filter($trips, fn($t) => $t['date'] === $today));

$pageTitle = 'My trips';
$activeNav = 'driver';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="banner">
        <div>
            <h1>Hello, <?= e($driver['driver_name']) ?></h1>
            <p><?= $showPast ? 'Your completed trips.' : ($todayCount ? "You have $todayCount trip(s) today. Drive safe!" : 'No trips today. Here are your upcoming trips.') ?></p>
        </div>
        <div class="chips">
            <a href="driver_view.php" class="chip <?= !$showPast ? 'active' : '' ?>">Upcoming</a>
            <a href="driver_view.php?when=past" class="chip <?= $showPast ? 'active' : '' ?>">Past</a>
        </div>
    </div>

    <?php if (!$trips): ?>
        <div class="card empty">
            <div class="feature-icon"><?= icon('bus', 'icon icon-lg') ?></div>
            <h3>No trips</h3>
            <p>You have no <?= $showPast ? 'past' : 'upcoming' ?> trips assigned.</p>
        </div>
    <?php endif; ?>

    <div class="grid grid-2">
    <?php foreach ($trips as $t):
        $list   = $passengers[$t['trip_id']] ?? [];
        $booked = (int) $t['seats_total'] - (int) $t['seats_available'];
    ?>
        <div class="card">
            <div class="trip-top">
                <div>
                    <h3 class="trip-dest"><?= e($t['destination']) ?></h3>
                    <span class="trip-dir"><?= e(trip_direction($t['time'])) ?></span>
                </div>
                <?php if ($t['date'] === $today): ?><span class="badge badge-warning">Today</span><?php endif; ?>
            </div>
            <div class="trip-meta mt-2">
                <span><?= icon('calendar') ?> <?= e(fmt_date($t['date'])) ?></span>
                <span><?= icon('clock') ?> <?= e(fmt_time($t['time'])) ?></span>
                <span><?= icon('bus') ?> <?= e($t['bus_number']) ?></span>
                <span><?= icon('seat') ?> <?= $booked ?> / <?= (int) $t['seats_total'] ?> seats booked</span>
            </div>
            <hr class="divider">
            <h4 class="small muted mb-0">PASSENGERS (<?= count($list) ?>)</h4>
            <?php if (!$list): ?>
                <p class="muted small mt-1 mb-0">No passengers booked yet.</p>
            <?php endif; ?>
            <?php foreach ($list as $p): ?>
                <div class="passenger">
                    <div class="avatar"><?= e(mb_strtoupper(mb_substr($p['username'], 0, 1))) ?></div>
                    <div class="passenger-info">
                        <strong><?= e($p['username']) ?> <span class="badge badge-info"><?= (int) $p['seats'] ?> seat(s)</span></strong>
                        <small><?= icon('pin', 'icon icon-sm') ?> <?= e($p['pickup_location'] ?: '-') ?></small>
                    </div>
                    <a href="tel:<?= e($p['phone']) ?>" class="btn btn-ghost btn-sm"><?= icon('phone', 'icon icon-sm') ?> <?= e($p['phone']) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
