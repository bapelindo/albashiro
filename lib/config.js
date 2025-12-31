/**
 * Configuration Module
 * Centralized configuration for Node.js service
 */

// Environment detection
const isVercel = process.env.VERCEL === '1';
const forceLocalhost = process.env.USE_LOCAL_OLLAMA === 'true';

// Ollama Configuration
// Default to production (https://ollama.bapel.my.id) unless explicitly set to localhost
export const OLLAMA_API_URL = forceLocalhost
    ? 'http://localhost:11434'
    : (process.env.OLLAMA_API_URL || 'https://ollama.bapel.my.id');

export const OLLAMA_MODEL = process.env.OLLAMA_MODEL || 'albashiro';

// Site Configuration
export const SITE_NAME = 'Albashiro';
export const SITE_TAGLINE = 'Islamic Spiritual Hypnotherapy';

// Semantic Routing (Experimental)
export const USE_SEMANTIC_ROUTING = false; // Disable for now (memory intensive)

// Cache Configuration
export const CACHE_TTL = 3600; // 1 hour in seconds

// Timezone
export const TIMEZONE = 'Asia/Jakarta';

// Export environment info
export const ENV = {
    isVercel,
    forceLocalhost,
    ollamaUrl: OLLAMA_API_URL,
    nodeEnv: process.env.NODE_ENV || 'development'
};
