<?php
/**
 * Endpoint AJAX penyedia data grafik bar untuk ApexCharts.
 *
 * Parameter POST dari form FilterGrafikAktivitas:
 * - mode_data: semua, user_pengguna, kategori_aktivitas, deskripsi_aktivitas.
 * - nilai_mode_data: nama user/kategori/deskripsi yang dipilih (kecuali semua).
 * - periode_data: Tahunan atau Bulanan.
 * - tahun: tahun dengan 4 digit; bulan: 01 sampai 12 untuk periode Bulanan.
 *
 * Respons sukses: status, title, categories (label sumbu X), dan series.
 * Tahunan menghasilkan 12 titik; Bulanan mengikuti jumlah hari dalam bulan.
 */

// 1. Muat konfigurasi dan session; buang output include agar JSON tetap valid.
header('Content-Type: application/json; charset=utf-8');
ob_start();
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';
ob_end_clean();

/**
 * Kirim pesan error dan status HTTP agar handler AJAX dapat menanganinya.
 * Proses dihentikan supaya respons error tidak bercampur dengan data grafik.
 */
function grafikError($message, $code = 400)
{
    http_response_code($code);
    // 7. Kirim format categories dan series yang langsung dapat dibaca ApexCharts.
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

// 2. Periksa session login dan izin akses halaman aktivitas.
if (empty($SessionIdAkses)) {
    grafikError('Sesi akses sudah berakhir.', 401);
}
if (IjinAksesSaya($Conn, $SessionIdAkses, 'w4BCAy6VPuZLhkgUQEU') !== 'Ada') {
    grafikError('Anda tidak memiliki izin akses.', 403);
}


// 3. Ambil filter. Default: semua data, periode Tahunan, tahun sekarang.
$mode = $_POST['mode_data'] ?? 'semua';
$periode = $_POST['periode_data'] ?? 'Tahunan';
$tahun = $_POST['tahun'] ?? date('Y');
$bulan = $_POST['bulan'] ?? '';
$nilai = $_POST['nilai_mode_data'] ?? '';
// Whitelist kolom SQL: pengguna tidak dapat mengirim nama kolom sembarang.
$columns = [
    'user_pengguna'      => 'a.nama_akses',
    'kategori_aktivitas' => 'l.kategori_log',
    'deskripsi_aktivitas' => 'l.deskripsi_log',
];
if (!is_string($mode) || ($mode !== 'semua' && !isset($columns[$mode]))) {
    grafikError('Mode data tidak valid.');
}
if (!in_array($periode, ['Tahunan', 'Bulanan'], true)) {
    grafikError('Periode tidak valid.');
}
if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int)$tahun < 1000 || (int)$tahun > 9998) {
    grafikError('Tahun tidak valid.');
}
if ($mode !== 'semua' && (!is_string($nilai) || $nilai === '')) {
    grafikError('Pilih data grafik terlebih dahulu.');
}

// 4. Siapkan label dan pengelompokan default untuk grafik satu tahun.
$namaBulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
$awal = new DateTimeImmutable($tahun . '-01-01');
$akhir = $awal->modify('+1 year');
$categories = $namaBulan;
$bucket = 'MONTH(l.datetime_log)';
$labelPeriode = $tahun;

// Untuk Bulanan, kelompokkan per hari dan tampilkan label 01, 02, dan seterusnya.
if ($periode === 'Bulanan') {
    if (!is_string($bulan) || !preg_match('/^(0[1-9]|1[0-2])$/D', $bulan)) {
        grafikError('Bulan tidak valid.');
    }
    $awal = new DateTimeImmutable($tahun . '-' . $bulan . '-01');
    $akhir = $awal->modify('+1 month');
    // Format t memberi jumlah hari yang tepat, termasuk Februari tahun kabisat.
    $categories = array_map(
        function ($day) {
            return str_pad((string)$day, 2, '0', STR_PAD_LEFT);
        },
        range(1, (int)$awal->format('t'))
    );
    $bucket = 'DAY(l.datetime_log)';
    $labelPeriode = $namaBulan[(int)$bulan - 1] . ' ' . $tahun;
}

// 5. Hitung log pada rentang waktu dan mode data yang dipilih.
try {
    // Batas akhir eksklusif: awal periode termasuk, awal periode berikutnya tidak.
    $where = 'l.datetime_log >= ? AND l.datetime_log < ?';
    $values = [$awal->format('Y-m-d H:i:s'), $akhir->format('Y-m-d H:i:s')];
    if ($mode !== 'semua') {
        $where .= ' AND ' . $columns[$mode] . ' = ?';
        $values[] = $nilai;
    }
    $stmt = $Conn->prepare("SELECT $bucket AS posisi, COUNT(*) AS jumlah FROM log AS l
        LEFT JOIN akses AS a ON a.id_akses = l.id_akses WHERE $where GROUP BY $bucket ORDER BY posisi ASC");
    // Semua nilai filter dikirim sebagai parameter string, bukan disisipkan ke SQL.
    $stmt->bind_param(str_repeat('s', count($values)), ...$values);
    $stmt->execute();
    $result = $stmt->get_result();
    // 6. Isi periode kosong dengan nol agar seluruh bulan/tanggal tetap terlihat.
    $data = array_fill(0, count($categories), 0);
    while ($item = $result->fetch_assoc()) {
        // MONTH/DAY dimulai dari 1; indeks array PHP dimulai dari 0.
        $data[(int)$item['posisi'] - 1] = (int)$item['jumlah'];
    }
    $stmt->close();
    // 7. Kirim format categories dan series yang langsung dapat dibaca ApexCharts.
    echo json_encode([
        'status' => 'success',
        'title' => 'Log Aktivitas - ' . $labelPeriode . ($mode === 'semua' ? ' - Semua' : ' - ' . $nilai),
        'categories' => $categories,
        'series' => [
            [
                'name' => 'Jumlah Log',
                'data' => $data,
            ],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    // Detail teknis dicatat di server; pengguna menerima pesan yang mudah dipahami.
    error_log('Grafik aktivitas: ' . $exception->getMessage());
    grafikError('Gagal memuat data grafik. Silakan coba kembali.', 500);
}
