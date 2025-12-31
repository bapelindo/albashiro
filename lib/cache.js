/**
 * In-Memory Cache Module
 * Simple caching for database queries and embeddings
 */

class MemoryCache {
    constructor() {
        this.cache = new Map();
        this.ttl = new Map(); // Time-to-live tracking
    }

    /**
     * Set cache value with optional TTL
     * @param {string} key - Cache key
     * @param {any} value - Value to cache
     * @param {number} ttlSeconds - Time to live in seconds (default: 3600)
     */
    set(key, value, ttlSeconds = 3600) {
        this.cache.set(key, value);

        // Set expiration time
        const expiresAt = Date.now() + (ttlSeconds * 1000);
        this.ttl.set(key, expiresAt);

        // Auto-cleanup after TTL
        setTimeout(() => {
            this.delete(key);
        }, ttlSeconds * 1000);
    }

    /**
     * Get cache value
     * @param {string} key - Cache key
     * @returns {any|null} Cached value or null if not found/expired
     */
    get(key) {
        // Check if exists
        if (!this.cache.has(key)) {
            return null;
        }

        // Check if expired
        const expiresAt = this.ttl.get(key);
        if (expiresAt && Date.now() > expiresAt) {
            this.delete(key);
            return null;
        }

        return this.cache.get(key);
    }

    /**
     * Check if key exists and is not expired
     * @param {string} key - Cache key
     * @returns {boolean}
     */
    has(key) {
        return this.get(key) !== null;
    }

    /**
     * Delete cache entry
     * @param {string} key - Cache key
     */
    delete(key) {
        this.cache.delete(key);
        this.ttl.delete(key);
    }

    /**
     * Clear all cache
     */
    clear() {
        this.cache.clear();
        this.ttl.clear();
    }

    /**
     * Get cache statistics
     * @returns {object} Cache stats
     */
    getStats() {
        return {
            size: this.cache.size,
            keys: Array.from(this.cache.keys())
        };
    }
}

// Export singleton instance
const cache = new MemoryCache();
export default cache;
