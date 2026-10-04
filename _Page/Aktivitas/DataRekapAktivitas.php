<?php
/**
 * Endpoint AJAX untuk daftar rekap dan tabel rincian log aktivitas.
 *
 * Parameter POST:
 * - tipe: user, kategori, atau deskripsi.
 * - periode_data: Semua, Tahunan, Bulanan, atau Harian.
 * - tahun/bulan/tanggal: mengikuti periode yang dipilih.
 * - page, limit, keyword: filter daftar pada masing-masing card.
 * - mode=rincian dan item_value: mengambil log dari item yang diklik.
 *
 * Respons JSON berisi status, HTML, dan informasi jumlah data.
 * Daftar memiliki metadata paginasi; rincian menampilkan seluruh log item.
 */

// 1. Muat koneksi dan session tanpa mencampurkan output ke respons JSON.
header('Content-Type: application/json; charset=utf-8');
ob_start();
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';
ob_end_clean();

/**
 * Kirim pesan kegagalan dengan status HTTP, lalu hentikan proses.
 * Pesan dibaca oleh handler error AJAX di Aktivitas.js.
 */
function rekapError($message, $code = 400)
{
    http_response_code($code);
    echo json_encode(['status' => 'error', 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}
// 2. Pastikan pengguna masih login dan memiliki izin halaman aktivitas.
if (empty($SessionIdAkses)) {
    rekapError('Sesi akses sudah berakhir.', 401);
}
if (IjinAksesSaya($Conn, $SessionIdAkses, 'w4BCAy6VPuZLhkgUQEU') !== 'Ada') {
    rekapError('Anda tidak memiliki izin akses.', 403);
}

// 3. Tentukan kolom pengelompokan dari whitelist, bukan input SQL langsung.
$tipe = $_POST['tipe'] ?? '';
$columns = [
    'user'      => 'l.id_akses',
    'kategori'  => 'l.kategori_log',
    'deskripsi' => 'l.deskripsi_log',
];
if (!is_string($tipe) || !isset($columns[$tipe])) {
    rekapError('Jenis rekap tidak valid.');
}

// 4. Validasi periode dan tentukan batas waktu awal serta akhir.
// Batas akhir bersifat eksklusif: datetime_log >= awal dan < akhir.
$periode = $_POST['periode_data'] ?? 'Semua';
$tahun = $_POST['tahun'] ?? '';
$bulan = $_POST['bulan'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$awal = null;
if (!in_array($periode, ['Semua', 'Tahunan', 'Bulanan', 'Harian'], true)) {
    rekapError('Periode tidak valid.');
}
if (
// 4. Validasi periode dan tentukan batas waktu awal serta akhir.
// Batas akhir bersifat eksklusif: datetime_log >= awal dan < akhir.
$periode === 'Tahunan' || 
// 4. Validasi periode dan tentukan batas waktu awal serta akhir.
// Batas akhir bersifat eksklusif: datetime_log >= awal dan < akhir.
$periode === 'Bulanan') {
    if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int)$tahun < 1000 || (int)$tahun > 9998) {
        rekapError('Tahun tidak valid.');
    }
    $awal = new DateTimeImmutable($tahun . '-01-01');
    $akhir = $awal->modify('+1 year');
    if (
// 4. Validasi periode dan tentukan batas waktu awal serta akhir.
// Batas akhir bersifat eksklusif: datetime_log >= awal dan < akhir.
$periode === 'Bulanan') {
        if (!is_string($bulan) || !preg_match('/^(0[1-9]|1[0-2])$/D', $bulan)) {
            rekapError('Bulan tidak valid.');
        }
        $awal = new DateTimeImmutable($tahun . '-' . $bulan . '-01');
        $akhir = $awal->modify('+1 month');
    }
} elseif (
// 4. Validasi periode dan tentukan batas waktu awal serta akhir.
// Batas akhir bersifat eksklusif: datetime_log >= awal dan < akhir.
$periode === 'Harian') {
    $awal = is_string($tanggal) ? DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal) : false;
    if (!$awal || $awal->format('Y-m-d') !== $tanggal || $tanggal < '1000-01-01' || $tanggal > '9998-12-31') {
        rekapError('Tanggal tidak valid.');
    }
    $akhir = $awal->modify('+1 day');
}

// 5. Validasi pencarian dan paginasi; limit maksimal 100 item per card.
$page = max(1, (int)filter_var($_POST['page'] ?? 1, FILTER_VALIDATE_INT));
$limit = filter_var($_POST['limit'] ?? 10, FILTER_VALIDATE_INT);
$limit = $limit && $limit > 0 ? min(100, $limit) : 10;
$keyword = $_POST['keyword'] ?? '';
if (!is_string($keyword)) {
    rekapError('Pencarian tidak valid.');
}

