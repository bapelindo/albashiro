<?php
// api/debug_stream.php
// Tujuannya: Cek apakah Vercel mengizinkan Streaming atau menahan (Buffer) output.

// 1. Matikan Buffering
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
while (ob_get_level())
    ob_end_clean();

// 2. Set Header Streaming
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Penting buat Nginx/Vercel

// 3. Padding (Pancingan) 4KB
echo ":" . str_repeat(" ", 4096) . "\n\n";
flush();

// 4. Mulai Streaming Dummy
$startTime = time();

echo "data: " . json_encode(['msg' => 'Mulai Test Streaming', 'time' => date('H:i:s')]) . "\n\n";
flush();

for ($i = 1; $i <= 5; $i++) {
    sleep(1); // Tunggu 1 detik

    echo "data: " . json_encode([
        'step' => $i,
        'elapsed' => (time() - $startTime) . 's',
        'msg' => "Detik ke-$i"
    ]) . "\n\n";

    flush(); // Paksa kirim ke browser
}

echo "data: " . json_encode(['msg' => 'Selesai! Jika ini muncul satu per satu = SUKSES. Jika muncul sekaligus di akhir = BUFFERED/GAGAL.']) . "\n\n";
flush();
?>