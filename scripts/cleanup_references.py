"""
TiDB Reference Chunk Cleanup Script
Removes reference-only chunks from knowledge_vectors table
"""

import mysql.connector
import re
from typing import List

# TiDB Connection Config
TIDB_CONFIG = {
    'host': 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com',
    'port': 4000,
    'user': 'root',
    'password': 'Lintang2580',
    'database': 'albashiro',
    'ssl_ca': 'C:/apache/htdocs/albashiro/isrgrootx1.pem'
}

def is_reference_chunk(content: str) -> bool:
    """
    Detect if content is primarily bibliographic references
    Uses same logic as Go scraper
    """
    content_lower = content.lower()
    reference_score = 0
    
    # 1. Check for DOI/URL patterns
    doi_count = content.count('doi.org/') + content.count('https://doi')
    url_count = content.count('http://') + content.count('https://')
    if doi_count > 2 or url_count > 3:
        reference_score += 3
    
    # 2. Check for year patterns in citations
    year_pattern = re.compile(r'\(?\b(19|20)\d{2}\b\)?')
    year_matches = year_pattern.findall(content)
    if len(year_matches) > 5:
        reference_score += 2
    
    # 3. Check for author citation patterns
    author_pattern = re.compile(r'[A-Z][a-z]+,\s+[A-Z]\.')
    author_matches = author_pattern.findall(content)
    if len(author_matches) > 3:
        reference_score += 2
    
    # 4. Check for reference section keywords
    ref_keywords = ['references', 'bibliography', 'works cited', 'daftar pustaka']
    for keyword in ref_keywords:
        if keyword in content_lower:
            reference_score += 1
            break
    
    # 5. Check for journal/publisher patterns
    journal_keywords = ['journal of', 'proceedings of', 'international journal', 'vol.', 'pp.']
    journal_count = sum(1 for keyword in journal_keywords if keyword in content_lower)
    if journal_count > 2:
        reference_score += 2
    
    return reference_score >= 5

def cleanup_reference_chunks():
    """Remove reference-only chunks from TiDB"""
    
    print("🔗 Connecting to TiDB...")
    conn = mysql.connector.connect(**TIDB_CONFIG)
    cursor = conn.cursor()
    
    # Get all chunks
    print("📊 Fetching all chunks from knowledge_vectors...")
    cursor.execute("SELECT id, content_text FROM knowledge_vectors")
    chunks = cursor.fetchall()
    
    print(f"   Found {len(chunks)} total chunks")
    
    # Identify reference chunks
    reference_ids = []
    for chunk_id, content_text in chunks:
        if is_reference_chunk(content_text):
            reference_ids.append(chunk_id)
    
    print(f"   Identified {len(reference_ids)} reference chunks")
    
    if len(reference_ids) == 0:
        print("✅ No reference chunks found - database is clean!")
        cursor.close()
        conn.close()
        return
    
    # Delete reference chunks
    print(f"🗑️  Deleting {len(reference_ids)} reference chunks...")
    
    # Delete in batches of 100
    batch_size = 100
    deleted_count = 0
    
    for i in range(0, len(reference_ids), batch_size):
        batch = reference_ids[i:i+batch_size]
        placeholders = ','.join(['%s'] * len(batch))
        delete_query = f"DELETE FROM knowledge_vectors WHERE id IN ({placeholders})"
        cursor.execute(delete_query, batch)
        conn.commit()
        deleted_count += len(batch)
        print(f"   Deleted {deleted_count}/{len(reference_ids)} chunks...")
    
    print(f"✅ Cleanup complete!")
    print(f"   Total deleted: {deleted_count} reference chunks")
    print(f"   Remaining: {len(chunks) - deleted_count} content chunks")
    
    cursor.close()
    conn.close()

if __name__ == "__main__":
    try:
        cleanup_reference_chunks()
    except Exception as e:
        print(f"❌ Error: {e}")
        import traceback
        traceback.print_exc()
