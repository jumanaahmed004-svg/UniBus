<?php
require_once __DIR__ . '/includes/functions.php';
$admin = require_admin();
$today = date('Y-m-d');

$s = [
    'trips'     => (int) q_val('SELECT COUNT(*) FROM trips'),
    'upcoming'  => (int) q_val('SELECT COUNT(*) FROM trips WHERE date >= ?', [$today]),
    'bookings'  => (int) q_val("SELECT COUNT(*) FROM booking WHERE status = 'confirmed'"),
    'users'     => (int) q_val('SELECT COUNT(*) FROM users'),
    'feedback'  => (int) q_val('SELECT COUNT(*) FROM feedback'),
    'revenue'   => (float) q_val("SELECT COALESCE(SUM(total_price),0) FROM booking WHERE status = 'confirmed'"),
];
$seat = q_one('SELECT COALESCE(SUM(seats_total),0) AS total, COALESCE(SUM(seats_total - seats_available),0) AS used FROM trips WHERE date >= ?', [$today]);
$occupancy = $seat['total'] ? round($seat['used'] * 100 / $seat['total']) : 0;

$byDest = q_all(
    "SELECT t.destination, COALESCE(SUM(CASE WHEN b.status='confirmed' THEN b.seats END),0) AS seats
       FROM trips t LEFT JOIN booking b ON b.trip_id = t.trip_id
      GROUP BY t.destination ORDER BY seats DESC"
);
$maxDest = max(1, ...array_map('intval', array_column($byDest, 'seats') ?: [1]));

$recent = q_all(
    'SELECT b.booking_id, b.seats, b.status, b.booking_date, u.username, t.destination, t.date
       FROM booking b JOIN users u ON u.user_id = b.user_id JOIN trips t ON t.trip_id = b.trip_id
      ORDER BY b.booking_id DESC LIMIT 5'
);

$pageTitle = 'Admin dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <span class="eyebrow">Admin</span>
            <h1>Welcome, <?= e($admin['admin_name']) ?></h1>
            <p>Here is what is happening with UniBus today.</p>
        </div>
        <a href="insert.php" class="btn btn-accent"><?= icon('plus', 'icon icon-sm') ?> Insert new trip</a>
    </div>

    <div class="grid grid-4 mb-3">
        <div class="card stat">
            <div class="stat-icon"><?= icon('bus', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $s['upcoming'] ?></div><div class="stat-label">Upcoming trips</div></div>
        </div>
        <div class="card stat">
            <div class="stat-icon orange"><?= icon('ticket', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $s['bookings'] ?></div><div class="stat-label">Active bookings</div></div>
        </div>
        <div class="card stat">
            <div class="stat-icon green"><?= icon('users', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $s['users'] ?></div><div class="stat-label">Registered users</div></div>
        </div>
        <div class="card stat">
            <div class="stat-icon orange"><?= icon('wallet', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= number_format($s['revenue'], 0) ?></div><div class="stat-label">Revenue (OMR)</div></div>
        </div>
    </div>

    <div class="grid grid-2 mb-3">
        <div class="card">
            <h2 class="card-title"><?= icon('seat') ?> Seat occupancy (upcoming trips)</h2>
            <div class="stat-value"><?= $occupancy ?>%</div>
            <div class="progress"><span style="width: <?= $occupancy ?>%"></span></div>
            <p class="muted small mt-1"><?= (int) $seat['used'] ?> of <?= (int) $seat['total'] ?> seats booked</p>
            <hr class="divider">
            <h3 class="small muted">Seats booked by destination</h3>
            <?php foreach ($byDest as $d): $pct = round($d['seats'] * 100 / $maxDest); ?>
                <div class="summary-row small"><span><?= e($d['destination']) ?></span><span><?= (int) $d['seats'] ?></span></div>
                <div class="progress mb-2" style="margin-top:0"><span style="width: <?= $pct ?>%"></span></div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 class="card-title"><?= icon('chart') ?> Manage the system</h2>
            <div class="grid grid-2">
                <a href="insert.php" class="card feature"><div class="feature-icon"><?= icon('plus') ?></div><h3>Insert trip</h3><p>Add a new trip.</p></a>
                <a href="admin_view.php" class="card feature"><div class="feature-icon blue"><?= icon('bus') ?></div><h3>View trips</h3><p>All trips &amp; seats.</p></a>
                <a href="update.php" class="card feature"><div class="feature-icon blue"><?= icon('edit') ?></div><h3>Update trip</h3><p>Change trip details.</p></a>
                <a href="delete.php" class="card feature"><div class="feature-icon"><?= icon('trash') ?></div><h3>Delete trip</h3><p>Remove old trips.</p></a>
                <a href="admin_bookings.php" class="card feature"><div class="feature-icon"><?= icon('ticket') ?></div><h3>Bookings</h3><p>Who booked what.</p></a>
                <a href="admin_feedback.php" class="card feature"><div class="feature-icon blue"><?= icon('message') ?></div><h3>Feedback</h3><p><?= $s['feedback'] ?> message(s).</p></a>
                <a href="admin_drivers.php" class="card feature"><div class="feature-icon blue"><?= icon('steering') ?></div><h3>Drivers</h3><p>Add, edit or remove.</p></a>
                <a href="admin_admins.php" class="card feature"><div class="feature-icon"><?= icon('shield') ?></div><h3>Admins</h3><p>Accounts &amp; password.</p></a>
            </div>
        </div>
    </div>

    <div class="page-head"><h2 class="mb-0">Recent bookings</h2><a href="admin_bookings.php" class="btn btn-ghost btn-sm">View all</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>#</th><th>Passenger</th><th>Destination</th><th>Trip date</th><th>Seats</th><th>Status</th><th>Booked on</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
                <tr>
                    <td>#<?= (int) $r['booking_id'] ?></td>
                    <td class="cell-strong"><?= e($r['username']) ?></td>
                    <td><?= e($r['destination']) ?></td>
                    <td><?= e(fmt_date($r['date'])) ?></td>
                    <td><?= (int) $r['seats'] ?></td>
                    <td><span class="badge <?= $r['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                    <td><?= e(fmt_date($r['booking_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="7" class="text-center muted">No bookings yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
