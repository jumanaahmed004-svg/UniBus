<?php
require_once __DIR__ . '/includes/functions.php';

$stats = [
    'trips'        => (int) q_val('SELECT COUNT(*) FROM trips WHERE date >= ?', [date('Y-m-d')]),
    'destinations' => (int) q_val('SELECT COUNT(DISTINCT destination) FROM trips'),
    'drivers'      => (int) q_val('SELECT COUNT(*) FROM drivers'),
];

$pageTitle = APP_TAGLINE;
$activeNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-inner">
        <div>
            <span class="hero-badge"><?= icon('pin', 'icon icon-sm') ?> <?= e(CAMPUS) ?></span>
            <h1>Your seat on the <span>university bus</span>, booked in seconds.</h1>
            <p class="lead">UniBus lets students and staff check bus schedules, reserve seats and manage their trips online, with no queues and no guessing.</p>
            <div class="hero-actions">
                <a href="login.php" class="btn btn-accent btn-lg"><?= icon('user', 'icon') ?> Student / Staff login</a>
                <a href="register.php" class="btn btn-outline-light btn-lg">Create account</a>
            </div>
            <div class="hero-stats">
                <div><strong><?= $stats['trips'] ?></strong><span>Upcoming trips</span></div>
                <div><strong><?= $stats['destinations'] ?></strong><span>Destinations</span></div>
                <div><strong><?= $stats['drivers'] ?></strong><span>Drivers</span></div>
            </div>
        </div>
        <div class="hero-card">
            <img src="assets/img/logo.jpg" alt="UniBus logo" width="640" height="539">
        </div>
    </div>
</section>

<section class="section" id="about">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">About UniBus</span>
            <h2>Smart, reliable transport for the university community</h2>
            <p>UniBus replaces paper schedules and phone calls with one simple platform. Students book seats, drivers see their passengers, and the transport office manages every trip.</p>
        </div>

        <div class="grid grid-4">
            <div class="card feature">
                <div class="feature-icon"><?= icon('ticket', 'icon icon-lg') ?></div>
                <h3>Book a seat</h3>
                <p>Pick a trip, choose your seats and pickup point, and confirm in three steps.</p>
            </div>
            <div class="card feature">
                <div class="feature-icon blue"><?= icon('calendar', 'icon icon-lg') ?></div>
                <h3>Live schedules</h3>
                <p>See every trip with its date, time, bus and remaining seats.</p>
            </div>
            <div class="card feature">
                <div class="feature-icon"><?= icon('history', 'icon icon-lg') ?></div>
                <h3>Manage bookings</h3>
                <p>View your history, see driver details and cancel when plans change.</p>
            </div>
            <div class="card feature">
                <div class="feature-icon blue"><?= icon('leaf', 'icon icon-lg') ?></div>
                <h3>Greener campus</h3>
                <p>Shared rides mean fewer cars, less traffic and lower emissions.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">Who is it for?</span>
            <h2>One system, three roles</h2>
        </div>
        <div class="grid grid-3">
            <div class="card role-card">
                <div class="feature-icon"><?= icon('users', 'icon icon-lg') ?></div>
                <h3>Students &amp; staff</h3>
                <p class="muted">Register, browse trips, book seats, view history and send feedback.</p>
                <a href="login.php" class="btn btn-primary">Log in <?= icon('arrow', 'icon icon-sm') ?></a>
            </div>
            <div class="card role-card">
                <div class="feature-icon blue"><?= icon('steering', 'icon icon-lg') ?></div>
                <h3>Drivers</h3>
                <p class="muted">See assigned trips and the passenger list with pickup locations.</p>
                <a href="driver_login.php" class="btn btn-ghost">Driver login <?= icon('arrow', 'icon icon-sm') ?></a>
            </div>
            <div class="card role-card">
                <div class="feature-icon"><?= icon('shield', 'icon icon-lg') ?></div>
                <h3>Administrators</h3>
                <p class="muted">Add, update and delete trips, and monitor bookings and feedback.</p>
                <a href="admin_login.php" class="btn btn-ghost">Admin login <?= icon('arrow', 'icon icon-sm') ?></a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
