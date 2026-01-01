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
    """Import single JSON file to TiDB WITH ROBUST ERROR HANDLING"""
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            data = json.load(f)
            
        if not data:
            print(f"      ⚠️ Empty JSON file")
            return 0
            
        conn = get_db_connection()
        if not conn:
            print(f"      ❌ Database connection failed")
            return -1
            
        cursor = conn.cursor()
        
        query = """
        INSERT INTO knowledge_vectors 
        (source_table, source_id, article_id, content_text, embedding) 
        VALUES (%s, %s, %s, %s, %s)
        """
        
        inserted_count = 0
        batch_size = 50
        batch_data = []
        failed_articles = 0

        for article in data:
            try:
                if 'vectors' not in article:
                    continue
                
                article_id = article.get('id', 0)
                title = article.get('processed_title', '')
                
                # Enumerate chunks starting from 1
                for chunk_num, vec_data in enumerate(article['vectors'], start=1):
                    try:
                        content_chunk = vec_data.get('chunk_text', '')
                        embedding_list = vec_data.get('embedding', [])
                        
                        if not content_chunk or not embedding_list:
                            continue
                        
                        # Format embedding as string '[0.1, 0.2, ...]'
                        embedding_str = '[' + ','.join(map(str, embedding_list)) + ']'
                        
                        batch_data.append((
                            title,                # source_table = TITLE
                            chunk_num,            # source_id = CHUNK NUMBER (1, 2, 3...)
                            article_id,           # article_id = ARTICLE ID
                            content_chunk,        # content_text = CHUNK ONLY (no prefix)
                            embedding_str         # embedding
                        ))
                        
                        if len(batch_data) >= batch_size:
                            try:
                                cursor.executemany(query, batch_data)
                                conn.commit()
                                inserted_count += len(batch_data)
                                batch_data = []
                            except Error as batch_error:
                                print(f"      ⚠️ Batch insert error: {str(batch_error)[:100]}")
                                conn.rollback()
                                batch_data = []
                                
                    except Exception as chunk_error:
                        print(f"      ⚠️ Chunk error (article {article_id}): {str(chunk_error)[:50]}")
                        continue
                        
            except Exception as article_error:
                failed_articles += 1
                print(f"      ⚠️ Article error: {str(article_error)[:50]}")
                continue
        
        # Insert remaining batch
        if batch_data:
            try:
                cursor.executemany(query, batch_data)
                conn.commit()
                inserted_count += len(batch_data)
            except Error as final_error:
                print(f"      ⚠️ Final batch error: {str(final_error)[:100]}")
                conn.rollback()
        
        if failed_articles > 0:
            print(f"      ⚠️ {failed_articles} articles had errors (partial import)")
            
        cursor.close()
        conn.close()
        return inserted_count
        
    except json.JSONDecodeError as json_error:
        print(f"      ❌ Invalid JSON: {str(json_error)[:50]}")
        return -1
    except Exception as e:
        print(f"      ❌ Critical error: {str(e)[:100]}")
        import traceback
        traceback.print_exc()
        return -1

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
