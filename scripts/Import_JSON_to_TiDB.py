import os
import json
import time
import glob
import mysql.connector
from mysql.connector import Error
from tqdm import tqdm

# ========== KONFIGURASI ==========

# TiDB Configuration (Sesuai Albashiro_Crawler_PERFECT.py)
TIDB_CONFIG = {
    'host': 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com',
    'port': 4000,
    'user': '4TnpUUxik5ZLHTT.root', 
    'password': 'xYwYMe4gp4c7IkgI', # ⚠️ PASTIKAN MANUAL DIISI SEBELUM RUN ATAU LOAD DARI ENV
    'database': 'albashiro',
    'ssl_verify_cert': True,
    'ssl_ca': r'C:\apache\htdocs\albashiro\isrgrootx1.pem',
    'connect_timeout': 30
}

# Direktori Backup
BACKUP_DIR = r'C:\apache\htdocs\albashiro\scraped_data\backup'

# ================================

def get_db_connection():
    """Connect to TiDB Cloud"""
    try:
        conn = mysql.connector.connect(**TIDB_CONFIG)
        if conn.is_connected():
            return conn
    except Error as e:
        print(f"❌ Connection failed: {e}")
        return None

def import_json_file(file_path):
    """Import single JSON file to TiDB"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            
        if not data:
            return 0
            
        conn = get_db_connection()
        if not conn:
            return 0
            
        cursor = conn.cursor()
        
        # Prepare queries
        # Skema harus sama persis dengan Albashiro_Crawler_PERFECT.py
        # (source_table, source_id, content_text, embedding)
        
        query = """
        INSERT INTO knowledge_vectors 
        (source_table, source_id, content_text, embedding) 
        VALUES (%s, %s, %s, %s)
        """
        
        inserted_count = 0
        batch_size = 50
        batch_data = []

        for article in data:
            if 'vectors' not in article:
                continue
            
            article_id = article.get('id', 0)
            title = article.get('processed_title', '')
            
            for vec_data in article['vectors']:
                # vec_data structure: {'chunk_text': ..., 'embedding': [...]}
                
                content_chunk = vec_data.get('chunk_text', '')
                embedding_list = vec_data.get('embedding', [])
                
                # Format embedding as string '[0.1, 0.2, ...]'
                embedding_str = '[' + ','.join(map(str, embedding_list)) + ']'
                
                # Format content text: "Judul: ...\n\nIsi..."
                content_to_store = f"Judul: {title}\n\n{content_chunk}"
                
                batch_data.append((
                    'ai_crawler_colab',   # source_table (hardcoded match crawler)
                    article_id,           # source_id
                    content_to_store,     # content_text
                    embedding_str         # embedding
                ))
                
                if len(batch_data) >= batch_size:
                    cursor.executemany(query, batch_data)
                    conn.commit()
                    inserted_count += len(batch_data)
                    batch_data = []
        
        # Insert sisa
        if batch_data:
            cursor.executemany(query, batch_data)
            conn.commit()
            inserted_count += len(batch_data)
            
        cursor.close()
        conn.close()
        return inserted_count
        
    except Exception as e:
        print(f"⚠️ Error processing {os.path.basename(file_path)}: {e}")
        return 0

import shutil
from datetime import datetime

# ... (Previous Config) ...
BACKUP_DIR = r'C:\apache\htdocs\albashiro\scraped_data\backup'
STREAM_DIR = r'C:\apache\htdocs\albashiro\scraped_data\backup\stream'

# ... (Previous Functions) ...

def main():
    print("="*60)
    print("🚚 ALBASHIRO DATA IMPORTER (JSON -> TiDB)")
    print("   Mode: STREAM CONSUMER (Dual-Write Support)")
    print("="*60)
    
    # Ensure stream dir exists
    os.makedirs(STREAM_DIR, exist_ok=True)
    
    # Check Password
    if TIDB_CONFIG['password'] == 'password_tidb_anda':
        import getpass
        pwd = getpass.getpass("🔑 Enter TiDB Password: ")
        TIDB_CONFIG['password'] = pwd
    
    print(f"👀 Watching Stream: {STREAM_DIR}")
    print(f"🗑️  Files will be DELETED after upload (Archives are safe in ../backup)")
    print("   Press Ctrl+C to stop.\n")
    
    try:
        while True:
            # Find JSON files in STREAM dir
            json_pattern = os.path.join(STREAM_DIR, '*.json')
            files = glob.glob(json_pattern)
            
            # Sort by modification time (oldest first)
            files.sort(key=os.path.getmtime)
            
            files_processed_in_batch = 0
            
            for file_path in files:
                filename = os.path.basename(file_path)
                
                # Check file lock/write status (naive check: can open for read?)
                try:
                    with open(file_path, 'r', encoding='utf-8') as f:
                        pass
                except IOError:
                    continue # File is still being written by crawler
                
                print(f"   ⚡ Processing Stream: {filename}")
                
                vectors = import_json_file(file_path)
                
                if vectors >= 0: 
                    # DELETE file after success (Consumer Pattern)
                    try:
                        os.remove(file_path)
                        print(f"      🗑️  Consumed (Deleted): {filename}")
                        files_processed_in_batch += 1
                    except Exception as e:
                        print(f"      ⚠️ Failed deleting file: {e}")
                else:
                    # If failed (e.g. timeout), keep file to retry layer
                    print(f"      ⚠️ Import failed, keeping for retry.")
            
            if files_processed_in_batch == 0:
                time.sleep(2) # Faster poll for stream
                # print(".", end='', flush=True) 
            else:
                print(f"   ✅ Batch complete.")
                
    except KeyboardInterrupt:
        print("\n⛔ Watchdog stopped by user.")

if __name__ == "__main__":
    main()
