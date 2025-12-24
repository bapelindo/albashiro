@echo off
REM =====================================================
REM Automatic WhatsApp Reminder Cron Job
REM Run this via Windows Task Scheduler every hour
REM =====================================================

REM Change to script directory
cd /d "%~dp0"

REM Run PHP script
REM Adjust PHP path if needed
php send_reminders.php

REM Exit with PHP script's exit code
exit /b %ERRORLEVEL%
