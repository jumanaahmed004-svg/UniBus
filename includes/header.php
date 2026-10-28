<?php
/**
 * Shared page header + navigation.
 * Before including, a page may set:
 *   $pageTitle  - text for the browser tab
 *   $activeNav  - key of the menu item to highlight
 *   $bodyClass  - extra class on <body>
 */
$pageTitle = $pageTitle ?? APP_TAGLINE;
$activeNav = $activeNav ?? '';
$bodyClass = $bodyClass ?? '';
$role      = current_role();

$menus = [
    'guest' => [
        'home'     => ['index.php', 'Home'],
        'about'    => ['index.php#about', 'About'],
        'driver'   => ['driver_login.php', 'Driver'],
        'admin'    => ['admin_login.php', 'Admin'],
        'login'    => ['login.php', 'Log in'],
    ],
    'user' => [
        'welcome'  => ['welcome.php', 'Home'],
        'trips'    => ['view_trip.php', 'Trips'],
        'history'  => ['history.php', 'My bookings'],
        'feedback' => ['feedback.php', 'Feedback'],
    ],
    'admin' => [
        'dashboard' => ['dashboard.php', 'Dashboard'],
        'trips'     => ['admin_view.php', 'Trips'],
        'bookings'  => ['admin_bookings.php', 'Bookings'],
        'feedback'  => ['admin_feedback.php', 'Feedback'],
        'drivers'   => ['admin_drivers.php', 'Drivers'],
        'admins'    => ['admin_admins.php', 'Admins'],
    ],
    'driver' => [
        'driver'   => ['driver_view.php', 'My trips'],
    ],
];
$homeLink = ['guest' => 'index.php', 'user' => 'welcome.php', 'admin' => 'dashboard.php', 'driver' => 'driver_view.php'][$role];
$who = [
    'user'   => current_user()['username']    ?? '',
    'admin'  => current_admin()['admin_name'] ?? '',
    'driver' => current_driver()['driver_name'] ?? '',
][$role] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <meta name="description" content="UniBus - book university bus seats online.">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($bodyClass) ?>">

<header class="navbar">
    <div class="container nav-inner">
        <a href="<?= e($homeLink) ?>" class="brand">
            <img src="assets/img/logo-mark.png" alt="" width="40" height="40">
            <span>Uni<b>Bus</b></span>
            <?php if ($role === 'admin'): ?><span class="role-tag">Admin</span><?php endif; ?>
            <?php if ($role === 'driver'): ?><span class="role-tag">Driver</span><?php endif; ?>
        </a>

        <button class="nav-toggle" type="button" aria-label="Open menu" aria-expanded="false">
            <?= icon('menu') ?>
        </button>

        <nav class="nav-links" id="nav-links">
            <?php foreach ($menus[$role] as $key => [$href, $label]): ?>
                <a href="<?= e($href) ?>" class="<?= $key === $activeNav ? 'active' : '' ?><?= $key === 'login' ? ' btn btn-primary btn-sm' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>

            <?php if ($role !== 'guest'): ?>
                <span class="nav-user"><?= icon('user', 'icon icon-sm') ?> <?= e($who) ?></span>
                <a href="logout.php" class="btn btn-ghost btn-sm"><?= icon('logout', 'icon icon-sm') ?> Log out</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page">
<?php foreach (take_flashes() as $f): ?>
    <div class="container">
        <div class="alert alert-<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'success' ? 'check' : 'info', 'icon icon-sm') ?> <span><?= e($f['message']) ?></span></div>
    </div>
<?php endforeach; ?>
