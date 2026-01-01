"""
Test Ollama Embedding - Debug Script
"""
import requests
import time

OLLAMA_URL = 'http://localhost:11434'
OLLAMA_EMBEDDING_MODEL = 'all-minilm'

print("=" * 60)
print("TESTING OLLAMA EMBEDDING")
print("=" * 60)

# Test text
test_text = "This is a test sentence for embedding generation."

print(f"\n1. Testing connection to {OLLAMA_URL}...")
try:
    response = requests.get(f'{OLLAMA_URL}/api/tags', timeout=5)
    print(f"   ✅ Ollama is running (status: {response.status_code})")
except Exception as e:
    print(f"   ❌ Ollama not responding: {e}")
    exit(1)

print(f"\n2. Requesting embedding for: '{test_text[:50]}...'")
print(f"   Model: {OLLAMA_EMBEDDING_MODEL}")
print(f"   Timeout: 15 seconds")

start_time = time.time()

try:
    response = requests.post(
        f'{OLLAMA_URL}/api/embeddings',
        json={
            'model': OLLAMA_EMBEDDING_MODEL,
            'prompt': test_text
        },
        timeout=15
    )
    
    elapsed = time.time() - start_time
    
    print(f"\n3. Response received in {elapsed:.2f}s")
    print(f"   Status code: {response.status_code}")
    
    if response.status_code == 200:
        data = response.json()
        embedding = data.get('embedding', [])
        print(f"   ✅ SUCCESS!")
        print(f"   Embedding dimensions: {len(embedding)}")
        print(f"   First 5 values: {embedding[:5]}")
    else:
        print(f"   ❌ ERROR: {response.text}")
        
except requests.exceptions.Timeout:
    print(f"\n   ❌ TIMEOUT after 15 seconds!")
    print(f"   Ollama is not responding to embedding requests")
except Exception as e:
    print(f"\n   ❌ EXCEPTION: {e}")

print("\n" + "=" * 60)
print("TEST COMPLETE")
print("=" * 60)
