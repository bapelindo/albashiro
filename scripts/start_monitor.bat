@echo off
echo ========================================
echo SYSTEM RESOURCE MONITOR
echo ========================================
echo.
echo Starting resource monitor...
echo Log file: scraped_data\monitor\system_monitor.log
echo.
echo Press Ctrl+C to stop monitoring
echo ========================================
echo.

cd /d c:\apache\htdocs\albashiro
python scripts\monitor_resources.py

pause