try {
    // 6. Susun kondisi WHERE dan nilai binding prepared statement.
    $conditions = [];
    $values = [];
    if ($awal) {
        $conditions[] = 'l.datetime_log >= ? AND l.datetime_log < ?';
        $values[] = $awal->format('Y-m-d H:i:s');
        $values[] = $akhir->format('Y-m-d H:i:s');
    }
    // LEFT JOIN menjaga log akun yang dihapus; COALESCE memberikan nama User #ID.
    $label = $tipe === 'user' ? "COALESCE(a.nama_akses, CONCAT('User #', l.id_akses))" : $columns[$tipe];

    // 7. Mode rincian: cocokkan item secara tepat, lalu buat baris tabel modal.
    if (($_POST['mode'] ?? '') === 'rincian') {
        $item = $_POST['item_value'] ?? null;
        if (!is_string($item)) {
            rekapError('Item rekap tidak valid.');
        }
        if ($tipe === 'user' && !preg_match('/^[0-9]+$/D', $item)) {
            rekapError('ID user tidak valid.');
        }
        // Kesetaraan (=) memastikan rincian sesuai item, bukan pencarian parsial.
        $conditions[] = $columns[$tipe] . ' = ?';
        $values[] = $item;
        $where = ' WHERE ' . implode(' AND ', $conditions);
        $stmt = $Conn->prepare(
            "SELECT $label AS item_label, COALESCE(a.nama_akses, CONCAT('User #', l.id_akses)) AS nama_akses,
                    l.kategori_log, l.deskripsi_log, l.datetime_log
             FROM log AS l LEFT JOIN akses AS a ON a.id_akses = l.id_akses
             $where ORDER BY l.datetime_log DESC, l.id_log DESC"
        );
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
        $stmt->execute();
        $result = $stmt->get_result();
        $html = '';
        $no = 1;
        while ($data = $result->fetch_assoc()) {
            $html .= '<tr><td><small class="text-muted">' . $no++ . '</small></td>';
            // Escape isi log sebelum disisipkan ke HTML untuk mencegah injeksi markup.
            foreach (['nama_akses', 'kategori_log', 'deskripsi_log', 'datetime_log'] as $field) {
                $html .= '<td><small class="text-muted">'
                    . htmlspecialchars($data[$field], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</small></td>';
            }
            $html .= '</tr>';
        }
        $total = $no - 1;
        if (!$total) {
            $html = '<tr><td colspan="5" class="text-center text-muted">Tidak ada data yang ditampilkan.</td></tr>';
        }
        $stmt->close();
        echo json_encode(['status' => 'success', 'html' => $html, 'total_data' => $total], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        exit;
    }

    // 8. Mode daftar: pencarian parsial pada nama user, kategori, atau deskripsi.
    if (trim($keyword) !== '') {
        $conditions[] = "$label LIKE ?";
        $values[] = '%' . trim($keyword) . '%';
    }
    $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';
    // User dikelompokkan berdasarkan ID agar nama yang sama tidak digabung.
    $group = $columns[$tipe] . ($tipe === 'user' ? ', a.nama_akses' : '');
    $base = "SELECT {$columns[$tipe]} AS item_value, $label AS label, COUNT(*) AS total_log
             FROM log AS l LEFT JOIN akses AS a ON a.id_akses = l.id_akses
             $where GROUP BY $group";

    // Hitung jumlah kelompok untuk paginasi, bukan jumlah seluruh baris log.
    $stmt = $Conn->prepare("SELECT COUNT(*) AS total FROM ($base) AS grouped_log");
    if ($values) {
        $stmt->bind_param(str_repeat('s', count($values)), ...$values);
    }
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // 9. Batasi halaman agar tetap valid, termasuk ketika hasil pencarian kosong.
    $totalPage = max(1, (int)ceil($total / $limit));
    $page = min($page, $totalPage);
    $offset = ($page - 1) * $limit;

    // Tampilkan kelompok dengan log terbanyak; urutan tambahan menjaga paginasi stabil.
    $stmt = $Conn->prepare($base . ' ORDER BY total_log DESC, label ASC, item_value ASC LIMIT ?, ?');
    $types = str_repeat('s', count($values)) . 'ii';
    $values[] = $offset;
    $values[] = $limit;
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $result = $stmt->get_result();

    // 10. Buat list group clickable dengan parameter tipe dan nilai item untuk modal.
    $html = '<div class="list-group">';
    while ($data = $result->fetch_assoc()) {
        $text = htmlspecialchars($data['label'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $value = htmlspecialchars($data['item_value'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html .= '<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start rekap-aktivitas-item" data-bs-toggle="modal" data-bs-target="#ModalRincianRekapAktivitas" data-tipe="' . $tipe . '" data-value="' . $value . '">'
            . '<span class="text-muted text-break text-start me-2">' . $text . '</span><span class="badge bg-primary rounded-pill">'
            . (int)$data['total_log'] . '</span></button>';
    }
    $html .= '</div>';
    if (!$total) {
        $html = '<div class="alert alert-warning text-center"><small>Tidak ada data yang ditampilkan.</small></div>';
    }
    $stmt->close();
    // 11. Kirim HTML beserta metadata untuk memperbarui tombol paginasi jQuery.
    echo json_encode([
        'status'     => 'success',
        'html'       => $html,
        'page'       => $page,
        'total_page' => $totalPage,
        'total_data' => $total,
    ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
} catch (Throwable $exception) {
    // Simpan detail teknis di log server; pengguna menerima pesan yang mudah dipahami.
    error_log('Rekap aktivitas: ' . $exception->getMessage());
    rekapError('Gagal memuat data rekap. Silakan coba kembali.', 500);
}
