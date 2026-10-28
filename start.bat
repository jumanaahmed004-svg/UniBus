@echo off
REM UniBus - start the local server (Windows)
cd /d "%~dp0"
set "PHP_EXE=php"
where php >nul 2>nul
if errorlevel 1 set "PHP_EXE=C:\xampp\php\php.exe"
if not "%PHP_EXE%"=="php" if not exist "%PHP_EXE%" (
    echo PHP was not found. Install PHP 8 from https://windows.php.net/download/ or install XAMPP.
    pause
    exit /b 1
)
echo Starting UniBus on http://localhost:8000  (press Ctrl+C to stop)
start "" http://localhost:8000
"%PHP_EXE%" -S localhost:8000
