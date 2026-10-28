#!/usr/bin/env sh
# UniBus - start the local server (Mac / Linux)
cd "$(dirname "$0")"
if ! command -v php >/dev/null 2>&1; then
  echo "PHP was not found. Install PHP 8 (Mac: brew install php, Ubuntu: sudo apt install php-cli php-sqlite3)."
  exit 1
fi
echo "Starting UniBus on http://localhost:8000  (press Ctrl+C to stop)"
php -S localhost:8000
