<?php
/** Cancels one of the logged-in user's upcoming bookings and frees its seats. */
require_once __DIR__ . '/includes/functions.php';
$user = require_user();

if (!is_post()) redirect('history.php');
csrf_check();

$b = q_one(
    "SELECT b.*, t.date, t.time FROM booking b JOIN trips t ON t.trip_id = b.trip_id
      WHERE b.booking_id = ? AND b.user_id = ? AND b.status = 'confirmed'",
    [(int) ($_POST['booking_id'] ?? 0), $user['user_id']]
);

if (!$b) {
    flash('error', 'Booking not found.');
} elseif (trip_departed($b)) {
    flash('error', 'This trip has already departed and cannot be cancelled.');
} else {
    $pdo = db();
    $pdo->beginTransaction();
    q("UPDATE booking SET status = 'cancelled' WHERE booking_id = ?", [$b['booking_id']]);
    q('UPDATE trips SET seats_available = MIN(seats_total, seats_available + ?) WHERE trip_id = ?', [$b['seats'], $b['trip_id']]);
    $pdo->commit();
    flash('success', 'Booking #' . $b['booking_id'] . ' was cancelled.');
}
redirect('history.php');
