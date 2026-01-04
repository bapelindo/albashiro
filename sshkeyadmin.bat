@echo off
title Install SSH Key for Administrator
cd /d %~dp0

echo ==================================================
echo  SSH KEY INSTALLER - ADMINISTRATOR FIX
echo ==================================================
echo.
echo Masalah: SSH server mencari key di lokasi khusus untuk Administrator
echo Solusi: Copy key ke C:\ProgramData\ssh\administrators_authorized_keys
echo.

if not exist "albashiro.pub" (
    echo [ERROR] File albashiro.pub tidak ditemukan!
    pause
    exit /b 1
)

echo [INFO] Copying key to administrators location...
copy /Y albashiro.pub C:\ProgramData\ssh\administrators_authorized_keys

if errorlevel 1 (
    echo [ERROR] Gagal copy! Pastikan run as Administrator
    pause
    exit /b 1
)

echo [FIXING] Permissions...
icacls C:\ProgramData\ssh\administrators_authorized_keys /inheritance:r /grant "Administrators:F" "SYSTEM:F"

echo.
echo [SUCCESS] Key installed for Administrator!
echo Location: C:\ProgramData\ssh\administrators_authorized_keys
echo.
echo Sekarang coba login SSH dengan PuTTY (tanpa password)
echo.
pause
