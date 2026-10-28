<?php
/**
 * UniBus - application settings.
 * Change these values to customise the system.
 */

define('APP_NAME', 'UniBus');
define('APP_TAGLINE', 'University Bus Booking System');
define('CONTACT_EMAIL', 'support@unibus.com');
define('CAMPUS', 'UTAS - Suhar Campus');

// Price of one seat (OMR)
define('SEAT_PRICE', 1.0);

// Maximum seats a user can book in one booking
define('MAX_SEATS_PER_BOOKING', 5);

// SQLite database file (created automatically from database/schema.sql)
define('DB_FILE', __DIR__ . '/../database/unibus.sqlite');
define('DB_SCHEMA', __DIR__ . '/../database/schema.sql');

date_default_timezone_set('Asia/Muscat');

// Show PHP errors while developing. Set to false before hosting online.
define('DEBUG', true);
ini_set('display_errors', DEBUG ? '1' : '0');
error_reporting(E_ALL);
