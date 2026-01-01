@echo off
echo ============================================================
echo ENABLE AI CHECK - Accurate Mode
echo ============================================================
echo.
echo Updating config to enable Ollama AI relevance check...
echo This will be slower but more accurate!
echo.

cd /d c:\apache\htdocs\albashiro\albashiro_crawler_go

REM Update config.go to set UseOllama to true
powershell -Command "(Get-Content internal\config\config.go) -replace 'UseOllama:\s+getEnvBool\(\"USE_OLLAMA\",\s+false\)', 'UseOllama:            getEnvBool(\"USE_OLLAMA\", true)' | Set-Content internal\config\config.go"

echo Config updated: UseOllama = true
echo.
echo Rebuilding crawler...
echo.

go build -o crawler.exe main.go 2>&1

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================================
    echo SUCCESS! AI Check is now ENABLED
    echo ============================================================
    echo.
    echo Speed: 10-20 articles/sec ^(more accurate^)
    echo Ollama: REQUIRED ^(must be running^)
    echo Embeddings: ENABLED ^(all-minilm^)
    echo.
    echo Make sure Ollama is running: ollama serve
    echo Then run crawler with: .\crawler.exe
    echo ============================================================
) else (
    echo.
    echo ============================================================
    echo BUILD FAILED! Please check errors above.
    echo ============================================================
)

echo.
pause
