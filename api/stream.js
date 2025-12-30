// Vercel Serverless Function - Node.js Streaming Proxy for Ollama
// This bypasses PHP buffering issues by using Node.js native streaming

export const config = {
    runtime: 'edge', // Use Edge Runtime for better streaming support
};

export default async function handler(req) {
    // Only allow POST requests
    if (req.method !== 'POST') {
        return new Response(JSON.stringify({ error: 'Method not allowed' }), {
            status: 405,
            headers: { 'Content-Type': 'application/json' },
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

        // Build Ollama request payload
        const ollamaUrl = 'https://ollama.bapel.my.id/api/chat';
        const ollamaPayload = {
            model: 'albashiro',
            messages: [
                ...history.map(h => ({
                    role: h.role === 'ai' ? 'assistant' : 'user',
                    content: h.message
                })),
                { role: 'user', content: message }
            ],
            stream: true
        };

        // Forward request to Ollama
        console.log('[DEBUG] Ollama URL:', ollamaUrl);
        console.log('[DEBUG] Ollama Payload:', JSON.stringify(ollamaPayload, null, 2));

        const ollamaResponse = await fetch(ollamaUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(ollamaPayload),
        });

        console.log('[DEBUG] Ollama Response Status:', ollamaResponse.status);
        console.log('[DEBUG] Ollama Response Headers:', Object.fromEntries(ollamaResponse.headers.entries()));

        if (!ollamaResponse.ok) {
            const errorText = await ollamaResponse.text();
            console.error('[ERROR] Ollama API error:', ollamaResponse.status, errorText);
            throw new Error(`Ollama API error: ${ollamaResponse.status} - ${errorText}`);
        }

        // Create a TransformStream to convert Ollama's JSON stream to SSE
        const { readable, writable } = new TransformStream();
        const writer = writable.getWriter();
        const encoder = new TextEncoder();

        // Process Ollama stream in background
        (async () => {
            try {
                const reader = ollamaResponse.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';

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

                            // Extract token from Ollama response
                            if (chunk.message?.content) {
                                const sseData = {
                                    token: chunk.message.content,
                                    done: chunk.done || false
                                };

                                // Send as SSE event
                                await writer.write(
                                    encoder.encode(`data: ${JSON.stringify(sseData)}\n\n`)
                                );
                            }

                            // Send completion event
                            if (chunk.done) {
                                await writer.write(
                                    encoder.encode(`data: ${JSON.stringify({ done: true })}\n\n`)
                                );
                            }
                        } catch (parseError) {
                            console.error('JSON parse error:', parseError);
                        }
                    }
                }
            } catch (error) {
                console.error('Stream processing error:', error);
                await writer.write(
                    encoder.encode(`data: ${JSON.stringify({ error: true, message: 'Stream error' })}\n\n`)
                );
            } finally {
                await writer.close();
            }
        })();

        // Return SSE response
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
        return new Response(
            JSON.stringify({ error: true, message: error.message }),
            {
                status: 500,
                headers: { 'Content-Type': 'application/json' },
            }
        );
    }
}
