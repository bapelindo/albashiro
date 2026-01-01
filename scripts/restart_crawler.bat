@echo off
echo Killing any running Python processes...
taskkill /F /IM python.exe 2>nul
timeout /t 2 /nobreak >nul

echo Starting fresh crawler instance...
cd /d c:\apache\htdocs\albashiro
python scripts\Albashiro_Crawler_PERFECT.py

pause
