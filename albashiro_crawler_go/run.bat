@echo off
title Albashiro Crawler System
cls
echo ============================================================
echo   ALBASHIRO KNOWLEDGE SYSTEM
echo ============================================================
echo.
echo  [1] Start Crawler (Panen Data Article)
echo  [2] Start Importer (Upload JSON to TiDB Database)
echo  [3] Build/Compile Both (Update Exe)
echo.
set /p choice=Select Option (1-3): 

if "%choice%"=="1" goto run_crawler
if "%choice%"=="2" goto run_importer
if "%choice%"=="3" goto build_all
goto end

:run_crawler
cls
echo Checking/Building Crawler...
go build -ldflags="-s -w" -o crawler.exe main.go
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Build Failed! Fix errors first.
    pause
    goto end
)
echo Starting Crawler...
crawler.exe
goto end

:run_importer
cls
echo Starting Importer (Database Sync)...
echo Note: Pastikan koneksi internet lancar dan port 4000 aman.
echo.
go run cmd/importer/main.go
pause
goto end

:build_all
cls
echo Building Crawler...
go build -ldflags="-s -w" -o crawler.exe main.go
echo.
echo Building Importer...
go build -ldflags="-s -w" -o importer.exe cmd/importer/main.go
echo.
echo ✅ Build Complete!
pause
goto end

:end
echo.
echo Bye!
pause
