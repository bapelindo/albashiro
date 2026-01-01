@echo off
echo ============================================================
echo DISABLE AI CHECK - Maximum Speed Mode
echo ============================================================
echo.
echo Updating config to disable Ollama AI relevance check...
echo This will make crawler 10x faster!
echo.

cd /d c:\apache\htdocs\albashiro\albashiro_crawler_go

REM Update config.go to set UseOllama to false
powershell -Command "(Get-Content internal\config\config.go) -replace 'UseOllama:\s+getEnvBool\(\"USE_OLLAMA\",\s+true\)', 'UseOllama:            getEnvBool(\"USE_OLLAMA\", false)' | Set-Content internal\config\config.go"

echo Config updated: UseOllama = false
echo.
echo Rebuilding crawler...
echo.

go build -o crawler.exe main.go 2>&1

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================================
    echo SUCCESS! AI Check is now DISABLED
    echo ============================================================
    echo.
    echo Speed: 50-100 articles/sec ^(10x faster!^)
    echo Ollama: NOT REQUIRED
    echo Embeddings: STILL ENABLED ^(all-minilm^)
    echo.
    echo Run crawler with: .\crawler.exe
    echo ============================================================
) else (
    echo.
    echo ============================================================
    echo BUILD FAILED! Please check errors above.
    echo ============================================================
)

echo.
pause
