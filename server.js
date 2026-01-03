/**
 * Robust Node.js server for Albashiro AI
 * Run: node server.js
 * Access: http://localhost:3000
 */

import express from 'express';
import cors from 'cors';
import streamHandler from './api/stream.js';
import { OLLAMA_API_URL, OLLAMA_MODEL } from './lib/config.js';

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// Process-level error handling to prevent crash
process.on('uncaughtException', (err) => {
    console.error('❌ UNCAUGHT EXCEPTION:', err);
    // Don't exit, try to keep running
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ UNHANDLED REJECTION:', reason);
    // Don't exit
});

// API endpoint with error boundary
app.post('/api/stream', async (req, res) => {
    try {
        // Log incoming request
        // console.log(`[REQ] ${req.body.message ? req.body.message.substring(0, 50) : 'No msg'}`);

        // Convert Express req/res to Vercel-style Request/Response
        const request = new Request('http://localhost:3000/api/stream', {
            method: 'POST',
            headers: req.headers,
            body: JSON.stringify(req.body)
        });

        // Use core handler
        const response = await streamHandler(request);

        // Check if handler returned error response
        if (response.status >= 400) {
            console.error(`handler returned status ${response.status}`);
        }

        // Copy headers
        response.headers.forEach((value, key) => {
            res.setHeader(key, value);
        });

        // Set status
        res.status(response.status);

        // Stream response
        const reader = response.body.getReader();
        const decoder = new TextDecoder();

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            res.write(decoder.decode(value, { stream: true }));
        }

        res.end();
    } catch (error) {
        console.error('SERVER ERROR:', error);
        if (!res.headersSent) {
            res.status(500).json({
                error: true,
                message: "Server Error: " + error.message
            });
        }
    }
});

app.listen(PORT, () => {
    console.log('=================================================');
    console.log(`✅ Node.js server running at http://localhost:${PORT}`);
    console.log(`🤖 AI Model: ${OLLAMA_MODEL}`);
    console.log(`🔗 Ollama URL: ${OLLAMA_API_URL}`);
    console.log('=================================================');
});
