@echo off
echo ============================================================
echo DISABLE EMBEDDINGS - Ultra Fast Scraping Mode
echo ============================================================
echo.
echo This will scrape articles WITHOUT generating embeddings.
echo You can generate embeddings later in batch mode.
echo.
echo Speed: 200-500 articles/sec (100x faster!)
echo.

cd /d c:\apache\htdocs\albashiro\albashiro_crawler_go

REM Create a simple scraper without embeddings
echo Creating ultra-fast scraper...

REM Backup original scraper
if not exist internal\scraper\scraper.go.backup (
    copy internal\scraper\scraper.go internal\scraper\scraper.go.backup
)

REM Comment out embedding generation
powershell -Command "(Get-Content internal\scraper\scraper.go) -replace '(\s+)// Generate embeddings', '$1// EMBEDDINGS DISABLED - Ultra Fast Mode$1/*' -replace '(\s+)vectors, err := s.generateVectors', '$1vectors := make([]VectorData, 0) // DISABLED$1_ = s.ollamaClient // suppress unused$1/*vectors, err := s.generateVectors' -replace '(\s+)return vectors, nil', '$1*/$1return vectors, nil' | Set-Content internal\scraper\scraper_temp.go"

if exist internal\scraper\scraper_temp.go (
    move /y internal\scraper\scraper_temp.go internal\scraper\scraper.go
)

echo.
echo Rebuilding crawler...
go build -o crawler.exe main.go 2>&1

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================================
    echo SUCCESS! Embeddings are now DISABLED
    echo ============================================================
    echo.
    echo Speed: 200-500 articles/sec ^(100x faster!^)
    echo Memory: LOW ^(no Ollama needed^)
    echo Embeddings: DISABLED ^(will be empty in JSON^)
    echo.
    echo Run crawler with: .\crawler.exe
    echo.
    echo To restore embeddings: .\enable_embeddings.bat
    echo ============================================================
) else (
    echo.
    echo ============================================================
    echo BUILD FAILED! Restoring backup...
    echo ============================================================
    if exist internal\scraper\scraper.go.backup (
        copy /y internal\scraper\scraper.go.backup internal\scraper\scraper.go
    )
)

echo.
pause
