-- =====================================================================
--  UniBus - University Bus Booking System
--  Database schema + demo data (SQLite)
--
--  This file is run automatically the first time the app starts
--  (see includes/db.php). To reset the demo, delete database/unibus.sqlite
--  and refresh the page.
--
--  Demo accounts
--    Student : student@unibus.test / Student@123
--    Staff   : staff@unibus.test   / Staff@123
--    Admin   : admin               / Admin@123
--    Driver  : Salim Al-Balushi    / 95123456   (driver name / phone)
-- =====================================================================

PRAGMA foreign_keys = ON;

-- ---------------------------------------------------------------------
-- Administrators
-- ---------------------------------------------------------------------
CREATE TABLE admin (
    admin_id    INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_name  TEXT    NOT NULL UNIQUE,
    password    TEXT    NOT NULL                -- bcrypt hash
);

-- ---------------------------------------------------------------------
-- Students and staff
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id     INTEGER PRIMARY KEY AUTOINCREMENT,
    username    TEXT    NOT NULL,
    email       TEXT    NOT NULL UNIQUE,
    password    TEXT    NOT NULL,               -- bcrypt hash
    phone       TEXT    NOT NULL,
    created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------------------
-- Bus drivers
-- ---------------------------------------------------------------------
CREATE TABLE drivers (
    driver_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    driver_name TEXT    NOT NULL,
    phone       TEXT    NOT NULL UNIQUE
);

-- ---------------------------------------------------------------------
-- Trips (one bus, one destination, one date/time)
-- ---------------------------------------------------------------------
CREATE TABLE trips (
    trip_id         INTEGER PRIMARY KEY AUTOINCREMENT,
    admin_id        INTEGER REFERENCES admin(admin_id)    ON DELETE SET NULL,
    bus_number      TEXT    NOT NULL,
    date            TEXT    NOT NULL,          -- YYYY-MM-DD
    destination     TEXT    NOT NULL,
    driver_id       INTEGER REFERENCES drivers(driver_id) ON DELETE SET NULL,
    seats_available INTEGER NOT NULL CHECK (seats_available >= 0),
    seats_total     INTEGER NOT NULL CHECK (seats_total > 0),
    time            TEXT    NOT NULL,          -- HH:MM
    CHECK (seats_available <= seats_total)
);
CREATE INDEX idx_trips_date   ON trips(date);
CREATE INDEX idx_trips_driver ON trips(driver_id);

-- ---------------------------------------------------------------------
-- Bookings
-- ---------------------------------------------------------------------
CREATE TABLE booking (
    booking_id      INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_date    TEXT    NOT NULL DEFAULT (date('now')),
    user_id         INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    trip_id         INTEGER NOT NULL REFERENCES trips(trip_id) ON DELETE CASCADE,
    seats           INTEGER NOT NULL DEFAULT 1 CHECK (seats > 0),
    pickup_location TEXT,
    payment_method  TEXT    NOT NULL DEFAULT 'cash' CHECK (payment_method IN ('cash','card')),
    total_price     REAL    NOT NULL DEFAULT 0,
    status          TEXT    NOT NULL DEFAULT 'confirmed' CHECK (status IN ('confirmed','cancelled'))
);
CREATE INDEX idx_booking_user ON booking(user_id);
CREATE INDEX idx_booking_trip ON booking(trip_id);

