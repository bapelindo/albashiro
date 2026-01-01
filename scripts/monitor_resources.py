"""
System Resource Monitor - Real-time CPU/GPU/Memory Tracking
Logs to file for leak detection
"""
import psutil
import time
import datetime
import os

# Configuration
LOG_FILE = r'c:\apache\htdocs\albashiro\scraped_data\monitor\system_monitor.log'
INTERVAL = 5  # seconds between checks

# Ensure log directory exists
os.makedirs(os.path.dirname(LOG_FILE), exist_ok=True)

print("=" * 70)
print("SYSTEM RESOURCE MONITOR")
print("=" * 70)
print(f"Logging to: {LOG_FILE}")
print(f"Update interval: {INTERVAL}s")
print("Press Ctrl+C to stop")
print("=" * 70)
print()

# Header
with open(LOG_FILE, 'w', encoding='utf-8') as f:
    f.write("Timestamp,CPU%,RAM_Used_GB,RAM_Total_GB,RAM%,GPU_Usage%,GPU_Mem_Used_GB,GPU_Mem_Total_GB\n")

try:
    while True:
        timestamp = datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        
        # CPU Usage
        cpu_percent = psutil.cpu_percent(interval=1)
        
        # RAM Usage
        ram = psutil.virtual_memory()
        ram_used_gb = ram.used / (1024**3)
        ram_total_gb = ram.total / (1024**3)
        ram_percent = ram.percent
        
        # GPU Usage (AMD Radeon via DXGI)
        try:
            # Try to get GPU info from Windows Performance Counters
            import subprocess
            result = subprocess.run(
                ['powershell', '-Command', 
                 'Get-Counter "\\GPU Engine(*engtype_3D)\\Utilization Percentage" | Select-Object -ExpandProperty CounterSamples | Measure-Object -Property CookedValue -Sum | Select-Object -ExpandProperty Sum'],
                capture_output=True,
                text=True,
                timeout=2
            )
            gpu_usage = float(result.stdout.strip()) if result.stdout.strip() else 0.0
        except:
            gpu_usage = 0.0
        
        # GPU Memory (from earlier detection)
        gpu_mem_used_gb = 0.5  # Placeholder - AMD iGPU shares system RAM
        gpu_mem_total_gb = 4.1
        
        # Console output
        print(f"[{timestamp}]")
        print(f"  CPU: {cpu_percent:5.1f}%")
        print(f"  RAM: {ram_used_gb:5.2f}/{ram_total_gb:.2f} GB ({ram_percent:5.1f}%)")
        print(f"  GPU: {gpu_usage:5.1f}% | VRAM: {gpu_mem_used_gb:.2f}/{gpu_mem_total_gb:.2f} GB")
        
        # Check for potential leaks
        if ram_percent > 80:
            print(f"  ⚠️  WARNING: High RAM usage!")
        if cpu_percent > 90:
            print(f"  ⚠️  WARNING: High CPU usage!")
        
        print()
        
        # Log to file
        with open(LOG_FILE, 'a', encoding='utf-8') as f:
            f.write(f"{timestamp},{cpu_percent:.1f},{ram_used_gb:.2f},{ram_total_gb:.2f},{ram_percent:.1f},{gpu_usage:.1f},{gpu_mem_used_gb:.2f},{gpu_mem_total_gb:.2f}\n")
        
        time.sleep(INTERVAL)
        
except KeyboardInterrupt:
    print("\n" + "=" * 70)
    print("Monitor stopped by user")
    print(f"Log saved to: {LOG_FILE}")
    print("=" * 70)
