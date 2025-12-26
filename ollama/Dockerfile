FROM ollama/ollama:latest

# Install gemma2:2b model (lightweight, fast, supports Indonesian)
# This happens during build to speed up container startup
RUN ollama serve & sleep 5 && ollama pull gemma2:2b && pkill ollama

# Expose Ollama API port
EXPOSE 11434

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
  CMD curl -f http://localhost:11434/api/tags || exit 1

# Start Ollama server
CMD ["ollama", "serve"]