-- ---------------------------------------------------------------------
-- Feedback
-- ---------------------------------------------------------------------
CREATE TABLE feedback (
    feedback_id   INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id       INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    message       TEXT    NOT NULL,
    feedback_date TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================================
--  Demo data
-- =====================================================================

INSERT INTO admin (admin_id, admin_name, password) VALUES
(1, 'admin', '$2y$10$aF3k2K.gCRyl7GpZdBiFnuY1qNNxFlFUlJgOKpoy5bVxDMjCCBJ5y');

INSERT INTO users (user_id, username, email, password, phone) VALUES
(1, 'Demo Student', 'student@unibus.test', '$2y$10$T2xAZXs/reaiNG1.mR8jl.86buDs5K610zN1AATIXWWal/Zc0D/xS', '90000001'),
(2, 'Demo Staff',   'staff@unibus.test',   '$2y$10$qoh8gm2hS6ATrf88Zm0sX.cc7EJ1Bn/LfS/BfOI.UC6n4PyCiw4sG', '90000002');

INSERT INTO drivers (driver_id, driver_name, phone) VALUES
(1,  'Salim Al-Balushi',  '95123456'),
(2,  'Ahmed Al-Aufi',     '95234567'),
(3,  'Khalid Al-Maamari', '95345678'),
(4,  'Maher Al-Hosni',    '95456789'),
(5,  'Nasser Al-Sheedi',  '95567890'),
(6,  'Fahad Al-Hinai',    '95678901'),
(7,  'Hamed Al-Rashdi',   '95789012'),
(8,  'Mansoor Al-Farsi',  '95890123'),
(9,  'Talib Al-Kiyumi',   '95901234'),
(10, 'Yousef Al-Saadi',   '95012345');

-- Trip dates are relative to the day the database is created,
-- so the demo always has upcoming trips.
INSERT INTO trips (trip_id, admin_id, bus_number, date, destination, driver_id, seats_available, seats_total, time) VALUES
(1,  1, 'BUS-101', date('now','-3 day'), 'Sohar',       1,  30, 30, '08:00'),
(2,  1, 'BUS-102', date('now','-3 day'), 'Suwaiq',      2,  30, 30, '14:00'),
(3,  1, 'BUS-101', date('now','+1 day'), 'Sohar',       1,  30, 30, '08:00'),
(4,  1, 'BUS-102', date('now','+1 day'), 'Suwaiq',      2,  30, 30, '10:00'),
(5,  1, 'BUS-103', date('now','+1 day'), 'Liwa',        3,  30, 30, '12:00'),
(6,  1, 'BUS-104', date('now','+2 day'), 'Shinas',      4,  30, 30, '14:00'),
(7,  1, 'BUS-105', date('now','+2 day'), 'Al Khaboura', 5,  30, 30, '16:00'),
(8,  1, 'BUS-106', date('now','+3 day'), 'Sohar',       6,  30, 30, '18:00'),
(9,  1, 'BUS-107', date('now','+3 day'), 'Suwaiq',      7,  30, 30, '08:00'),
(10, 1, 'BUS-108', date('now','+4 day'), 'Shinas',      8,  30, 30, '10:00'),
(11, 1, 'BUS-109', date('now','+5 day'), 'Liwa',        9,  30, 30, '12:00'),
(12, 1, 'BUS-110', date('now','+5 day'), 'Al Khaboura', 10, 30, 30, '14:00');

INSERT INTO booking (user_id, trip_id, seats, pickup_location, payment_method, total_price, status, booking_date) VALUES
(1, 1, 1, 'Al Hambar, near the mosque',      'cash', 1, 'confirmed', date('now','-5 day')),
(2, 2, 2, 'Al Tareef roundabout',            'card', 2, 'confirmed', date('now','-5 day')),
(1, 3, 1, 'Al Hambar, near the mosque',      'card', 1, 'confirmed', date('now','-1 day')),
(2, 3, 1, 'Falaj Al Qabail, main street',    'cash', 1, 'confirmed', date('now','-1 day')),
(2, 6, 3, 'Shinas souq',                     'cash', 3, 'confirmed', date('now','-1 day')),
(1, 7, 1, 'Al Khaboura hospital',            'cash', 1, 'cancelled', date('now','-2 day'));

-- Keep seats_available consistent with the confirmed bookings above
UPDATE trips SET seats_available = seats_total - COALESCE(
    (SELECT SUM(b.seats) FROM booking b WHERE b.trip_id = trips.trip_id AND b.status = 'confirmed'), 0);

INSERT INTO feedback (user_id, message, feedback_date) VALUES
(1, 'Booking was quick and easy. Please add more morning trips to Sohar.', datetime('now','-2 day')),
(2, 'The driver called on time. Great service!',                            datetime('now','-1 day'));
