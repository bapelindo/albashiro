@echo off
REM ============================================
REM Ollama GPU-Only Mode (Force All Layers to GPU)
REM ============================================

echo Starting Ollama with GPU-ONLY mode...
echo.

REM Force all layers to GPU (no CPU offloading)
set OLLAMA_NUM_GPU=999

REM Reduce context to fit in GPU VRAM
set OLLAMA_CONTEXT_LENGTH=2048

REM Disable CPU fallback
set OLLAMA_LLM_LIBRARY=

REM Keep alive for faster subsequent requests
set OLLAMA_KEEP_ALIVE=5m

REM Enable flash attention for efficiency
set OLLAMA_FLASH_ATTENTION=true

REM Max parallel requests
set OLLAMA_NUM_PARALLEL=1

REM Reduce GPU overhead
set OLLAMA_GPU_OVERHEAD=50000000

echo ========================================
echo GPU-Only Configuration:
echo   - All layers forced to GPU
echo   - Context length: 2048 tokens
echo   - No CPU fallback
echo   - Flash attention: enabled
echo   - Parallel requests: 1
echo ========================================
echo.

REM Start Ollama
ollama serve

pause
