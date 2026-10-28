<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$tripId = (int) ($_POST['trip_id'] ?? $_GET['trip_id'] ?? 0);
$trip   = $tripId ? q_one('SELECT t.*, d.driver_name FROM trips t LEFT JOIN drivers d ON d.driver_id = t.driver_id WHERE t.trip_id = ?', [$tripId]) : null;

if (is_post()) {
    csrf_check();
    if ($trip) {
        q('DELETE FROM trips WHERE trip_id = ?', [$tripId]);   // its bookings are removed too (ON DELETE CASCADE)
        flash('success', "Trip #$tripId was deleted.");
    } else {
        flash('error', 'Trip not found.');
    }
    redirect('admin_view.php?when=all');
}

$bookings = $trip ? (int) q_val("SELECT COUNT(*) FROM booking WHERE trip_id = ? AND status = 'confirmed'", [$tripId]) : 0;
$all = $trip ? [] : q_all('SELECT trip_id, destination, date, time, bus_number FROM trips ORDER BY date DESC, time');

$pageTitle = 'Delete trip';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <a href="admin_view.php" class="back-link"><?= icon('back', 'icon icon-sm') ?> Back to trips</a>
    <div class="card">
        <h1>Delete trip</h1>

        <?php if (!$trip): ?>
            <p class="muted">Choose the trip you want to delete.</p>
            <form method="get">
                <div class="form-group">
                    <label for="trip_id">Trip</label>
                    <select id="trip_id" name="trip_id" required>
                        <?php foreach ($all as $t): ?>
                            <option value="<?= (int) $t['trip_id'] ?>">#<?= (int) $t['trip_id'] ?> · <?= e($t['destination']) ?> · <?= e(fmt_date($t['date'])) ?> <?= e(fmt_time($t['time'])) ?> · <?= e($t['bus_number']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit">Continue <?= icon('arrow', 'icon icon-sm') ?></button>
            </form>
        <?php else: ?>
            <p class="muted">Please check the trip below before deleting it. This cannot be undone.</p>
            <div class="ticket">
                <dl class="details">
                    <div><dt>Trip</dt><dd>#<?= (int) $trip['trip_id'] ?></dd></div>
                    <div><dt>Destination</dt><dd><?= e($trip['destination']) ?></dd></div>
                    <div><dt>Date &amp; time</dt><dd><?= e(fmt_date($trip['date'])) ?>, <?= e(fmt_time($trip['time'])) ?></dd></div>
                    <div><dt>Bus</dt><dd><?= e($trip['bus_number']) ?></dd></div>
                    <div><dt>Driver</dt><dd><?= e($trip['driver_name'] ?? 'Not assigned') ?></dd></div>
                    <div><dt>Active bookings</dt><dd><?= $bookings ?></dd></div>
                </dl>
            </div>
            <?php if ($bookings): ?>
                <div class="alert alert-warning"><?= icon('info', 'icon icon-sm') ?>
                    <span>This trip has <?= $bookings ?> active booking(s). Deleting it will also delete those bookings.</span>
                </div>
            <?php endif; ?>
            <form method="post" data-confirm="Delete trip #<?= (int) $trip['trip_id'] ?> permanently?">
                <?= csrf_field() ?>
                <input type="hidden" name="trip_id" value="<?= (int) $trip['trip_id'] ?>">
                <div class="hero-actions">
                    <button type="submit" class="btn btn-danger-solid btn-lg"><?= icon('trash', 'icon icon-sm') ?> Delete trip</button>
                    <a href="admin_view.php" class="btn btn-ghost btn-lg">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
