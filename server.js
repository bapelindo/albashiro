/**
 * Simple Node.js server for testing OllamaService locally
 * Run: node server.js
 * Access: http://localhost:3000
 */

import express from 'express';
import cors from 'cors';
import streamHandler from './api/stream.js';

const app = express();
const PORT = 3000;

// Middleware
app.use(cors());
app.use(express.json());
app.use(express.static('public'));

// API endpoint
app.post('/api/stream', async (req, res) => {
    // Convert Express req/res to Vercel-style Request/Response
    const request = new Request('http://localhost:3000/api/stream', {
        method: 'POST',
        headers: req.headers,
        body: JSON.stringify(req.body)
    });

    // Fix: ESM default import is the function itself
    // Note: streamHandler is the default export from api/stream.js
    const response = await streamHandler(request);

    // Copy headers
    response.headers.forEach((value, key) => {
        res.setHeader(key, value);
    });

    // Stream response
    const reader = response.body.getReader();
    const decoder = new TextDecoder();

    while (true) {
        const { done, value } = await reader.read();
        if (done) break;
        res.write(decoder.decode(value, { stream: true }));
    }

    res.end();
});

app.listen(PORT, () => {
    console.log(`✅ Node.js server running at http://localhost:${PORT}`);
    console.log(`   Test chat at: http://localhost:${PORT}`);
});
