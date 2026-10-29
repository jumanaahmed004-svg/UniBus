<?php
/**
 * Shared helpers: sessions, security, auth guards, formatting, icons.
 * Every page starts with:  require_once __DIR__ . '/includes/functions.php';
 */

require_once __DIR__ . '/db.php';

/* ------------------------------------------------------------------
 * Fallbacks so UniBus also works when the PHP "mbstring" extension
 * is not available (for example when Windows blocks it).
 * ------------------------------------------------------------------ */
if (!function_exists('mb_strlen')) {
    function mb_strlen($s, $encoding = null): int
    {
        return (int) preg_match_all('/./us', (string) $s);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($s, $start, $length = null, $encoding = null): string
    {
        preg_match_all('/./us', (string) $s, $m);
        return implode('', array_slice($m[0], $start, $length));
    }
}
if (!function_exists('mb_strtoupper')) {
    function mb_strtoupper($s, $encoding = null): string
    {
        return strtoupper((string) $s);
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ------------------------------------------------------------------
 * Output & navigation
 * ------------------------------------------------------------------ */

/** Escape text for safe HTML output (prevents XSS). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** One-time message shown on the next page. type: success | error | info | warning */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/* ------------------------------------------------------------------
 * CSRF protection (every POST form includes csrf_field())
 * ------------------------------------------------------------------ */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    if (!hash_equals(csrf_token(), $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Invalid form token. Please go back, refresh the page and try again.');
    }
}

/* ------------------------------------------------------------------
 * Authentication
 * ------------------------------------------------------------------ */

/** Log in as one role; clears any other role in the session. */
function login_as(string $role, array $data): void
{
    session_regenerate_id(true);
    unset($_SESSION['user'], $_SESSION['admin'], $_SESSION['driver']);
    $_SESSION[$role] = $data;
}

function current_user(): ?array   { return $_SESSION['user']   ?? null; }
function current_admin(): ?array  { return $_SESSION['admin']  ?? null; }
function current_driver(): ?array { return $_SESSION['driver'] ?? null; }

function current_role(): string
{
    if (current_admin())  return 'admin';
    if (current_driver()) return 'driver';
    if (current_user())   return 'user';
    return 'guest';
}

function require_user(): array
{
    if (!current_user()) {
        flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
    return current_user();
}

function require_admin(): array
{
    // Also log out an admin whose account was removed by another admin
    if (current_admin() && !q_val('SELECT 1 FROM admin WHERE admin_id = ?', [current_admin()['admin_id']])) {
        unset($_SESSION['admin']);
    }
    if (!current_admin()) {
        flash('warning', 'Please log in as an administrator.');
        redirect('admin_login.php');
    }
    return current_admin();
}

function require_driver(): array
{
    if (current_driver() && !q_val('SELECT 1 FROM drivers WHERE driver_id = ?', [current_driver()['driver_id']])) {
        unset($_SESSION['driver']);
    }
    if (!current_driver()) {
        flash('warning', 'Please log in as a driver.');
        redirect('driver_login.php');
    }
    return current_driver();
}

/** Strong password rule from the report (section 4.2). Returns an error or null. */
function password_problem(string $pw): ?string
{
    if (strlen($pw) < 8)                    return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Z]/', $pw))        return 'Password needs at least one uppercase letter.';
    if (!preg_match('/[a-z]/', $pw))        return 'Password needs at least one lowercase letter.';
    if (!preg_match('/[0-9]/', $pw))        return 'Password needs at least one number.';
    if (!preg_match('/[^A-Za-z0-9]/', $pw)) return 'Password needs at least one special character (e.g. @ # !).';
    return null;
}

/* ------------------------------------------------------------------
 * Formatting
 * ------------------------------------------------------------------ */

function fmt_date(?string $d): string
{
    return $d ? date('D, j M Y', strtotime($d)) : '-';
}

function fmt_time(?string $t): string
{
    return $t ? date('g:i A', strtotime($t)) : '-';
}

function fmt_price(float $p): string
{
    return number_format($p, 3) . ' OMR';
}

/** Morning trips go Home -> University, the rest University -> Home. */
function trip_direction(string $time): string
{
    return $time < '12:00' ? 'Home → University' : 'University → Home';
}

/** True when the trip's date + time is already in the past. */
function trip_departed(array $trip): bool
{
    return strtotime($trip['date'] . ' ' . $trip['time']) < time();
}

/** Seats badge class by how full the trip is. */
function seats_class(int $available, int $total): string
{
    if ($available <= 0) return 'badge-danger';
    if ($available <= max(3, (int) ($total * 0.2))) return 'badge-warning';
    return 'badge-success';
}

/* ------------------------------------------------------------------
 * Inline SVG icons (no external library needed)
 * ------------------------------------------------------------------ */

function icon(string $name, string $class = 'icon'): string
{
    $p = [
        'bus'      => '<path d="M8 6v6M16 6v6M2 12h20M7 18h10M4 6a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2z"/><circle cx="7.5" cy="18.5" r="1.5"/><circle cx="16.5" cy="18.5" r="1.5"/>',
        'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
        'clock'    => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'pin'      => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'user'     => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'users'    => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.1 9.9a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'seat'     => '<path d="M6 3v10a2 2 0 0 0 2 2h8M6 15l-1 6M18 15v6M8 15h10l1-4"/>',
        'ticket'   => '<path d="M3 7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4z"/><path d="M13 5v2M13 17v2M13 11v2"/>',
        'history'  => '<path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/>',
        'message'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'search'   => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'steering' => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="2"/><path d="M12 14v8M10 12H2.5M14 12h7.5"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'x'        => '<path d="M18 6 6 18M6 6l12 12"/>',
        'arrow'    => '<path d="M5 12h14M12 5l7 7-7 7"/>',
        'back'     => '<path d="M19 12H5M12 19l-7-7 7-7"/>',
        'chart'    => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'wallet'   => '<path d="M20 12V8H6a2 2 0 0 1 0-4h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/>',
        'cash'     => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
        'card'     => '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'info'     => '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
        'leaf'     => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10z"/><path d="M2 21c0-3 1.85-5.36 5.08-6"/>',
        'home'     => '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    ];
    $path = $p[$name] ?? '';
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
         . 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
}

/* ------------------------------------------------------------------
 * Booking rules (used by booking.php and confirm_booking.php)
 * ------------------------------------------------------------------ */

/** Load a trip with its driver. */
function find_trip(int $tripId): ?array
{
    return q_one(
        'SELECT t.*, d.driver_name, d.phone AS driver_phone
           FROM trips t LEFT JOIN drivers d ON d.driver_id = t.driver_id
          WHERE t.trip_id = ?',
        [$tripId]
    );
}

/** Returns an error message if this user cannot book this trip, or null if OK. */
function booking_problem(?array $trip, int $userId, int $seats = 1): ?string
{
    if (!$trip)                    return 'This trip does not exist.';
    if (trip_departed($trip))      return 'This trip has already departed.';
    if ((int) $trip['seats_available'] <= 0) return 'Sorry, this trip is full.';
    if ($seats < 1 || $seats > MAX_SEATS_PER_BOOKING) {
        return 'You can book between 1 and ' . MAX_SEATS_PER_BOOKING . ' seats.';
    }
    if ($seats > (int) $trip['seats_available']) {
        return 'Only ' . (int) $trip['seats_available'] . ' seat(s) are left on this trip.';
    }
    $already = q_val("SELECT 1 FROM booking WHERE user_id = ? AND trip_id = ? AND status = 'confirmed'", [$userId, $trip['trip_id']]);
    if ($already)                  return 'You have already booked this trip. See “My bookings”.';
    return null;
}

/* ------------------------------------------------------------------
 * Trip form validation (used by insert.php and update.php)
 * ------------------------------------------------------------------ */

/** Read and validate the trip form. Returns [$data, $errors]. */
function trip_input(int $booked = 0): array
{
    $d = [
        'destination' => trim($_POST['destination'] ?? ''),
        'bus_number'  => strtoupper(trim($_POST['bus_number'] ?? '')),
        'date'        => $_POST['date'] ?? '',
        'time'        => substr($_POST['time'] ?? '', 0, 5),
        'driver_id'   => ($_POST['driver_id'] ?? '') === '' ? null : (int) $_POST['driver_id'],
        'seats_total' => (int) ($_POST['seats_total'] ?? 0),
    ];
    $errors = [];
    if ($d['destination'] === '' || mb_strlen($d['destination']) > 60) $errors[] = 'Please enter a destination.';
    if ($d['bus_number'] === '' || mb_strlen($d['bus_number']) > 20)   $errors[] = 'Please enter a bus number.';
    if (!DateTime::createFromFormat('Y-m-d', $d['date']))              $errors[] = 'Please choose a valid date.';
    if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['time']))        $errors[] = 'Please choose a valid time.';
    if ($d['seats_total'] < 1 || $d['seats_total'] > 100)              $errors[] = 'Total seats must be between 1 and 100.';
    if ($d['seats_total'] < $booked)                                   $errors[] = "Total seats cannot be less than the $booked seat(s) already booked.";
    if ($d['driver_id'] !== null && !q_val('SELECT 1 FROM drivers WHERE driver_id = ?', [$d['driver_id']])) {
        $errors[] = 'Please choose a valid driver.';
    }
    return [$d, $errors];
}
