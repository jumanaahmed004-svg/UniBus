<?php
/**
 * Booking step 2 -> 3.
 *  - POST from booking.php      : validate and show the summary + payment choice
 *  - POST with action=pay       : save the booking (in a transaction) and go to thanks.php
 */
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$tripId = (int) ($_GET['trip_id'] ?? 0);
$trip   = find_trip($tripId);

if (!is_post()) {
    redirect($trip ? 'booking.php?trip_id=' . $tripId : 'view_trip.php');
}
csrf_check();

/* ---------- Final step: pay & save ---------- */
if (($_POST['action'] ?? '') === 'pay') {
    $pending = $_SESSION['pending_booking'] ?? null;
    $method  = $_POST['payment_method'] ?? '';

    if (!$pending || $pending['trip_id'] !== $tripId) {
        flash('warning', 'Your booking session expired. Please start again.');
        redirect('view_trip.php');
    }
    if (!in_array($method, ['cash', 'card'], true)) {
        flash('error', 'Please choose a payment method.');
        redirect('booking.php?trip_id=' . $tripId);
    }
    if ($problem = booking_problem($trip, $user['user_id'], $pending['seats'])) {
        unset($_SESSION['pending_booking']);
        flash('error', $problem);
        redirect('view_trip.php');
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        // Take the seats only if they are still free (safe if two people book at once)
        $upd = q('UPDATE trips SET seats_available = seats_available - ? WHERE trip_id = ? AND seats_available >= ?',
                 [$pending['seats'], $tripId, $pending['seats']]);
        if ($upd->rowCount() !== 1) {
            throw new RuntimeException('Not enough seats left on this trip.');
        }
        q('INSERT INTO booking (booking_date, user_id, trip_id, seats, pickup_location, payment_method, total_price)
           VALUES (?, ?, ?, ?, ?, ?, ?)', [
            date('Y-m-d'), $user['user_id'], $tripId, $pending['seats'],
            $pending['pickup_location'], $method, $pending['seats'] * SEAT_PRICE,
        ]);
        $bookingId = (int) $pdo->lastInsertId();
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        flash('error', 'Booking failed: ' . $ex->getMessage());
        redirect('view_trip.php');
    }

    unset($_SESSION['pending_booking']);
    redirect('thanks.php?id=' . $bookingId);
}

/* ---------- Step 2: validate details and show summary ---------- */
$seats    = (int) ($_POST['seats'] ?? 0);
$location = trim($_POST['pickup_location'] ?? '');

$problem = booking_problem($trip, $user['user_id'], $seats);
if (!$problem && ($location === '' || mb_strlen($location) > 150)) {
    $problem = 'Please enter your pickup location.';
}
if ($problem) {
    flash('error', $problem);
    redirect($trip ? 'booking.php?trip_id=' . $tripId : 'view_trip.php');
}

$_SESSION['pending_booking'] = [
    'trip_id'         => $tripId,
    'seats'           => $seats,
    'pickup_location' => $location,
];
$total = $seats * SEAT_PRICE;

$pageTitle = 'Confirm booking';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <a href="booking.php?trip_id=<?= $tripId ?>" class="back-link"><?= icon('back', 'icon icon-sm') ?> Edit details</a>

    <div class="steps">
        <div class="step done">Choose trip</div>
        <div class="step done">Your details</div>
        <div class="step current">Payment</div>
        <div class="step">Done</div>
    </div>

    <div class="layout-side">
        <div>
            <div class="card">
                <h2 class="card-title"><?= icon('bus') ?> Trip details</h2>
                <dl class="details">
                    <div><dt>Destination</dt><dd><?= e($trip['destination']) ?></dd></div>
                    <div><dt>Direction</dt><dd><?= e(trip_direction($trip['time'])) ?></dd></div>
                    <div><dt>Date</dt><dd><?= e(fmt_date($trip['date'])) ?></dd></div>
                    <div><dt>Time</dt><dd><?= e(fmt_time($trip['time'])) ?></dd></div>
                    <div><dt>Bus number</dt><dd><?= e($trip['bus_number']) ?></dd></div>
                    <div><dt>Driver</dt><dd><?= e($trip['driver_name'] ?? 'To be assigned') ?></dd></div>
                </dl>
            </div>
            <div class="card">
                <h2 class="card-title"><?= icon('user') ?> Passenger</h2>
                <dl class="details">
                    <div><dt>Name</dt><dd><?= e($user['username']) ?></dd></div>
                    <div><dt>Email</dt><dd><?= e($user['email']) ?></dd></div>
                    <div><dt>Pickup location</dt><dd><?= e($location) ?></dd></div>
                    <div><dt>Seats</dt><dd><?= $seats ?></dd></div>
                </dl>
            </div>
        </div>

        <aside class="card">
            <h2 class="card-title"><?= icon('wallet') ?> Payment</h2>
            <div class="summary-row"><span><?= $seats ?> seat(s) × <?= e(fmt_price(SEAT_PRICE)) ?></span><span><?= e(fmt_price($total)) ?></span></div>
            <div class="summary-row total"><span>Total</span><span><?= e(fmt_price($total)) ?></span></div>

            <form method="post" class="mt-3">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="pay">
                <label>Payment method</label>
                <div class="pay-options">
                    <label class="pay-option selected">
                        <input type="radio" name="payment_method" value="cash" checked>
                        <span class="feature-icon"><?= icon('cash') ?></span>
                        <span><strong>Cash</strong><small>Pay on the bus</small></span>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="payment_method" value="card">
                        <span class="feature-icon blue"><?= icon('card') ?></span>
                        <span><strong>Card / Apple Pay</strong><small>Demo only</small></span>
                    </label>
                </div>
                <button type="submit" class="btn btn-accent btn-block btn-lg"><?= icon('check') ?> Confirm booking</button>
            </form>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
