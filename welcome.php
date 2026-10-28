<?php
require_once __DIR__ . '/includes/functions.php';
$user = require_user();
$today = date('Y-m-d');

$nextTrip = q_one(
    "SELECT b.booking_id, b.seats, t.*
       FROM booking b JOIN trips t ON t.trip_id = b.trip_id
      WHERE b.user_id = ? AND b.status = 'confirmed' AND t.date >= ?
      ORDER BY t.date, t.time LIMIT 1",
    [$user['user_id'], $today]
);
$upcoming = (int) q_val(
    "SELECT COUNT(*) FROM booking b JOIN trips t ON t.trip_id = b.trip_id
      WHERE b.user_id = ? AND b.status = 'confirmed' AND t.date >= ?",
    [$user['user_id'], $today]
);
$total = (int) q_val("SELECT COUNT(*) FROM booking WHERE user_id = ? AND status = 'confirmed'", [$user['user_id']]);
$available = (int) q_val('SELECT COUNT(*) FROM trips WHERE date >= ? AND seats_available > 0', [$today]);

$pageTitle = 'Welcome';
$activeNav = 'welcome';
require __DIR__ . '/includes/header.php';
?>
<div class="container">
    <div class="banner">
        <div>
            <h1>Welcome, <?= e($user['username']) ?> 👋</h1>
            <p>Glad to have you on board. Where are we going today?</p>
        </div>
        <a href="view_trip.php" class="btn btn-accent btn-lg"><?= icon('bus') ?> Book a trip</a>
    </div>

    <div class="grid grid-3 mb-3">
        <div class="card stat">
            <div class="stat-icon"><?= icon('ticket', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $upcoming ?></div><div class="stat-label">Upcoming bookings</div></div>
        </div>
        <div class="card stat">
            <div class="stat-icon orange"><?= icon('history', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $total ?></div><div class="stat-label">Total trips booked</div></div>
        </div>
        <div class="card stat">
            <div class="stat-icon green"><?= icon('bus', 'icon icon-lg') ?></div>
            <div><div class="stat-value"><?= $available ?></div><div class="stat-label">Trips open for booking</div></div>
        </div>
    </div>

    <div class="layout-side">
        <div>
            <h2>Our services</h2>
            <div class="grid grid-2">
                <a href="view_trip.php" class="card feature">
                    <div class="feature-icon"><?= icon('ticket', 'icon icon-lg') ?></div>
                    <h3>Book your trip</h3>
                    <p>Reserve up to <?= MAX_SEATS_PER_BOOKING ?> seats on any upcoming trip.</p>
                </a>
                <a href="view_trip.php" class="card feature">
                    <div class="feature-icon blue"><?= icon('calendar', 'icon icon-lg') ?></div>
                    <h3>View bus schedules</h3>
                    <p>Search trips by destination and check free seats.</p>
                </a>
                <a href="history.php" class="card feature">
                    <div class="feature-icon blue"><?= icon('history', 'icon icon-lg') ?></div>
                    <h3>Manage your bookings</h3>
                    <p>See your history, driver details, or cancel a booking.</p>
                </a>
                <a href="feedback.php" class="card feature">
                    <div class="feature-icon"><?= icon('message', 'icon icon-lg') ?></div>
                    <h3>Share your feedback</h3>
                    <p>Tell the transport office how we are doing.</p>
                </a>
            </div>
        </div>

        <aside>
            <h2>Your next trip</h2>
            <?php if ($nextTrip): ?>
                <div class="card">
                    <div class="trip-top">
                        <div>
                            <p class="trip-dest"><?= e($nextTrip['destination']) ?></p>
                            <span class="trip-dir"><?= e(trip_direction($nextTrip['time'])) ?></span>
                        </div>
                        <span class="badge badge-success">Confirmed</span>
                    </div>
                    <div class="trip-meta mt-2">
                        <span><?= icon('calendar') ?> <?= e(fmt_date($nextTrip['date'])) ?></span>
                        <span><?= icon('clock') ?> <?= e(fmt_time($nextTrip['time'])) ?></span>
                        <span><?= icon('bus') ?> <?= e($nextTrip['bus_number']) ?></span>
                        <span><?= icon('seat') ?> <?= (int) $nextTrip['seats'] ?> seat(s)</span>
                    </div>
                    <a href="history.php" class="btn btn-ghost btn-block mt-2">View details</a>
                </div>
            <?php else: ?>
                <div class="card empty">
                    <div class="feature-icon"><?= icon('bus', 'icon icon-lg') ?></div>
                    <p>You have no upcoming trips.</p>
                    <a href="view_trip.php" class="btn btn-primary">Find a trip</a>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
