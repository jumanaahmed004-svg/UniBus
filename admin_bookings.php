<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$status = $_GET['status'] ?? 'all';    // all | confirmed | cancelled
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
if (in_array($status, ['confirmed', 'cancelled'], true)) { $where[] = 'b.status = ?'; $params[] = $status; }
if ($search !== '') {
    $where[] = '(u.username LIKE ? OR u.email LIKE ? OR t.destination LIKE ?)';
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$rows = q_all(
    'SELECT b.*, u.username, u.email, u.phone, t.destination, t.date, t.time, t.bus_number
       FROM booking b JOIN users u ON u.user_id = b.user_id JOIN trips t ON t.trip_id = b.trip_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY b.booking_id DESC',
    $params
);

$pageTitle = 'Bookings';
$activeNav = 'bookings';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <h1>Bookings</h1>
            <p><?= count($rows) ?> booking(s) found.</p>
        </div>
    </div>

    <div class="toolbar">
        <form method="get" class="search">
            <input type="hidden" name="status" value="<?= e($status) ?>">
            <div class="input-icon"><?= icon('search') ?>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search passenger, email or destination">
            </div>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <div class="chips">
            <?php foreach (['all' => 'All', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $k => $label): ?>
                <a href="?status=<?= $k ?>&search=<?= urlencode($search) ?>" class="chip <?= $status === $k ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>#</th><th>Passenger</th><th>Phone</th><th>Trip</th><th>Date &amp; time</th><th>Seats</th><th>Pickup</th><th>Payment</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td>#<?= (int) $r['booking_id'] ?></td>
                    <td><span class="cell-strong"><?= e($r['username']) ?></span><br><span class="muted small"><?= e($r['email']) ?></span></td>
                    <td><?= e($r['phone']) ?></td>
                    <td><?= e($r['destination']) ?><br><span class="muted small"><?= e($r['bus_number']) ?> · trip #<?= (int) $r['trip_id'] ?></span></td>
                    <td><?= e(fmt_date($r['date'])) ?><br><span class="muted small"><?= e(fmt_time($r['time'])) ?></span></td>
                    <td><?= (int) $r['seats'] ?></td>
                    <td class="wrap"><?= e($r['pickup_location'] ?: '-') ?></td>
                    <td><?= $r['payment_method'] === 'card' ? 'Card' : 'Cash' ?> · <?= e(fmt_price((float) $r['total_price'])) ?></td>
                    <td><span class="badge <?= $r['status'] === 'confirmed' ? 'badge-success' : 'badge-danger' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="9" class="text-center muted">No bookings found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
