@echo off
setlocal EnableDelayedExpansion

REM =====================================================
REM Albashiro Reminder System - Manager
REM Manage the automatic WhatsApp reminder system
REM =====================================================

:MENU
cls
echo ===========================================
echo   ALBASHIRO REMINDER SYSTEM MANAGER
echo ===========================================
echo.
echo  [1] INSTALL / ENABLE (Runs every 1 hour)
echo  [2] UNINSTALL / DISABLE
echo  [3] CHECK STATUS
echo  [4] RUN MANUALLY NOW (Test)
echo  [5] EXIT
echo.
set /p "CHOICE=Enter your choice (1-5): "

if "%CHOICE%"=="1" goto INSTALL
if "%CHOICE%"=="2" goto UNINSTALL
if "%CHOICE%"=="3" goto STATUS
if "%CHOICE%"=="4" goto RUN_NOW
if "%CHOICE%"=="5" goto END

goto MENU

:INSTALL
cls
echo ===========================================
echo   INSTALLING REMINDER SYSTEM
echo ===========================================
echo.

set "SCRIPT_DIR=%~dp0"
if "%SCRIPT_DIR:~-1%"=="\" set "SCRIPT_DIR=%SCRIPT_DIR:~0,-1%"
set "TASK_NAME=AlbashiroReminder"
set "RUN_COMMAND=%SCRIPT_DIR%\run_reminders.bat"

echo Script Path: %RUN_COMMAND%
echo.

echo Creating Scheduled Task...
schtasks /Create /SC HOURLY /MO 1 /TN "%TASK_NAME%" /TR "\"%RUN_COMMAND%\"" /F

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] System installed successfully!
    echo Reminders will run automatically every hour.
) else (
    echo.
    echo [ERROR] Failed to create task.
    echo Please run this script as ADMINISTRATOR.
)
pause
goto MENU

:UNINSTALL
cls
echo ===========================================
echo   UNINSTALLING REMINDER SYSTEM
echo ===========================================
echo.
set "TASK_NAME=AlbashiroReminder"

echo Deleting Scheduled Task...
schtasks /Delete /TN "%TASK_NAME%" /F

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [SUCCESS] System uninstalled.
    echo Automatic reminders are now STOPPED.
) else (
    echo.
    echo [ERROR] Failed to delete task or task not found.
    echo Make sure you are running as ADMINISTRATOR.
)
pause
goto MENU

:STATUS
cls
echo ===========================================
echo   SYSTEM STATUS
echo ===========================================
echo.
set "TASK_NAME=AlbashiroReminder"

schtasks /Query /TN "%TASK_NAME%"
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [INFO] System is NOT INSTALLED.
) else (
    echo.
    echo [INFO] System is RUNNING.
)
pause
goto MENU

:RUN_NOW
cls
echo ===========================================
echo   RUNNING MANUALLY (TEST)
echo ===========================================
echo.
echo Running run_reminders.bat...
echo.
call "%~dp0run_reminders.bat"
echo.
echo [DONE] Manual run completed.
pause
goto MENU

:END
exit /b
