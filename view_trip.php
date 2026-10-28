<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$search = trim($_GET['search'] ?? '');
$today  = date('Y-m-d');

$sql    = 'SELECT t.*, d.driver_name FROM trips t LEFT JOIN drivers d ON d.driver_id = t.driver_id WHERE t.date >= ?';
$params = [$today];
if ($search !== '') {
    $sql     .= ' AND t.destination LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql  .= ' ORDER BY t.date, t.time';
$trips = q_all($sql, $params);

// Trips this user already booked (to show "Booked" instead of "Book")
$booked = array_column(
    q_all("SELECT trip_id FROM booking WHERE user_id = ? AND status = 'confirmed'", [$user['user_id']]),
    'trip_id'
);
$destinations = array_column(q_all('SELECT DISTINCT destination FROM trips ORDER BY destination'), 'destination');

$pageTitle = 'Available trips';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Step 1 of 3</span>
            <h1>Available trips</h1>
            <p>Choose a trip to book your seat.</p>
        </div>
    </div>

    <div class="schedule">
        <div class="schedule-item">
            <div class="feature-icon"><?= icon('home', 'icon') ?></div>
            <div><strong>8:00 AM &amp; 10:00 AM</strong><span>From home to university</span></div>
        </div>
        <div class="schedule-item">
            <div class="feature-icon blue"><?= icon('bus', 'icon') ?></div>
            <div><strong>12:00, 2:00, 4:00 &amp; 6:00 PM</strong><span>From university to home</span></div>
        </div>
    </div>

    <div class="toolbar">
        <form method="get" class="search">
            <div class="input-icon"><?= icon('search') ?>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search destination (e.g. Sohar, Shinas)">
            </div>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <div class="chips">
            <a href="view_trip.php" class="chip <?= $search === '' ? 'active' : '' ?>">All</a>
            <?php foreach ($destinations as $d): ?>
                <a href="?search=<?= urlencode($d) ?>" class="chip <?= strcasecmp($search, $d) === 0 ? 'active' : '' ?>"><?= e($d) ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (!$trips): ?>
        <div class="card empty">
            <div class="feature-icon"><?= icon('search', 'icon icon-lg') ?></div>
            <h3>No trips found</h3>
            <p>There are no upcoming trips<?= $search ? ' for “' . e($search) . '”' : '' ?>.</p>
            <a href="view_trip.php" class="btn btn-ghost">Show all trips</a>
        </div>
    <?php else: ?>
        <div class="trip-list">
            <?php foreach ($trips as $t):
                $departed = trip_departed($t);
                $full     = (int) $t['seats_available'] <= 0;
                $mine     = in_array($t['trip_id'], $booked);
            ?>
                <article class="card trip <?= $departed ? 'departed' : '' ?>">
                    <div class="trip-top">
                        <div>
                            <h3 class="trip-dest"><?= e($t['destination']) ?></h3>
                            <span class="trip-dir"><?= e(trip_direction($t['time'])) ?></span>
                        </div>
                        <span class="badge <?= seats_class((int) $t['seats_available'], (int) $t['seats_total']) ?>">
                            <?= (int) $t['seats_available'] ?> / <?= (int) $t['seats_total'] ?> seats
                        </span>
                    </div>
                    <div class="trip-meta">
                        <span><?= icon('calendar') ?> <?= e(fmt_date($t['date'])) ?></span>
                        <span><?= icon('clock') ?> <?= e(fmt_time($t['time'])) ?></span>
                        <span><?= icon('bus') ?> <?= e($t['bus_number']) ?></span>
                        <span><?= icon('steering') ?> <?= e($t['driver_name'] ?? 'To be assigned') ?></span>
                    </div>
                    <div class="trip-foot">
                        <span class="muted small">Trip #<?= (int) $t['trip_id'] ?> · <?= e(fmt_price(SEAT_PRICE)) ?>/seat</span>
                        <?php if ($mine): ?>
                            <a href="history.php" class="btn btn-ghost btn-sm"><?= icon('check', 'icon icon-sm') ?> Booked</a>
                        <?php elseif ($departed): ?>
                            <span class="badge badge-neutral">Departed</span>
                        <?php elseif ($full): ?>
                            <span class="badge badge-danger">Full</span>
                        <?php else: ?>
                            <a href="booking.php?trip_id=<?= (int) $t['trip_id'] ?>" class="btn btn-primary btn-sm">Book now <?= icon('arrow', 'icon icon-sm') ?></a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
