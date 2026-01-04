/**
 * Albashiro - Ollama AI Service (Node.js Complete Port)
 * Full migration from OllamaService.php (1460 lines)
 * All 27 methods with 100% feature parity
 */

import db from './database.js';
import cache from './cache.js';
import { OLLAMA_API_URL, OLLAMA_MODEL, SITE_NAME, TIMEZONE, USE_SEMANTIC_ROUTING } from './config.js';
import { readFile, writeFile } from 'fs/promises';
import { existsSync } from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import crypto from 'crypto';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

class OllamaService {
    constructor(host = null, model = null, timeout = 180) {
        this.baseUrl = (host || OLLAMA_API_URL).replace(/\/$/, '');
        this.model = model || OLLAMA_MODEL;
        this.timeout = timeout * 1000; // Convert to milliseconds

        // Embedding cache
        this.cacheFile = path.join(__dirname, '../cache/ollama_embeddings.json');
        this.embeddingCache = {};
        this.loadEmbeddingCache();

        // Knowledge search cache
        this.knowledgeCache = {};

        // Static properties for performance tracking
        this.lastKnowledgeMatchCount = 0;
        this.lastSearchKeywords = '';

        // Greeting pattern (from PHP)
        this.greetingPattern = /^(halo|hai|hi|assalamu'?alaikum|salam|selamat\s+(pagi|siang|sore|malam)|apa\s+kabar|permisi|mohon\s+maaf)/i;
    }

    /**
     * Get database connection
     */
    getDb() {
        return db;
    }

    // =====================================================
    // DATABASE METHODS
    // =====================================================

    /**
     * Get site settings from database
     * @returns {Promise<Object>} Site settings
     */
    async getSiteSettings() {
        const cacheKey = 'site_settings';
        const cached = cache.get(cacheKey);
        if (cached) return cached;

        const [rows] = await db.query('SELECT setting_key, setting_value FROM site_settings');
        const settings = {};
        rows.forEach(row => {
            settings[row.setting_key] = row.setting_value;
        });

        cache.set(cacheKey, settings, 3600); // 1 hour
        return settings;
    }

    /**
     * Get services information
     * @returns {Promise<string>} Formatted services info
     */
    async getServicesInfo() {
        const cacheKey = 'services_info';
        const cached = cache.get(cacheKey);
        if (cached) return cached;

        const [services] = await db.query(
            'SELECT name, price, duration FROM services ORDER BY sort_order'
        );

        let output = '';
        services.forEach(s => {
            const formattedPrice = new Intl.NumberFormat('id-ID').format(s.price);
            output += `- ${s.name}: Rp ${formattedPrice} (${s.duration})\n`;
        });

        const result = output || 'Data layanan belum tersedia.';
        cache.set(cacheKey, result, 3600);
        return result;
    }

    /**
     * Get therapists information
     * @returns {Promise<string>} Formatted therapists info
     */
    async getTherapistsInfo() {
        const cacheKey = 'therapists_info';
        const cached = cache.get(cacheKey);
        if (cached) return cached;

        const [therapists] = await db.query(
            'SELECT id, name, title, specialty FROM therapists WHERE is_active=1'
        );

        let output = '';
        therapists.forEach(t => {
            output += `- ${t.name} ${t.title} (${t.specialty})\n`;
        });

        const result = output || 'Data kosong.';
        cache.set(cacheKey, result, 3600);
        return result;
    }

    /**
     * Get testimonials information
     * @returns {Promise<string>} Formatted testimonials
     */
    async getTestimonialsInfo() {
        const cacheKey = 'testimonials_info';
        const cached = cache.get(cacheKey);
        if (cached) return cached;

        const [testimonials] = await db.query(
            'SELECT client_name, rating FROM testimonials WHERE is_featured=1 LIMIT 2'
        );

        let output = '';
        testimonials.forEach(t => {
            const stars = '⭐'.repeat(t.rating);
            output += `- ${t.client_name}: ${stars}\n`;
        });

        const result = output || 'Belum ada testimoni.';
        cache.set(cacheKey, result, 3600);
        return result;
    }

    // =====================================================
    // EMBEDDING & VECTOR METHODS
    // =====================================================

    /**
     * Generate embedding using Ollama
     * @param {string} text - Text to embed
     * @returns {Promise<Array<number>|null>} Embedding vector
     */
    async generateEmbedding(text) {
        if (!text || text.trim().length === 0) return null;

        // Check cache first
        const cacheKey = text.trim().toLowerCase();
        if (this.embeddingCache[cacheKey]) {
            return this.embeddingCache[cacheKey];
        }

        try {
            const response = await fetch(`${this.baseUrl}/api/embeddings`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    model: 'all-minilm', // Match PHP version for compatibility
                    prompt: text
                }),
                signal: AbortSignal.timeout(this.timeout)
            });

            if (!response.ok) {
                throw new Error(`Embedding API error: ${response.status}`);
            }

            const data = await response.json();
            const embedding = data.embedding;

            // Cache the result
            this.embeddingCache[cacheKey] = embedding;
            await this.saveEmbeddingCache();

