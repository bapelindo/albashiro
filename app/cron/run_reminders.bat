@echo off
REM =====================================================
REM Automatic WhatsApp Reminder Cron Job
REM Run this via Windows Task Scheduler
REM =====================================================

REM 1. Set PHP Path (Manual Install Location)
set PHP_BIN=C:\apache\php\php.exe

REM 2. Change to script directory
cd /d "%~dp0"

REM 3. Run PHP script
REM If PHP is not found at specific path, try global 'php' command
if exist "%PHP_BIN%" (
    "%PHP_BIN%" send_reminders.php
) else (
    echo [WARNING] PHP not found at %PHP_BIN%, trying global path...
    php send_reminders.php
)

REM 4. Exit with PHP script's exit code
exit /b %ERRORLEVEL%
