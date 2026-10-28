<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$search = trim($_GET['search'] ?? '');
$when   = $_GET['when'] ?? 'upcoming';     // upcoming | past | all
$today  = date('Y-m-d');

$where = [];
$params = [];
if ($search !== '') { $where[] = '(t.destination LIKE ? OR t.bus_number LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($when === 'upcoming') { $where[] = 't.date >= ?'; $params[] = $today; }
if ($when === 'past')     { $where[] = 't.date < ?';  $params[] = $today; }

$trips = q_all(
    'SELECT t.*, d.driver_name,
            (SELECT COUNT(*) FROM booking b WHERE b.trip_id = t.trip_id AND b.status = \'confirmed\') AS bookings
       FROM trips t LEFT JOIN drivers d ON d.driver_id = t.driver_id'
    . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
    . ' ORDER BY t.date ' . ($when === 'past' ? 'DESC' : 'ASC') . ', t.time',
    $params
);

$pageTitle = 'All trips';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="page-head">
        <div>
            <h1>Trips</h1>
            <p><?= count($trips) ?> trip(s) found.</p>
        </div>
        <a href="insert.php" class="btn btn-accent"><?= icon('plus', 'icon icon-sm') ?> Insert new trip</a>
    </div>

    <div class="toolbar">
        <form method="get" class="search">
            <input type="hidden" name="when" value="<?= e($when) ?>">
            <div class="input-icon"><?= icon('search') ?>
                <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search destination or bus number">
            </div>
            <button class="btn btn-primary" type="submit">Search</button>
        </form>
        <div class="chips">
            <?php foreach (['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $k => $label): ?>
                <a href="?when=<?= $k ?>&search=<?= urlencode($search) ?>" class="chip <?= $when === $k ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr><th>ID</th><th>Bus</th><th>Destination</th><th>Date</th><th>Time</th><th>Driver</th><th>Seats</th><th>Bookings</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($trips as $t): ?>
                <tr>
                    <td>#<?= (int) $t['trip_id'] ?></td>
                    <td><?= e($t['bus_number']) ?></td>
                    <td class="cell-strong"><?= e($t['destination']) ?></td>
                    <td><?= e(fmt_date($t['date'])) ?></td>
                    <td><?= e(fmt_time($t['time'])) ?></td>
                    <td><?= $t['driver_name'] ? e($t['driver_name']) : '<span class="badge badge-warning">Not assigned</span>' ?></td>
                    <td><span class="badge <?= seats_class((int) $t['seats_available'], (int) $t['seats_total']) ?>"><?= (int) $t['seats_available'] ?> / <?= (int) $t['seats_total'] ?></span></td>
                    <td><?= (int) $t['bookings'] ?></td>
                    <td>
                        <div class="actions">
                            <a href="update.php?trip_id=<?= (int) $t['trip_id'] ?>" class="btn btn-ghost btn-sm" title="Update"><?= icon('edit', 'icon icon-sm') ?></a>
                            <a href="delete.php?trip_id=<?= (int) $t['trip_id'] ?>" class="btn btn-danger btn-sm" title="Delete"><?= icon('trash', 'icon icon-sm') ?></a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$trips): ?><tr><td colspan="9" class="text-center muted">No trips found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
