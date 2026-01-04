@echo off
setlocal enabledelayedexpansion
title SSH KEY INSTALLER
cd /d %~dp0

echo ==================================================
echo  SSH KEY INSTALLER - ALBASHIRO
echo ==================================================
echo.

:: Cari file .pub
set "PUBFILE="
for %%f in (*.pub) do (
    set "PUBFILE=%%f"
    goto :found
)

:found
if "%PUBFILE%"=="" (
    echo [ERROR] File .pub tidak ditemukan!
    echo Simpan public key dari PuTTYgen sebagai albashiro.pub
    pause
    exit /b 1
)

echo [OK] File ditemukan: %PUBFILE%
echo.

:: Baca isi file
set "KEYDATA="
for /f "usebackq delims=" %%a in ("%PUBFILE%") do (
    set "LINE=%%a"
    :: Skip header/footer/comment lines
    echo !LINE! | findstr /C:"BEGIN SSH2" >nul
    if errorlevel 1 (
        echo !LINE! | findstr /C:"END SSH2" >nul
        if errorlevel 1 (
            echo !LINE! | findstr /C:"Comment:" >nul
            if errorlevel 1 (
                set "KEYDATA=!KEYDATA!!LINE!"
            )
        )
    )
)

:: Cek apakah sudah format OpenSSH atau masih SSH2
echo %KEYDATA% | findstr /C:"ssh-rsa" >nul
if errorlevel 1 (
    echo [CONVERTING] PuTTY SSH2 -^> OpenSSH format...
    set "KEYDATA=ssh-rsa %KEYDATA% albashiro"
) else (
    echo [OK] Format OpenSSH detected
)

:: Setup directory
if not exist "%USERPROFILE%\.ssh" mkdir "%USERPROFILE%\.ssh"

:: RESET authorized_keys (hapus yang lama)
if exist "%USERPROFILE%\.ssh\authorized_keys" (
    echo [RESET] Menghapus key lama...
    del "%USERPROFILE%\.ssh\authorized_keys"
)

:: Tulis key baru
echo %KEYDATA% > "%USERPROFILE%\.ssh\authorized_keys"

:: Fix permissions
echo [FIXING] Permissions...
icacls "%USERPROFILE%\.ssh\authorized_keys" /inheritance:r /grant "Administrators:F" "SYSTEM:F" "%USERNAME%:F" >nul 2>&1

echo.
echo [SUCCESS] Key installed!
echo Location: %USERPROFILE%\.ssh\authorized_keys
echo.
echo Sekarang coba login SSH dengan PuTTY (tanpa password)
echo.
pause
