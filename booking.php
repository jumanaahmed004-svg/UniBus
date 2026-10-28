<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

$tripId = (int) ($_GET['trip_id'] ?? 0);
$trip   = find_trip($tripId);

if ($problem = booking_problem($trip, $user['user_id'])) {
    flash('warning', $problem);
    redirect('view_trip.php');
}

$profile = q_one('SELECT username, email, phone FROM users WHERE user_id = ?', [$user['user_id']]);
$pending = $_SESSION['pending_booking'] ?? null;
$old     = ($pending && $pending['trip_id'] === $tripId) ? $pending : ['seats' => 1, 'pickup_location' => ''];
$maxSeats = min(MAX_SEATS_PER_BOOKING, (int) $trip['seats_available']);

$pageTitle = 'Book trip';
$activeNav = 'trips';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <a href="view_trip.php" class="back-link"><?= icon('back', 'icon icon-sm') ?> Back to trips</a>

    <div class="steps">
        <div class="step done">Choose trip</div>
        <div class="step current">Your details</div>
        <div class="step">Payment</div>
        <div class="step">Done</div>
    </div>

    <div class="layout-side">
        <div class="card">
            <h2 class="card-title"><?= icon('user') ?> Passenger details</h2>
            <form method="post" action="confirm_booking.php?trip_id=<?= $tripId ?>">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Your name</label>
                        <input type="text" value="<?= e($profile['username']) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" value="<?= e($profile['email']) ?>" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label for="seats">Number of seats</label>
                    <input type="number" id="seats" name="seats" min="1" max="<?= $maxSeats ?>"
                           value="<?= (int) $old['seats'] ?>" data-seat-price="<?= SEAT_PRICE ?>" required>
                    <span class="hint">Up to <?= $maxSeats ?> seat(s) · <?= e(fmt_price(SEAT_PRICE)) ?> per seat</span>
                </div>
                <div class="form-group">
                    <label for="pickup_location">Your exact pickup location in <?= e($trip['destination']) ?></label>
                    <div class="input-icon"><?= icon('pin') ?>
                        <input type="text" id="pickup_location" name="pickup_location" maxlength="150"
                               value="<?= e($old['pickup_location']) ?>" placeholder="e.g. Al Hambar, near the mosque" required>
                    </div>
                </div>
                <div class="note mb-2"><?= icon('phone', 'icon icon-sm') ?>
                    <span>Tip: send your home location to the driver on WhatsApp after booking.</span>
                </div>
                <div class="summary-row total"><span>Total</span><span id="live-total"><?= e(fmt_price(SEAT_PRICE)) ?></span></div>
                <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">Continue to payment <?= icon('arrow', 'icon icon-sm') ?></button>
            </form>
        </div>

        <aside class="card">
            <h2 class="card-title"><?= icon('bus') ?> Trip summary</h2>
            <p class="trip-dest"><?= e($trip['destination']) ?></p>
            <p class="trip-dir"><?= e(trip_direction($trip['time'])) ?></p>
            <dl class="details mt-2">
                <div><dt>Date</dt><dd><?= e(fmt_date($trip['date'])) ?></dd></div>
                <div><dt>Time</dt><dd><?= e(fmt_time($trip['time'])) ?></dd></div>
                <div><dt>Bus</dt><dd><?= e($trip['bus_number']) ?></dd></div>
                <div><dt>Seats left</dt><dd><?= (int) $trip['seats_available'] ?> / <?= (int) $trip['seats_total'] ?></dd></div>
            </dl>
            <hr class="divider">
            <dl class="details">
                <div><dt>Driver</dt><dd><?= e($trip['driver_name'] ?? 'To be assigned') ?></dd></div>
                <div><dt>Driver phone</dt><dd><?= e($trip['driver_phone'] ?? '-') ?></dd></div>
            </dl>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
