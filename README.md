<p align="center">
  <img src="assets/img/logo.jpg" alt="UniBus logo" width="200">
</p>

<h1 align="center">UniBus</h1>
<p align="center"><b>University Bus Booking System</b><br>
University of Technology and Applied Sciences – Suhar Campus</p>

<p align="center">
  <img alt="PHP" src="https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white">
  <img alt="SQLite" src="https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white">
  <img alt="HTML5" src="https://img.shields.io/badge/HTML5-CSS3-E34F26?logo=html5&logoColor=white">
  <img alt="JavaScript" src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?logo=javascript&logoColor=black">
</p>

---

## Contents

- [About the Project](#about-the-project)
- [Problem Statement](#problem-statement)
- [Objectives](#objectives)
- [The Solution](#the-solution)
- [Users and Features](#users-and-features)
- [System Requirements](#system-requirements)
- [Technologies](#technologies)
- [System Design](#system-design)
- [Screenshots](#screenshots)
- [Getting Started](#getting-started)
- [Demo Accounts](#demo-accounts)
- [Project Structure](#project-structure)
- [Security](#security)
- [Future Work](#future-work)
- [Team](#team)

---

## About the Project

**UniBus** is a web-based information system for managing and improving university bus
transportation. It gives students, lecturers and staff one simple place to check bus schedules
and book seats on trips between their homes and the university.

UniBus aims to reduce waiting times, prevent overcrowded buses and improve communication between
passengers and the transportation department. Administrators manage trips, drivers and bookings
from a dashboard, and drivers see exactly who to pick up and where.

The system also supports the university's sustainability goals: shared transport means fewer
private cars, less traffic around the campus and lower carbon emissions.

---

## Problem Statement

University transportation is usually managed manually. Schedules are posted on notice boards,
and there is no single platform for booking seats or receiving updates. This leads to:

- **Overcrowded buses** and poor use of the available seats
- **Delays and confusion** about trip times
- **Weak communication** between students, drivers and the transport office
- **Manual work** for staff to organise routes, drivers and passengers

UniBus solves these problems by moving the whole process online: seats are booked in advance,
seat availability is always up to date, and every user sees the information they need.

---

## Objectives

1. Provide comfortable and reliable university transport.
2. Offer specialised services for students and staff.
3. Manage user and driver data efficiently.
4. Give users an easy way to send feedback and comments.
5. Make booking and managing bus trips quick and simple.
6. Help students easily find available transportation.
7. Improve safety and organisation through better planning.

---

## The Solution

UniBus is a user-friendly platform for transportation management and online booking:

- Passengers **register once**, then browse upcoming trips and **book seats in three steps**:
  choose a trip, enter the number of seats and the pickup location, and choose a payment method.
- Each booking immediately **reduces the available seats**, so a bus can never be over-booked.
- The **driver** receives a passenger list with names, phone numbers and pickup locations.
- The **administrator** plans trips, assigns drivers and follows bookings and feedback through a
  dashboard with live statistics.

**Daily schedule**

- **8:00 AM and 10:00 AM:** from home to the university
- **12:00 PM, 2:00 PM, 4:00 PM and 6:00 PM:** from the university to home

The service covers the areas around Suhar, including Sohar, Suwaiq, Liwa, Shinas and Al Khaboura.

---

## Users and Features

### 🎓 Students and Staff
- Create an account and log in securely
- View upcoming trips with date, time, bus number, driver and free seats
- Search trips by destination
- Book up to 5 seats with an exact pickup location
- Choose a payment method: cash on the bus or card
- Receive a booking confirmation with the driver's contact number
- View booking history and cancel upcoming bookings
- Send feedback to the transport office

### 🚌 Drivers
- Log in with full name and phone number
- View today's, upcoming and past trips
- See the passenger list for each trip: name, phone number, seats and pickup location

### 🛡️ Administrators
- Dashboard with statistics: upcoming trips, active bookings, registered users, revenue and seat occupancy per destination
- Insert, update and delete trips, and assign a driver to each trip
- View and search all bookings
- Read user feedback
- Add, edit and remove drivers
- Add and remove administrators, and change their own password

---

## System Requirements

### Functional requirements
- **Registration and login:** secure accounts for students/staff, drivers and administrators
- **Seat booking:** book and cancel seats, with live seat availability
- **Bus schedule:** display trips with routes, dates and times
- **Admin dashboard:** tools to plan trips and manage drivers, bookings and accounts
- **Feedback system:** users can send comments and report problems

### Non-functional requirements
- **Usability:** a simple and clear interface for all users, on computers and phones
- **Security:** protected user data, encrypted passwords and secure login
- **Reliability:** consistent data, with seat updates that cannot conflict
- **Performance:** fast page loading
- **Scalability:** can grow to more routes, buses and campuses
- **Maintainability:** organised code that is easy to update

---

## Technologies

- **HTML5 and CSS3:** page structure and a responsive design for desktop and mobile
- **JavaScript:** interactive features such as the mobile menu, live price and confirmations
- **PHP 8:** server-side logic, user sessions and booking rules
- **SQLite:** relational database stored in a single file and created automatically
- **PDO (PHP Data Objects):** secure communication between PHP and the database
- **Visual Studio Code:** development environment

---

## System Design

### Architecture
UniBus follows a **three-tier client–server architecture**:

- **Client tier:** web pages in the browser for students/staff, drivers and administrators.
- **Application tier:** PHP pages that handle requests, check permissions and apply the booking rules.
- **Data tier:** the SQLite database that stores users, drivers, trips, bookings and feedback.

<p align="center"><img src="docs/diagrams/architecture.png" alt="System architecture" width="780"></p>

### Use case diagram
Shows what each of the three actors (Student/Staff, Administrator and Driver) can do in the system.

<p align="center"><img src="docs/diagrams/use_case.png" alt="Use case diagram" width="700"></p>

### Database (ERD)
The database has six tables:

- **users:** students and staff
- **admin:** administrator accounts
- **drivers:** bus drivers
- **trips:** trips with destination, date, time, bus, driver and seats
- **booking:** bookings with seats, pickup location, payment and status
- **feedback:** messages from users

A user makes many bookings, and each trip has many bookings. A driver drives many trips,
an administrator manages many trips, and a user can write many feedback messages.

<p align="center"><img src="docs/diagrams/erd.png" alt="Entity relationship diagram" width="780"></p>

More diagrams (context diagram, data flow diagram, booking flowchart and project schedule) are in
[`docs/diagrams`](docs/diagrams).

---

## Screenshots

| Available trips | Booking and payment |
|---|---|
| ![Trips](docs/screenshots/trips.jpg) | ![Booking](docs/screenshots/confirm.jpg) |
| **Administrator dashboard** | **Driver – passenger list** |
| ![Dashboard](docs/screenshots/dashboard.jpg) | ![Driver](docs/screenshots/driver_view.jpg) |

---

## Getting Started

### Required software
- **PHP 8** or newer, with the `pdo_sqlite` extension
- **Visual Studio Code**
- A web browser (Chrome, Edge, Firefox or Safari)

No separate database server is needed.

### Step 1 – Install Visual Studio Code
Download and install it from https://code.visualstudio.com.

### Step 2 – Install PHP

**Windows:** open PowerShell and run:
```powershell
winget install PHP.PHP.8.3
```
Close PowerShell, open it again and check the version:
```powershell
php -v
```

**macOS:**
```bash
brew install php
```

**Linux (Ubuntu / Debian):**
```bash
sudo apt install php-cli php-sqlite3
```

### Step 3 – Enable the SQLite extension (Windows only)
Paste this into PowerShell and press Enter:
```powershell
$dir = Split-Path (php -r "echo PHP_BINARY;")
if (!(Test-Path "$dir\php.ini")) { Copy-Item "$dir\php.ini-development" "$dir\php.ini" }
(Get-Content "$dir\php.ini") `
  -replace '^;?\s*extension_dir\s*=\s*"ext"', "extension_dir = `"$dir\ext`"" `
  -replace '^;extension=pdo_sqlite', 'extension=pdo_sqlite' |
  Set-Content "$dir\php.ini"
php -m | Select-String "pdo_sqlite"
```
The result must show **pdo_sqlite**.

### Step 4 – Open and run the project in VS Code
1. Download or extract the **UniBus** project folder.
2. Open VS Code and select **File → Open Folder…**, then choose the `UniBus` folder.
3. Open the terminal: **Terminal → New Terminal**.
4. Start the server:
   ```bash
   php -S localhost:8000
   ```
5. Open **http://localhost:8000** in your browser.
6. To stop the server, click in the terminal and press **Ctrl + C**.

The database is created automatically with demo data on the first visit.

---

## Demo Accounts

| Role | Login | Password |
|---|---|---|
| Student | `student@unibus.test` | `Student@123` |
| Staff | `staff@unibus.test` | `Staff@123` |
| Administrator | `admin` | `Admin@123` |
| Driver | `Salim Al-Balushi` | `95123456` |

Drivers log in with their **full name** and their **phone number** as the password.

---

## Project Structure

```
UniBus/
├── index.php, login.php, register.php, logout.php     Public pages
├── welcome.php, view_trip.php, booking.php,
│   confirm_booking.php, thanks.php, history.php,
│   cancel_booking.php, feedback.php                    Student / staff pages
├── driver_login.php, driver_view.php                   Driver pages
├── admin_login.php, dashboard.php, admin_view.php,
│   insert.php, update.php, delete.php,
│   admin_bookings.php, admin_feedback.php,
│   admin_drivers.php, admin_admins.php                 Administrator pages
├── includes/      Shared code: settings, database, security, header and footer
├── assets/        Stylesheet, JavaScript and images
├── database/      Database tables and demo data (schema.sql)
└── docs/          Diagrams and screenshots
```

---

## Security

- **Encrypted passwords:** stored with bcrypt hashing, never as plain text
- **Strong password rule:** at least 8 characters with uppercase, lowercase, a number and a special character
- **Protection against SQL injection:** all database queries use prepared statements
- **Protection against XSS:** all displayed data is escaped
- **Protection against CSRF:** every form includes a secret security token
- **Access control:** each page is only available to the correct role (student, driver or administrator)
- **Safe booking:** seat updates run in a single database transaction

---

## Future Work

- Real-time GPS tracking of buses on a live map
- SMS, email or push notifications for confirmations and delays
- Integration with an online payment gateway
- A mobile application for Android and iOS
- Trip status updates by drivers (departed, delayed, completed)
- Support for multiple campuses and route optimisation

---

## Team

**Software Engineer:** Jumana Albalushi
