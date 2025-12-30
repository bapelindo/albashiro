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

        // Step 1: Get context from PHP endpoint
        console.log('[DEBUG] Fetching context from PHP...');
        const contextUrl = 'https://' + req.headers.get('host') + '/api/context.php';

        const contextResponse = await fetch(contextUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message, history })
        });

        if (!contextResponse.ok) {
            throw new Error(`Context API error: ${contextResponse.status}`);
        }

        const contextData = await contextResponse.json();
        console.log('[DEBUG] Context received, length:', contextData.metadata?.context_length);

        // Step 2: Build Ollama request with context from PHP
        const ollamaUrl = 'https://ollama.bapel.my.id/api/chat';
        const ollamaPayload = {
            model: 'albashiro',
            messages: contextData.messages, // Use messages from PHP (includes context)
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
                let chunkCount = 0;

                console.log('[DEBUG] Starting stream processing...');

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) {
                        console.log('[DEBUG] Stream ended. Total chunks processed:', chunkCount);
                        break;
                    }

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() || '';

                    for (const line of lines) {
                        if (!line.trim()) continue;

                        chunkCount++;
                        console.log('[DEBUG] Processing chunk', chunkCount, ':', line.substring(0, 100));

                        try {
                            const chunk = JSON.parse(line);

                            console.log('[DEBUG] Parsed chunk:', JSON.stringify(chunk).substring(0, 200));

                            // Extract token from Ollama response
                            if (chunk.message?.content) {
                                const sseData = {
                                    token: chunk.message.content,
                                    done: chunk.done || false
                                };

                                console.log('[DEBUG] Sending token:', sseData.token.substring(0, 50));

                                // Send as SSE event
                                await writer.write(
                                    encoder.encode(`data: ${JSON.stringify(sseData)}\n\n`)
                                );
                            }

                            // Send completion event
                            if (chunk.done) {
                                console.log('[DEBUG] Sending completion event');
                                await writer.write(
                                    encoder.encode(`data: ${JSON.stringify({ done: true })}\n\n`)
                                );
                            }
                        } catch (parseError) {
                            console.error('[ERROR] JSON parse error:', parseError, 'Line:', line.substring(0, 100));
                        }
                    }
                }
            } catch (error) {
                console.error('[ERROR] Stream processing error:', error);
                await writer.write(
                    encoder.encode(`data: ${JSON.stringify({ error: true, message: 'Stream error' })}\n\n`)
                );
            } finally {
                console.log('[DEBUG] Closing writer');
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
