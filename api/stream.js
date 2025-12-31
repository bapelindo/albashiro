// Vercel Serverless Function - Node.js Streaming with Full OllamaService
// Complete migration from PHP - uses OllamaService.js for context building

import OllamaService from '../lib/ollama-service.js';

export const config = {
    runtime: 'nodejs', // Use Node.js runtime for mysql2 support
    maxDuration: 60, // 60 seconds timeout
};

export default async function handler(req) {
    const corsHeaders = {
        'Access-Control-Allow-Origin': '*', // Atau spesifik 'https://albashiro.bapel.my.id'
        'Access-Control-Allow-Methods': 'POST, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type',
    };

    // Handle Preflight OPTIONS request
    if (req.method === 'OPTIONS') {
        return new Response(null, { status: 204, headers: corsHeaders });
    }

    // Only allow POST requests
    if (req.method !== 'POST') {
        return new Response(JSON.stringify({ error: 'Method not allowed' }), {
            status: 405,
            headers: { 'Content-Type': 'application/json', ...corsHeaders },
        });
    }

    try {
        // Parse request body
        const { message, history = [] } = await req.json();

        if (!message || typeof message !== 'string') {
            return new Response(JSON.stringify({ error: 'Message is required' }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' },
            });
        }

        // Initialize OllamaService
        const ollamaService = new OllamaService();

        // Build messages using OllamaService (includes full context)
        const conversationHistory = history.map(h => ({
            role: h.role,
            message: h.message
        }));

        // Create SSE response
        const { readable, writable } = new TransformStream();
        const writer = writable.getWriter();
        const encoder = new TextEncoder();

        // Stream chat in background
        (async () => {
            try {
                // Token callback
                const onToken = async (token) => {
                    const sseData = {
                        token: token,
                        done: false
                    };
                    await writer.write(
                        encoder.encode(`data: ${JSON.stringify(sseData)}\n\n`)
                    );
                };

                // Status callback
                const onStatus = async (status) => {
                    const sseData = {
                        status: status
                    };
                    await writer.write(
                        encoder.encode(`data: ${JSON.stringify(sseData)}\n\n`)
                    );
                };

                // Call chatStream with full context building
                const result = await ollamaService.chatStream(
                    message,
                    conversationHistory,
                    onToken,
                    onStatus,
                    false, // skipAutoLearning
                    false  // skipRAG
                );

                // Send completion event
                await writer.write(
                    encoder.encode(`data: ${JSON.stringify({ done: true })}\n\n`)
                );

            } catch (error) {
                console.error('Stream processing error:', error);
                await writer.write(
                    encoder.encode(`data: ${JSON.stringify({
                        error: true,
                        message: error.message
                    })}\n\n`)
                );
            } finally {
                await writer.close();
            }
        })();

        // Return SSE stream
        return new Response(readable, {
            headers: {
                'Content-Type': 'text/event-stream',
                'Cache-Control': 'no-cache',
                'Connection': 'keep-alive',
                'X-Accel-Buffering': 'no',
            },
        });

    } catch (error) {
        console.error('Handler error:', error);
        return new Response(JSON.stringify({
            error: true,
            message: error.message
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' },
        });
    }
}
