<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$tripId = (int) ($_GET['trip_id'] ?? 0);
$trip   = $tripId ? q_one('SELECT * FROM trips WHERE trip_id = ?', [$tripId]) : null;

// No trip chosen yet: show a list to pick from
if (!$trip) {
    if ($tripId) flash('error', 'Trip not found.');
    $all = q_all('SELECT t.trip_id, t.destination, t.date, t.time, t.bus_number FROM trips t ORDER BY t.date DESC, t.time');
    $pageTitle = 'Update trip';
    $activeNav = 'trips';
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="container container-sm">
        <a href="admin_view.php" class="back-link"><?= icon('back', 'icon icon-sm') ?> Back to trips</a>
        <div class="card">
            <h1>Update trip</h1>
            <p class="muted">Choose the trip you want to update.</p>
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
        </div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$drivers = q_all('SELECT * FROM drivers ORDER BY driver_name');
$booked  = (int) $trip['seats_total'] - (int) $trip['seats_available'];
$errors  = [];

if (is_post()) {
    csrf_check();
    [$input, $errors] = trip_input($booked);
    if (!$errors) {
        q('UPDATE trips SET destination = ?, bus_number = ?, date = ?, time = ?, driver_id = ?,
                            seats_total = ?, seats_available = ?
            WHERE trip_id = ?', [
            $input['destination'], $input['bus_number'], $input['date'], $input['time'], $input['driver_id'],
            $input['seats_total'], $input['seats_total'] - $booked, $tripId,
        ]);
        flash('success', "Trip #$tripId was updated.");
        redirect('admin_view.php?when=all');
    }
    $trip = array_merge($trip, $input);
}

$pageTitle = 'Update trip';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container container-sm">
    <a href="admin_view.php" class="back-link"><?= icon('back', 'icon icon-sm') ?> Back to trips</a>
    <div class="card">
        <span class="eyebrow">Trip #<?= $tripId ?></span>
        <h1>Update trip</h1>
        <p class="muted">Change the details below and save.</p>

        <?php if ($errors): ?>
            <div class="alert alert-error"><?= icon('x', 'icon icon-sm') ?><span><?= implode('<br>', array_map('e', $errors)) ?></span></div>
        <?php endif; ?>

        <form method="post">
            <?php require __DIR__ . '/includes/trip_form.php'; ?>
            <div class="hero-actions">
                <button type="submit" class="btn btn-primary btn-lg"><?= icon('check', 'icon icon-sm') ?> Save changes</button>
                <a href="admin_view.php" class="btn btn-ghost btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
