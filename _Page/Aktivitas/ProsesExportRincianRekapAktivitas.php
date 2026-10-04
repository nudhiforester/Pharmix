<?php
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Cegah output dari konfigurasi tercampur dengan file Excel.
ob_start();
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';
ob_end_clean();

if (empty($SessionIdAkses)) {
    http_response_code(401);
    exit('Sesi akses sudah berakhir. Silakan login ulang.');
}
if (IjinAksesSaya($Conn, $SessionIdAkses, 'w4BCAy6VPuZLhkgUQEU') !== 'Ada') {
    http_response_code(403);
    exit('Anda tidak memiliki izin untuk export log aktivitas.');
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Metode request tidak valid.');
}

// Filter item harus sama dengan yang dikirim saat membuka modal rincian.
$tipe = $_POST['tipe'] ?? '';
$item = $_POST['item_value'] ?? null;
$columns = [
    'user' => 'l.id_akses',
    'kategori' => 'l.kategori_log',
    'deskripsi' => 'l.deskripsi_log',
];
if (!is_string($tipe) || !isset($columns[$tipe]) || !is_string($item)) {
    http_response_code(400);
    exit('Item rincian rekap tidak valid.');
}
if ($tipe === 'user' && !preg_match('/^[0-9]+$/D', $item)) {
    http_response_code(400);
    exit('ID user tidak valid.');
}

$periode = $_POST['periode_data'] ?? '';
$tahun = $_POST['tahun'] ?? '';
$bulan = $_POST['bulan'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$awal = null;
$akhir = null;
$label = 'Semua';

if (!in_array($periode, ['Semua', 'Tahunan', 'Bulanan', 'Harian'], true)) {
    http_response_code(400);
    exit('Periode data tidak valid.');
}
if ($periode === 'Tahunan' || $periode === 'Bulanan') {
    if (!is_string($tahun) || !preg_match('/^[0-9]{4}$/D', $tahun) || (int)$tahun < 1000 || (int)$tahun > 9998) {
        http_response_code(400);
        exit('Tahun harus berupa 4 digit angka antara 1000 dan 9998.');
    }
    $label = $tahun;
    $awal = new DateTimeImmutable($tahun . '-01-01');
    $akhir = $awal->modify('+1 year');
    if ($periode === 'Bulanan') {
        if (!is_string($bulan) || !preg_match('/^(0[1-9]|1[0-2])$/D', $bulan)) {
            http_response_code(400);
            exit('Bulan harus berupa angka 01 sampai 12.');
        }
        $label = $tahun . '-' . $bulan;
        $awal = new DateTimeImmutable($label . '-01');
        $akhir = $awal->modify('+1 month');
    }
} elseif ($periode === 'Harian') {
    $awal = is_string($tanggal) ? DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal) : false;
    if (!$awal || $awal->format('Y-m-d') !== $tanggal || $tanggal < '1000-01-01' || $tanggal > '9998-12-31') {
        http_response_code(400);
        exit('Tanggal tidak valid. Gunakan format YYYY-MM-DD.');
    }
    $label = $tanggal;
    $akhir = $awal->modify('+1 day');
}

require __DIR__ . '/../../vendor/autoload.php';

try {
    // Kesetaraan item dan rentang periode mengikuti query rincian modal.
    $where = 'WHERE ' . $columns[$tipe] . ' = ?';
    $parameters = [$item];
    if ($awal) {
        $where .= ' AND l.datetime_log >= ? AND l.datetime_log < ?';
        $parameters[] = $awal->format('Y-m-d H:i:s');
        $parameters[] = $akhir->format('Y-m-d H:i:s');
    }
    $stmt = $Conn->prepare(
        'SELECT l.id_akses, a.nama_akses, l.kategori_log, l.deskripsi_log, l.datetime_log
         FROM log AS l LEFT JOIN akses AS a ON a.id_akses = l.id_akses '
         . $where . ' ORDER BY l.datetime_log DESC, l.id_log DESC'
    );
    if (!$stmt) {
        throw new RuntimeException('Query export gagal disiapkan.');
    }
    $stmt->bind_param(str_repeat('s', count($parameters)), ...$parameters);
    if (!$stmt->execute() || !($query = $stmt->get_result())) {
        throw new RuntimeException('Query export gagal dijalankan.');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Log Aktivitas');
    $sheet->fromArray(['No', 'Nama User', 'Kategori', 'Deskripsi', 'Tgl/Jam'], null, 'A1');
    $row = 2;
    while ($data = $query->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $row - 1);
        $values = [
            $data['nama_akses'] ?? ('User #' . $data['id_akses']),
            $data['kategori_log'], $data['deskripsi_log'], $data['datetime_log']
        ];
        $column = 'B';
        foreach ($values as $value) {
            // Simpan teks secara eksplisit agar isi log tidak menjadi rumus Excel.
            $sheet->setCellValueExplicit($column++ . $row, (string)$value, DataType::TYPE_STRING);
        }
        $row++;
    }
    $query->free();
    $stmt->close();

    $lastRow = max(1, $row - 1);
    $sheet->getStyle('A1:E1')->getFont()->setBold(true);
    $sheet->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE9ECEF');
    $sheet->getStyle('A1:E' . $lastRow)->getAlignment()
        ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
    $sheet->freezePane('A2');
    $sheet->setAutoFilter('A1:E' . $lastRow);
    foreach (['A' => 8, 'B' => 30, 'C' => 25, 'D' => 70, 'E' => 23] as $column => $width) {
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    // Siapkan file terlebih dahulu sebelum mengirim header download.
    $file = tmpfile();
    if (!$file) {
        throw new RuntimeException('File sementara tidak dapat dibuat.');
    }
    $writer = new Xlsx($spreadsheet);
    $writer->save(stream_get_meta_data($file)['uri']);
    $spreadsheet->disconnectWorksheets();
} catch (Throwable $exception) {
    error_log('Export log aktivitas: ' . $exception->getMessage());
    http_response_code(500);
    exit('Gagal membuat file Excel. Silakan coba kembali.');
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="Rincian_Rekap_' . $tipe . '_' . $label . '_' . date('Ymd_His') . '.xlsx"');
header('Cache-Control: no-store, max-age=0');
rewind($file);
fpassthru($file);
fclose($file);
exit;