            return embedding;
        } catch (error) {
            console.error('Embedding generation error:', error.message);
            return null;
        }
    }

    /**
     * Load embedding cache from file
     */
    async loadEmbeddingCache() {
        try {
            if (existsSync(this.cacheFile)) {
                const data = await readFile(this.cacheFile, 'utf8');
                this.embeddingCache = JSON.parse(data);
            }
        } catch (error) {
            console.error('Failed to load embedding cache:', error.message);
            this.embeddingCache = {};
        }
    }

    /**
     * Save embedding cache to file
     */
    async saveEmbeddingCache() {
        try {
            await writeFile(this.cacheFile, JSON.stringify(this.embeddingCache, null, 2));
        } catch (error) {
            console.error('Failed to save embedding cache:', error.message);
        }
    }

    /**
     * Calculate cosine similarity between two vectors
     * @param {Array<number>} vecA - First vector
     * @param {Array<number>} vecB - Second vector
     * @returns {number} Similarity score (0-1)
     */
    cosineSimilarity(vecA, vecB) {
        if (!vecA || !vecB || vecA.length !== vecB.length) return 0;

        let dotProduct = 0;
        let normA = 0;
        let normB = 0;

        for (let i = 0; i < vecA.length; i++) {
            dotProduct += vecA[i] * vecB[i];
            normA += vecA[i] * vecA[i];
            normB += vecB[i] * vecB[i];
        }

        normA = Math.sqrt(normA);
        normB = Math.sqrt(normB);

        if (normA === 0 || normB === 0) return 0;

        return dotProduct / (normA * normB);
    }

    /**
     * Vector search using TiDB native vector search
     * @param {string} queryText - Query text
     * @param {number} limit - Number of results
     * @param {Array<number>|null} inputVector - Pre-computed vector
     * @param {string|null} sourceTable - Optional source table filter (e.g., 'blog_posts')
     * @returns {Promise<Array>} Search results
     */
    async vectorSearch(queryText, limit = 3, inputVector = null, sourceTable = null) {
        try {
            const queryVector = inputVector || await this.generateEmbedding(queryText);
            if (!queryVector) return [];

            // Convert vector to string format for SQL
            const vectorStr = `[${queryVector.join(',')}]`;

            try {
                // Build query with optional source_table filter
                let query = `
                    SELECT 
                        id, source_table, source_id, article_id, content_text,
                        VEC_COSINE_DISTANCE(embedding, ?) AS distance
                    FROM knowledge_vectors
                    WHERE embedding IS NOT NULL
                `;
                const params = [vectorStr];

                if (sourceTable) {
                    query += ` AND source_table = ?`;
                    params.push(sourceTable);
                }

                query += ` ORDER BY distance ASC LIMIT ?`;
                params.push(limit);

                const [results] = await db.query(query, params);

                return results
                    .map(r => ({
                        ...r,
                        similarity: 1 - r.distance // Convert distance to similarity
                    }))
                    .filter(r => r.similarity > 0.4); // Threshold matching PHP version
            } catch (dbError) {
                console.warn(`⚠️ Vector Search DB Error (source: ${sourceTable || 'all'}): ${dbError.message}`);
                return []; // Fail safe: return empty results instead of crashing
            }
        } catch (error) {
            console.error('Vector search error:', error.message);
            return [];
        }
    }

    /**
     * Detect intent semantically using vector search
     * @param {string} userMessage - User's message
     * @param {Array<number>|null} inputVector - Pre-computed vector
     * @returns {Promise<Array<string>>} Detected intents
     */
    async detectIntentSemantic(userMessage, inputVector = null) {
        if (!USE_SEMANTIC_ROUTING) return [];

        try {
            const queryVector = inputVector || await this.generateEmbedding(userMessage);
            if (!queryVector) return [];

            const vectorStr = `[${queryVector.join(',')}]`;

            const [results] = await db.query(`
                SELECT 
                    intent,
                    VEC_COSINE_DISTANCE(embedding, ?) AS distance
                FROM router_intent
                WHERE embedding IS NOT NULL
                ORDER BY distance ASC
                LIMIT 3
            `, [vectorStr]);

            // Return intents with similarity > 0.7
            return results
                .filter(r => (1 - r.distance) > 0.7)
                .map(r => r.intent);
        } catch (error) {
            console.error('Intent detection error:', error.message);
            return [];
        }
    }

    /**
     * Update/Insert Vector for a specific source
     * Used for Real-time RAG Sync (Auto-Sync)
     * @param {string} table - Source table name
     * @param {number} id - Source ID
     * @param {string} text - Text to embed
     * @returns {Promise<boolean>} Success status
     */
    async upsertVector(table, id, text) {
        try {
            const vector = await this.generateEmbedding(text);
            if (!vector) return false;

            const vectorStr = `[${vector.join(',')}]`;

            // TiDB Upsert
            const sql = `
                INSERT INTO knowledge_vectors (source_table, source_id, content_text, embedding) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE content_text=?, embedding=?
            `;

            await db.query(sql, [table, id, text, vectorStr, text, vectorStr]);
            return true;
        } catch (error) {
            console.error('Vector upsert error:', error.message);
            return false;
        }
    }

    // =====================================================
    // KNOWLEDGE SEARCH METHODS
    // =====================================================

    /**
     * Extract keywords from message
     * @param {string} msg - User message
     * @returns {Array<string>} Keywords
     */
    extractKeywords(msg) {
        // Indonesian stopwords
        const stopwords = [
            'apa', 'yang', 'dan', 'atau', 'saya', 'bisa', 'tidak', 'ini', 'itu',
            'untuk', 'dari', 'dengan', 'pada', 'adalah', 'akan', 'ada', 'juga',
            'sudah', 'belum', 'kalau', 'kalo', 'jika', 'bila', 'mau', 'ingin',
            'dapat', 'harus', 'perlu', 'maka', 'jadi', 'lalu', 'kemudian',
            'sangat', 'sekali', 'lebih', 'paling', 'sama', 'lain', 'semua', 'setiap'
        ];

        const words = msg.toLowerCase().split(' ');
        const keywords = [];

        words.forEach(word => {
            // Remove punctuation
            word = word.replace(/[^\p{L}\p{N}]/gu, '');

            // Filter: length > 3 and not stopword
            if (word.length > 3 && !stopwords.includes(word)) {
                keywords.push(word);
            }
        });

        return [...new Set(keywords)]; // Unique keywords
    }

    /**
     * Search relevant knowledge from database
     * @param {string} query - Search query
     * @param {Function|null} onStatus - Status callback
     * @param {Array<number>|null} inputVector - Pre-computed vector
     * @returns {Promise<string>} Relevant knowledge
     */
    async searchRelevantKnowledge(query, onStatus = null, inputVector = null) {
        // Cache check
        const cacheKey = crypto.createHash('md5').update(query.toLowerCase().trim()).digest('hex');
        if (this.knowledgeCache[cacheKey]) {
            const cached = this.knowledgeCache[cacheKey];
            this.lastKnowledgeMatchCount = cached.count;
            this.lastSearchKeywords = cached.keywords;
            return cached.result;
        }

        const contextMatches = [];
        const seenContent = new Set();

        // 1. Vector Search (Semantic) - Highest Priority
        try {
            const vectorResults = await this.vectorSearch(query, 5, inputVector); // Increased to 5 for better coverage
            vectorResults.forEach(r => {
                const hash = crypto.createHash('md5').update(r.content_text).digest('hex');
                if (!seenContent.has(hash)) {
                    // Prioritize blog_posts, then other sources
                    const priority = r.source_table === 'blog_posts' ? 0 : 1;
                    const metadata = r.article_id ? ` [Article #${r.article_id}]` : '';
                    contextMatches.push({
                        content: r.content_text.substring(0, 500), // Truncate to 500 chars
                        priority,
                        source: r.source_table || 'unknown',
                        metadata
                    });
                    seenContent.add(hash);
                }
            });
        } catch (error) {
            // Vector search failed, continue with keyword search
        }

        // If vector match found, format and return
        if (contextMatches.length > 0) {
            // Sort by priority (blog_posts first)
            contextMatches.sort((a, b) => a.priority - b.priority);

            const result = contextMatches.map(m => m.content + m.metadata).join('\n---\n');
            this.knowledgeCache[cacheKey] = {
                result,
                count: contextMatches.length,
                keywords: '',
                sources: contextMatches.map(m => m.source).join(', ')
            };
            this.lastKnowledgeMatchCount = contextMatches.length;
            return result;
        }

        // 2. Keyword Search (Fallback)
        const keywords = this.extractKeywords(query);
        if (keywords.length === 0) return '';

        const topKeywords = keywords.slice(0, 3); // Limit to top 3
        this.lastSearchKeywords = topKeywords.join(', ');

        // 2a. Search FAQs
        try {
            const conditions = [];
            const params = [];
            topKeywords.forEach(k => {
                conditions.push('question LIKE ? OR answer LIKE ?');
                params.push(`${k}%`, `${k}%`);
            });

            const [faqs] = await db.query(
                `SELECT question, answer FROM faqs WHERE is_active=1 AND (${conditions.join(' OR ')}) LIMIT 3`,
                params
            );

            faqs.forEach(r => {
                const content = `Q: ${r.question}\nA: ${r.answer}`;
                const hash = crypto.createHash('md5').update(content).digest('hex');
                if (!seenContent.has(hash)) {
                    contextMatches.push({
                        content: content.substring(0, 500),
                        priority: 2, // Lower priority than vectors
                        source: 'faqs',
                        metadata: ''
                    });
                    seenContent.add(hash);
                }
            });

            if (contextMatches.length > 0) {
                contextMatches.sort((a, b) => a.priority - b.priority);
                const result = contextMatches.map(m => m.content + m.metadata).join('\n---\n');
                this.knowledgeCache[cacheKey] = {
                    result,
                    count: contextMatches.length,
                    keywords: this.lastSearchKeywords,
                    sources: contextMatches.map(m => m.source).join(', ')
                };
                this.lastKnowledgeMatchCount = contextMatches.length;
                return result;
            }
        } catch (error) {
            // FAQ search failed, continue
        }

        // 2b. Search Knowledge Vectors
        try {
            const conditions = topKeywords.map(() => 'content_text LIKE ?');
            const params = topKeywords.map(k => `%${k}%`); // Changed to %keyword% for better matching

            const [vectors] = await db.query(
                `SELECT content_text, source_table, article_id FROM knowledge_vectors WHERE ${conditions.join(' OR ')} LIMIT 5`,
                params
            );

            vectors.forEach(r => {
                const content = r.content_text.substring(0, 500);
                const hash = crypto.createHash('md5').update(content).digest('hex');
                if (!seenContent.has(hash)) {
                    const priority = r.source_table === 'blog_posts' ? 1 : 2;
                    const metadata = r.article_id ? ` [Article #${r.article_id}]` : '';
                    contextMatches.push({
                        content,
                        priority,
                        source: r.source_table || 'knowledge_vectors',
                        metadata
                    });
                    seenContent.add(hash);
                }
            });
        } catch (error) {
            // Knowledge vectors search failed, continue
        }

        // 2c. Search AI Knowledge Base
        try {
            const conditions = [];
            const params = [];
            topKeywords.forEach(k => {
                conditions.push('(question LIKE ? OR answer LIKE ?)');
                params.push(`${k}%`, `${k}%`);
            });

            const [aiKb] = await db.query(
                `SELECT question, answer FROM ai_knowledge_base WHERE is_active=1 AND (${conditions.join(' OR ')}) LIMIT 3`,
                params
            );

            aiKb.forEach(r => {
                const content = `Q: ${r.question}\nA: ${r.answer}`;
                const hash = crypto.createHash('md5').update(content).digest('hex');
                if (!seenContent.has(hash)) {
                    contextMatches.push({
                        content,
                        priority: 3, // Lowest priority
                        source: 'ai_knowledge_base',
                        metadata: ''
                    });
                    seenContent.add(hash);
                }
            });
        } catch (error) {
            // AI KB search failed, continue
        }

        // Sort by priority and format result
        contextMatches.sort((a, b) => a.priority - b.priority);
        const result = contextMatches.map(m => m.content + m.metadata).join('\n---\n');
        this.knowledgeCache[cacheKey] = {
            result,
            count: contextMatches.length,
            keywords: this.lastSearchKeywords,
            sources: contextMatches.map(m => m.source).join(', ')
        };
        this.lastKnowledgeMatchCount = contextMatches.length;
        return result;
    }

    /**
     * Get last knowledge match count
     * @returns {number} Match count
     */
    getLastKnowledgeMatchCount() {
        return this.lastKnowledgeMatchCount;
    }

    /**
     * Get last knowledge sources (for debugging/monitoring)
     * @returns {string} Comma-separated source list
     */
    getLastKnowledgeSources() {
        const cacheKeys = Object.keys(this.knowledgeCache);
        if (cacheKeys.length === 0) return '';

        const lastKey = cacheKeys[cacheKeys.length - 1];
        return this.knowledgeCache[lastKey]?.sources || '';
    }

    // =====================================================
    // SENTIMENT & EXTRACTION METHODS
    // =====================================================

    /**
     * Analyze user sentiment
     * @param {string} msg - User message
     * @returns {string} Sentiment (CURIOUS, ANXIETY, SADNESS, ANGER, URGENT, NEUTRAL)
     */
    analyzeSentiment(msg) {
        const msgLower = msg.toLowerCase();

        // Priority 1: Curiosity / Buying Intent
        if (/(harga|biaya|lokasi|alamat|jadwal|pesan|booking|daftar)/.test(msgLower)) {
            return 'CURIOUS';
        }

        // Anxiety / Fear
        if (/(cemas|takut|panik|khawatir|gelisah|deg-degan|mati|bahaya)/.test(msgLower)) {
            return 'ANXIETY';
        }

        // Sadness / Depression
        if (/(sedih|nangis|putus asa|lelah|capek|sendiri|sepi|hampa)/.test(msgLower)) {
            return 'SADNESS';
        }

        // Anger / Frustration
        if (/(marah|kesal|benci|dendam|kecewa|bohong|penipu)/.test(msgLower)) {
            return 'ANGER';
        }

        // Critical / Urgent
        if (/(darurat|bantu|tolong|sakit|parah)/.test(msgLower)) {
            return 'URGENT';
        }

        return 'NEUTRAL';
    }

    /**
     * Extract therapist name from message
     * @param {string} msg - User message
     * @returns {Promise<string|null>} Therapist name or null
     */
    async extractTherapistFromMessage(msg) {
        const [therapists] = await db.query('SELECT name FROM therapists WHERE is_active=1');
        const msgLower = msg.toLowerCase();

        for (const t of therapists) {
            const name = t.name;

            // Check full name match
            if (msgLower.includes(name.toLowerCase())) {
                return name;
            }

            // Check partial name
            const nameParts = name.split(' ');
            for (const part of nameParts) {
                // Skip titles and short words
                if (part.length <= 2 || ['hj', 'dr', 's.psi', 'ch.t', 'ci', 'spd.i'].includes(part.toLowerCase())) {
                    continue;
                }

                if (msgLower.includes(part.toLowerCase())) {
                    return name;
                }

                // Check nickname (first 4 chars)
                if (part.length > 4) {
                    const shortName = part.substring(0, 4).toLowerCase();
                    if (msgLower.includes(shortName)) {
                        return name;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Extract date from message
     * @param {string} msg - User message
     * @returns {string|null} Date in Y-m-d format or null
     */
    extractDateFromMessage(msg) {
        const msgLower = msg.toLowerCase();

        if (!msg) return null;

        // Relative dates
        if (msgLower.includes('besok')) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            return tomorrow.toISOString().split('T')[0];
        }

        if (msgLower.includes('lusa')) {
            const dayAfter = new Date();
            dayAfter.setDate(dayAfter.getDate() + 2);
            return dayAfter.toISOString().split('T')[0];
        }

        if (msgLower.includes('hari ini')) {
            return new Date().toISOString().split('T')[0];
        }

        // Numeric dates (e.g., "tanggal 5", "tgl 25", "5 januari")
        const dateMatch = msgLower.match(/(tanggal|tgl)\s*(\d+)/i);
        if (dateMatch) {
            const day = parseInt(dateMatch[2]);
            let month = new Date().getMonth() + 1;
            let year = new Date().getFullYear();

            const months = {
                'januari': 1, 'februari': 2, 'maret': 3, 'april': 4,
                'mei': 5, 'juni': 6, 'juli': 7, 'agustus': 8,
                'september': 9, 'oktober': 10, 'november': 11, 'desember': 12,
                'jan': 1, 'feb': 2, 'mar': 3, 'apr': 4,
                'jun': 6, 'jul': 7, 'agust': 8, 'sep': 9,
                'okt': 10, 'nov': 11, 'des': 12
            };

            for (const [name, num] of Object.entries(months)) {
                if (msgLower.includes(name)) {
                    month = num;
                    break;
                }
            }

            // If date has passed this year, assume next year
            const targetDate = new Date(year, month - 1, day);
            if (targetDate < new Date()) {
                year++;
            }

            return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        }

        return null;
    }

    // =====================================================
    // SCHEDULE METHODS
    // =====================================================

    /**
     * Get specific slots for a therapist on a given date
     * @param {number} therapistId
     * @param {string} date - YYYY-MM-DD
     * @returns {Promise<string[]>} Array of time slots e.g. ['09.00', '10.00']
     */
    async getTherapistSlots(therapistId, date) {
        try {
            const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            const dateObj = new Date(date);
            const dayName = days[dateObj.getDay()];

            const [rows] = await db.query(`
                SELECT start_time, end_time 
                FROM therapist_availability 
                WHERE therapist_id = ? AND day_of_week = ? AND is_available = 1
                LIMIT 1
            `, [therapistId, dayName]);

            if (!rows || rows.length === 0) {
                // Return empty if no schedule found (e.g. Sunday/Closed)
                return [];
            }

            const startStr = rows[0].start_time; // "09:00:00"
            const endStr = rows[0].end_time;     // "17:00:00"

            const startHour = parseInt(startStr.split(':')[0], 10);
            const endHour = parseInt(endStr.split(':')[0], 10);

            const slots = [];
            // Generate hourly slots
            // Using <= endHour to include the closing hour as a start slot if 17:00 is desired
            for (let h = startHour; h <= endHour; h++) {
                const hourStr = h.toString().padStart(2, '0');
                slots.push(`${hourStr}.00`);
            }
            return slots;
        } catch (error) {
            console.error('Error fetching therapist slots:', error.message);
            return [];
        }
    }

    /**
     * Get all therapists schedules for a date
     * @param {string} date - Date in Y-m-d format
     * @returns {Promise<string>} Formatted schedule info
     */
    async getAllTherapistsSchedules(date) {
        try {
            const [therapists] = await db.query('SELECT id, name FROM therapists WHERE is_active=1 ORDER BY id');
            const [bookings] = await db.query(`
                SELECT therapist_id, appointment_time 
                FROM bookings 
                WHERE DATE(appointment_date) = ? 
                AND status IN ('confirmed', 'pending')
            `, [date]);

            let output = `Jadwal tersedia untuk ${date}:\n\n`;
            let hasAnySchedule = false;

            for (const therapist of therapists) {
                const allRefSlots = await this.getTherapistSlots(therapist.id, date);

                // Filter bookings for this therapist
                const therapistBookings = bookings
                    .filter(b => b.therapist_id === therapist.id)
                    .map(b => b.appointment_time.substring(0, 5).replace(':', '.'));

                // Calculate available slots
                const availableSlots = allRefSlots.filter(s => !therapistBookings.includes(s));

                // Map to object structure
                const slots = availableSlots.map(time => ({ time_slot: time }));

                if (slots.length > 0) {
                    hasAnySchedule = true;
                    output += `${therapist.name}:\n`;
                    slots.forEach(s => {
                        output += `  - ${s.time_slot}\n`;
                    });
                    output += '\n';
                }
            }

            return hasAnySchedule ? output : 'Tidak ada jadwal tersedia.';
        } catch (error) {
            console.error('Schedule fetch error:', error.message);
            return 'Gagal mengambil jadwal.';
        }
    }

    /**
     * Get available schedules for specific therapist
     * @param {string} date - Date in Y-m-d format
     * @param {string|null} therapistName - Therapist name
     * @returns {Promise<string>} Formatted schedule info
     */
    async getAvailableSchedules(date, therapistName = null) {
        try {
            let params = [date];
            let therapistSnippet = '';

            // Resolve therapist to get canonical name if provided
            let resolvedTherapist = null;
            if (therapistName) {
                const [rows] = await db.query('SELECT id, name FROM therapists WHERE name LIKE ? AND is_active=1 LIMIT 1', [`%${therapistName}%`]);
                if (rows.length > 0) {
                    resolvedTherapist = rows[0];
                    therapistSnippet = ' AND therapist_id = ?';
                    params.push(resolvedTherapist.id);
                }
            }

            const [bookings] = await db.query(`
                SELECT therapist_id, appointment_time 
                FROM bookings 
                WHERE DATE(appointment_date) = ? 
                AND status IN ('confirmed', 'pending')
                ${therapistSnippet}
            `, params);

            const allTherapists = resolvedTherapist ? [resolvedTherapist] : (await db.query('SELECT id, name FROM therapists WHERE is_active=1'))[0];

            let outputSlots = [];

            for (const therapist of allTherapists) {
                const allRefSlots = await this.getTherapistSlots(therapist.id, date);

                const therapistBookings = bookings
                    .filter(b => b.therapist_id === therapist.id)
                    .map(b => b.appointment_time.substring(0, 5).replace(':', '.'));

                const availableForTherapist = allRefSlots.filter(s => !therapistBookings.includes(s));
                availableForTherapist.forEach(time => {
                    outputSlots.push({
                        time_slot: time,
                        therapist_name: therapist.name
                    });
                });
            }

            // Sort by time
            outputSlots.sort((a, b) => a.time_slot.localeCompare(b.time_slot));

            if (outputSlots.length === 0) {
                return 'Tidak ada jadwal tersedia.';
            }

            let output = `Jadwal tersedia untuk ${date}:\n`;
            outputSlots.forEach(s => {
                output += `- ${s.time_slot} (${s.therapist_name})\n`;
            });

            return output;

        } catch (error) {
            console.error('Schedule fetch error:', error.message);
            return 'Gagal mengambil jadwal.';
        }
    }

    // =====================================================
    // CONTEXT BUILDING (MOST IMPORTANT METHOD)
    // =====================================================

    /**
     * Build system context with smart injection
     * @param {string} userMessage - User's message
     * @param {Object} perfData - Performance data object
     * @param {Function|null} onStatus - Status callback
     * @param {boolean} hasHistory - Has conversation history
     * @returns {Promise<string>} System context
     */
    async buildSystemContext(userMessage = '', perfData = {}, onStatus = null, hasHistory = false) {
        // Initialize flags
        let needsServices = false;
        let needsTherapists = false;
        let needsSchedule = false;
        let needsTestimonials = false;
        let needsContact = false;

        // Initialize data variables
        let servicesInfo = '';
        let therapistsInfo = '';
        let testimonialsInfo = '';
        let scheduleInfo = '';
        let contactInfo = '';
        let relevantKnowledge = '';

        let computedVector = null;

        // Semantic Routing (if enabled)
        if (USE_SEMANTIC_ROUTING) {
            computedVector = await this.generateEmbedding(userMessage);
            if (computedVector) {
                const detectedIntents = await this.detectIntentSemantic(userMessage, computedVector);
                detectedIntents.forEach(intent => {
                    if (intent === 'PRICE') needsServices = true;
                    if (intent === 'SCHEDULE') needsSchedule = true;
                    if (intent === 'THERAPIST') needsTherapists = true;
                    if (intent === 'CONTACT') needsContact = true;
                });
            }
        }

        // Regex supplement (always run)
        if (!needsServices) {
            needsServices = /(layanan|service|paket|harga|biaya|price|tarif|berapa.*biaya|berapa.*harga|berapa.*terapi)/i.test(userMessage);
        }

        if (!needsTherapists) {
            needsTherapists = /(terapis|therapist|dokter|bunda|ustadzah|siapa.*terapis|profil.*terapis)/i.test(userMessage);
        }

        if (!needsSchedule) {
            needsSchedule = /(jadwal|tersedia|booking|slot|kosong|kapan.*bisa|ada.*kosong|jam.*praktek|jam.*buka|reservasi|janji.*temu|hari.*apa)/i.test(userMessage);
        }

        if (!needsTestimonials) {
            needsTestimonials = /(testimoni|review|pengalaman|hasil.*terapi|berhasil.*terapi)/i.test(userMessage);
        }

        if (!needsContact) {
            needsContact = /(alamat|lokasi|dimana.*praktek|kantor|tempat|wa|telp|hubungi|contact|arah|maps|peta)/i.test(userMessage);
        }

        // Fetch data based on flags
        if (needsServices) {
            servicesInfo = await this.getServicesInfo();
        }

        if (needsTherapists) {
            therapistsInfo = await this.getTherapistsInfo();
        }

        if (needsTestimonials) {
            testimonialsInfo = await this.getTestimonialsInfo();
        }

        // Check for greetings and time checks
        const isGreeting = this.greetingPattern.test(userMessage.trim());
        const isTimeCheck = /(jam|pukul|tanggal|hari).*(berapa|apa|sekarang)/i.test(userMessage) &&
            !/(buka|tutup|praktek|jadwal)/i.test(userMessage);

        // Contact info already injected in main context above
        // No need for conditional injection

        // Knowledge search (skip for greetings and specific data requests)
        const hasSpecificData = needsServices || needsTherapists || needsSchedule || needsTestimonials || isTimeCheck || needsContact;

        if (userMessage && !isGreeting && !isTimeCheck && !hasSpecificData) {
            relevantKnowledge = await this.searchRelevantKnowledge(userMessage, onStatus, computedVector);
            if (perfData) {
                perfData.knowledge_matched = this.lastKnowledgeMatchCount;
                perfData.keywords_searched = this.lastSearchKeywords;
            }
        }

        // Schedule (if needed)
        if (needsSchedule && !isGreeting) {
            const queryDate = this.extractDateFromMessage(userMessage) || new Date().toISOString().split('T')[0];
            const therapistName = await this.extractTherapistFromMessage(userMessage);

            if (therapistName === null) {
                scheduleInfo = await this.getAllTherapistsSchedules(queryDate);
            } else {
                scheduleInfo = await this.getAvailableSchedules(queryDate, therapistName);
            }
        }

        // Get site settings (always needed for contact info)
        const settings = await this.getSiteSettings();

        // Analyze sentiment
        const sentiment = this.analyzeSentiment(userMessage);
        if (perfData) {
            perfData.user_sentiment = sentiment;
        }

        // Build context
        const siteName = settings.site_name || settings.name || SITE_NAME;
        const siteTagline = settings.site_tagline || settings.tagline || 'Islamic Spiritual Hypnotherapy';
        const whatsappAdmin = settings.admin_whatsapp || '+62 822 2896 7897';

        let context = `IDENTITAS AI:\n`;
        context += `Nama: Asisten Albashiro\n`;
        context += `Peran: Chatbot Islamic Spiritual Hypnotherapy yang empatik dan profesional\n`;
        context += `Tujuan: Membantu klien dengan penuh perhatian, memberikan informasi akurat, jika perlu arahkan ke terapis profesional\n\n`;

        context += `IDENTITAS SITUS:\n`;
        context += `Nama Situs: ${siteName}\n`;
        context += `Tagline: ${siteTagline}\n`;
        context += `WhatsApp Admin: ${whatsappAdmin}\n`;
        context += `Lokasi: ${settings.address || 'Jl. Imam Bonjol No. 123, Jakarta Pusat, DKI Jakarta 10310'}\n`;
        if (settings.admin_email) {
            context += `Email: ${settings.admin_email}\n`;
        }
        if (settings.gmaps_link) {
            context += `Maps: ${settings.gmaps_link}\n`;
        }
        context += `\n`;

        // Inject time (only if needed)
        if (isTimeCheck || needsSchedule) {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dayName = days[now.getDay()];
            const date = `${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
            const time = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
            context += `WAKTU: ${dayName}, ${date} pukul ${time} WIB\n\n`;
        }

        // Sentiment-based response adjustment
        switch (sentiment) {
            case 'URGENT':
                context += `PENTING: User tampak membutuhkan bantuan segera. Berikan respons yang sangat empatik dan tawarkan solusi cepat. Prioritaskan keselamatan dan kesehatan mental mereka. Gunakan nada yang menenangkan namun responsif.\n\n`;
                break;
            case 'ANXIETY':
                context += `PERHATIAN: User tampak cemas atau khawatir. Berikan respons yang menenangkan dan reassuring. Fokus pada solusi praktis. Hindari informasi yang bisa menambah kecemasan. Gunakan bahasa yang lembut dan supportif.\n\n`;
                break;
            case 'SADNESS':
                context += `EMPATI: User tampak sedih atau down. Berikan dukungan emosional yang lembut. Tunjukkan empati dan pengertian mendalam. Tawarkan bantuan dengan cara yang supportif dan non-judgmental. Validasi perasaan mereka.\n\n`;
                break;
            case 'ANGER':
                context += `TENANG: User tampak frustrasi atau kecewa. Tetap tenang dan profesional. Akui perasaan mereka dengan validasi. Fokus pada solusi konstruktif. Hindari nada defensif. Tunjukkan bahwa Anda memahami dan siap membantu.\n\n`;
                break;
            case 'CURIOUS':
                context += `INFORMATIF: User sedang mencari informasi (harga/jadwal/booking). Berikan jawaban yang jelas, terstruktur, dan lengkap. Sertakan detail praktis. Proaktif tawarkan bantuan lebih lanjut. Gunakan nada yang helpful dan encouraging.\n\n`;
                break;
        }

        // Inject data (priority order: lowest to highest)
        if (relevantKnowledge) {
            context += `\n\nKB:\n${relevantKnowledge}`;
        }

        if (servicesInfo) {
            context += `\n\nLAYANAN:\n${servicesInfo}`;
        }

        if (therapistsInfo) {
            context += `\n\nTERAPIS:\n${therapistsInfo}`;
        }

        if (testimonialsInfo) {
            context += `\n\nTESTI:\n${testimonialsInfo}`;
        }

        // Real-time schedule (HIGHEST PRIORITY)
        if (scheduleInfo) {
            context += `\n\nJADWAL (INFO TERKINI - PRIORITAS UTAMA):\n${scheduleInfo}`;
            context += `\n[INSTRUKSI: Gunakan data JADWAL di atas sebagai kebenaran mutlak. Abaikan info jadwal lain di bagian KB jika bertentangan.]`;
        }

        return context;
    }

    // =====================================================
    // STREAMING METHODS
    // =====================================================

    /**
     * Generate streaming chat response from Ollama
     * @param {Array} messages - Messages array
     * @param {Function|null} onToken - Token callback
     * @returns {Promise<Object>} Response with metadata
     */
    async generateChatStream(messages, onToken = null) {
        try {
            const response = await fetch(`${this.baseUrl}/api/chat`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    model: this.model,
                    messages: messages,
                    stream: true,
                    keep_alive: '60m',  // Keep model loaded for 60 mins (faster subsequent)
                    // Ollama parameters (from PHP version)
                    options: {
                        temperature: 0.5,       // Balanced creativity
                        top_k: 20,              // Standard diversity
                        top_p: 0.9,             // Natural language flow
                        repeat_penalty: 1.15,   // Prevent repetition
                        num_ctx: 1024,          // Context window
                        num_predict: 1024,      // Max response tokens
                        num_gpu: 99,            // Force all layers to GPU
                        num_thread: 1,          // Minimal CPU threads
                        num_batch: 1024         // Batch size
                    }
                }),
                signal: AbortSignal.timeout(this.timeout)
            });

            if (!response.ok) {
                throw new Error(`Ollama API error: ${response.status}`);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let fullResponse = '';
            let metrics = {};

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop() || '';

                for (const line of lines) {
                    if (!line.trim()) continue;

                    try {
                        const chunk = JSON.parse(line);

                        if (chunk.message?.content) {
                            const token = chunk.message.content;
                            fullResponse += token;

                            if (onToken && typeof onToken === 'function') {
                                onToken(token);
                            }
                        }

                        if (chunk.done) {
                            metrics = {
                                total_duration: chunk.total_duration,
                                load_duration: chunk.load_duration,
                                prompt_eval_count: chunk.prompt_eval_count,
                                prompt_eval_duration: chunk.prompt_eval_duration,
                                eval_count: chunk.eval_count,
                                eval_duration: chunk.eval_duration
                            };
                        }
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError.message);
                    }
                }
            }

            return {
                response: fullResponse,
                metrics: metrics
            };
        } catch (error) {
            console.error('Chat stream error:', error.message);
            throw error;
        }
    }

    /**
     * Main chat stream method (complete port from PHP)
     * @param {string} userMessage - User's message
     * @param {Array} conversationHistory - Conversation history
     * @param {Function|null} onToken - Token callback
     * @param {Function|null} onStatus - Status callback
     * @param {boolean} skipAutoLearning - Skip auto-learning
     * @param {boolean} skipRAG - Skip RAG context
     * @returns {Promise<Object>} Response with metadata
     */
    async chatStream(userMessage, conversationHistory = [], onToken = null, onStatus = null, skipAutoLearning = false, skipRAG = false) {
        const startTime = Date.now();

        // Performance tracking
        const perfData = {
            session_id: 'nodejs-session',
            user_message: userMessage.substring(0, 500),
            ai_response: '',
            provider: 'Local Ollama (Streaming)',
            model: this.model,
            total_time_ms: 0,
            api_call_time_ms: null,
            context_build_time_ms: null,
            knowledge_matched: 0,
            keywords_searched: '',
            user_sentiment: 'NEUTRAL',
            error_occurred: 0,
            error_message: null
        };

        // Early return for empty messages
        if (!userMessage || !userMessage.trim()) {
            return {
                response: 'Maaf, pesan Anda kosong. Silakan kirim pertanyaan.',
                metadata: {
                    provider: 'validation',
                    response_time_ms: 0
                }
            };
        }

        // Send thinking status
        if (onStatus && typeof onStatus === 'function') {
            onStatus('Sedang memproses dengan penuh perhatian...');
        }

        // Build messages array
        const messages = [];

        // Add conversation history
        if (conversationHistory && conversationHistory.length > 0) {
            const historyToUse = conversationHistory.slice(-8); // Last 8 messages
            historyToUse.forEach(msg => {
                const role = msg.role === 'ai' ? 'assistant' : 'user';
                const content = msg.message || msg.content || '';
                messages.push({ role, content });
            });
        }

        // Build system context
        const contextStart = Date.now();
        let systemContext = '';

        if (!skipRAG) {
            const hasHistory = messages.length > 0;
            systemContext = await this.buildSystemContext(userMessage, perfData, onStatus, hasHistory);
            perfData.context_build_time_ms = Date.now() - contextStart;
        }

        // For custom model (albashiro), prepend context to user message
        const isCustomModel = this.model.includes('albashiro');

        // Check for duplicate last message
        const lastMsg = messages[messages.length - 1];
        const isDuplicate = lastMsg && lastMsg.role === 'user' && lastMsg.content.trim() === userMessage.trim();

        if (!isDuplicate) {
            if (isCustomModel && systemContext) {
                const userMessageWithContext = `<context>\n${systemContext}\n</context>\n\n<user_query>\n${userMessage}\n</user_query>`;
                messages.push({ role: 'user', content: userMessageWithContext });
            } else {
                messages.push({ role: 'user', content: userMessage });
            }
        }

        try {
            // Call streaming API
            const apiStart = Date.now();
            const result = await this.generateChatStream(messages, onToken);
            perfData.api_call_time_ms = Date.now() - apiStart;

            perfData.ai_response = result.response.substring(0, 500);
            perfData.total_time_ms = Date.now() - startTime;

            return {
                response: result.response,
                metadata: {
                    provider: 'Ollama',
                    model: this.model,
                    response_time_ms: perfData.total_time_ms,
                    context_build_time_ms: perfData.context_build_time_ms,
                    api_call_time_ms: perfData.api_call_time_ms,
                    knowledge_matched: perfData.knowledge_matched,
                    knowledge_sources: this.getLastKnowledgeSources(), // Track which sources contributed
                    user_sentiment: perfData.user_sentiment,
                    metrics: result.metrics
                }
            };
        } catch (error) {
            perfData.error_occurred = 1;
            perfData.error_message = error.message;
            perfData.total_time_ms = Date.now() - startTime;

            console.error('Chat stream error:', error);

            return {
                response: 'Maaf, terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi.',
                metadata: {
                    provider: 'error',
                    error: error.message,
                    response_time_ms: perfData.total_time_ms
                }
            };
        }
    }

    /**
     * Log performance data to database
     * @param {Object} perfData - Performance data
     */
    async logPerformance(perfData) {
        try {
            await db.query(`
                INSERT INTO ai_performance_logs 
                (session_id, user_message, ai_response, provider, model, total_time_ms, 
                 api_call_time_ms, context_build_time_ms, knowledge_matched, keywords_searched,
                 user_sentiment, error_occurred, error_message)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `, [
                perfData.session_id,
                perfData.user_message,
                perfData.ai_response,
                perfData.provider,
                perfData.model,
                perfData.total_time_ms,
                perfData.api_call_time_ms,
                perfData.context_build_time_ms,
                perfData.knowledge_matched,
                perfData.keywords_searched,
                perfData.user_sentiment,
                perfData.error_occurred,
                perfData.error_message
            ]);
        } catch (error) {
            console.error('Performance logging error:', error.message);
        }
    }
}

export default OllamaService;
