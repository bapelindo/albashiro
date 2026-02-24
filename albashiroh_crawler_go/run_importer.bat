@echo off
echo ============================================================
echo ALBASHIROH TIDB IMPORTER - GOLANG EDITION
echo ============================================================
echo.
echo Building importer...
cd /d c:\apache\htdocs\albashiro\albashiroh_crawler_go

REM Build importer executable
echo Building executable...
go build -o importer.exe cmd/importer/main.go

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✅ Build successful!
    echo.
    echo Starting importer...
    echo ============================================================
    echo.
    importer.exe
) else (
    echo.
    echo ❌ Build failed! Please check errors above.
    pause
)
