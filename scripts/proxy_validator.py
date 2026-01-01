"""
Proxy Validator & Speed Tester
Automatically fetch proxies from Proxifly, test them, and sort by speed
"""

import requests
import time
import concurrent.futures
from typing import List, Tuple
import os

# Proxifly sources (HTTPS only for security)
PROXY_SOURCES = [
    'https://raw.githubusercontent.com/proxifly/free-proxy-list/main/proxies/all/data.txt',
    'https://raw.githubusercontent.com/proxifly/free-proxy-list/main/proxies/protocols/http/data.txt',
    'https://raw.githubusercontent.com/proxifly/free-proxy-list/main/proxies/protocols/https/data.txt',
]

# Test URL (fast and reliable)
TEST_URL = 'https://www.google.com'
TIMEOUT = 5  # seconds
MAX_WORKERS = 50  # concurrent tests

def fetch_proxies() -> List[str]:
    """Fetch proxies from all sources"""
    print("🌐 Fetching proxies from Proxifly...")
    all_proxies = set()
    
    for source in PROXY_SOURCES:
        try:
            response = requests.get(source, timeout=10)
            if response.status_code == 200:
                proxies = response.text.strip().split('\n')
                for proxy in proxies:
                    proxy = proxy.strip()
                    if proxy and not proxy.startswith('#'):
                        # Add protocol if missing
                        if '://' not in proxy:
                            all_proxies.add(f'http://{proxy}')
                        else:
                            all_proxies.add(proxy)
                print(f"   ✅ {source.split('/')[-2]}: {len(proxies)} proxies")
        except Exception as e:
            print(f"   ⚠️ Failed to fetch from {source}: {e}")
    
    print(f"\n📊 Total unique proxies: {len(all_proxies)}\n")
    return list(all_proxies)

def test_proxy(proxy: str) -> Tuple[str, float]:
    """Test a single proxy and return (proxy, response_time) or None if failed"""
    try:
        proxies = {
            'http': proxy,
            'https': proxy
        }
        
        start_time = time.time()
        response = requests.get(
            TEST_URL,
            proxies=proxies,
            timeout=TIMEOUT,
            headers={'User-Agent': 'Mozilla/5.0'}
        )
        elapsed = time.time() - start_time
        
        if response.status_code == 200:
            return (proxy, elapsed)
        else:
            return None
            
    except Exception:
        return None

def validate_proxies(proxies: List[str]) -> List[Tuple[str, float]]:
    """Validate proxies concurrently and return working ones with speed"""
    print(f"🔍 Testing {len(proxies)} proxies (timeout: {TIMEOUT}s)...\n")
    
    working_proxies = []
    
    with concurrent.futures.ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
        # Submit all tasks
        future_to_proxy = {executor.submit(test_proxy, proxy): proxy for proxy in proxies}
        
        # Process results as they complete
        completed = 0
        for future in concurrent.futures.as_completed(future_to_proxy):
            completed += 1
            result = future.result()
            
            if result:
                proxy, speed = result
                working_proxies.append((proxy, speed))
                print(f"✅ [{completed}/{len(proxies)}] {proxy} - {speed:.2f}s")
            else:
                if completed % 10 == 0:  # Only print every 10th failure to reduce spam
                    print(f"⏳ [{completed}/{len(proxies)}] Testing...")
    
    return working_proxies

def save_proxies(proxies: List[Tuple[str, float]], output_file: str):
    """Save validated proxies sorted by speed"""
    # Sort by speed (fastest first)
    proxies.sort(key=lambda x: x[1])
    
    with open(output_file, 'w') as f:
        for proxy, speed in proxies:
            f.write(f"{proxy}\n")
    
    print(f"\n✅ Saved {len(proxies)} working proxies to: {output_file}")
    print(f"   Fastest: {proxies[0][0]} ({proxies[0][1]:.2f}s)")
    print(f"   Slowest: {proxies[-1][0]} ({proxies[-1][1]:.2f}s)")
    print(f"   Average: {sum(p[1] for p in proxies) / len(proxies):.2f}s")

def main():
    # Get script directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    project_root = os.path.dirname(script_dir)
    output_file = os.path.join(project_root, 'scraped_data', 'valid_proxies.txt')
    
    print("=" * 60)
    print("🚀 PROXY VALIDATOR & SPEED TESTER")
    print("=" * 60)
    print()
    
    # Step 1: Fetch proxies
    proxies = fetch_proxies()
    
    if not proxies:
        print("❌ No proxies found!")
        return
    
    # Step 2: Validate and test speed
    working_proxies = validate_proxies(proxies)
    
    if not working_proxies:
        print("\n❌ No working proxies found!")
        return
    
    # Step 3: Save sorted results
    save_proxies(working_proxies, output_file)
    
    print("\n" + "=" * 60)
    print("🎉 DONE!")
    print("=" * 60)

if __name__ == '__main__':
    main()
