@echo off
title Albashiroh AI Service Launcher
set OLLAMA_MODELS=D:\ollama\models
set OLLAMA_IGPU_ENABLE=1

echo [1/2] Checking Ollama Service...
curl.exe -s http://127.0.0.1:11434/api/tags >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo Starting Ollama Server in background...
    start "" /B "C:\Users\ahnri\AppData\Local\Programs\Ollama\ollama.exe" serve
    timeout /t 3 /nobreak >nul
) else (
    echo Ollama Server is already active.
)

echo [2/2] Starting Albashiroh Node.js Streaming Server on Port 3001...
cd /d "C:\apache\htdocs\albashiroh"
node server.js
