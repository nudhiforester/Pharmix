<?php
// Menghasilkan select lanjutan untuk mode data grafik.
header('Content-Type: text/html; charset=utf-8');
require __DIR__ . '/../../_Config/Connection.php';
require __DIR__ . '/../../_Config/GlobalFunction.php';
require __DIR__ . '/../../_Config/Session.php';

if (empty($SessionIdAkses)) {
    http_response_code(401);
    exit('Sesi akses sudah berakhir. Silakan login ulang.');
}
if (IjinAksesSaya($Conn, $SessionIdAkses, 'w4BCAy6VPuZLhkgUQEU') !== 'Ada') {
    http_response_code(403);
    exit('Anda tidak memiliki izin akses.');
}

$mode = $_POST['mode_data'] ?? 'semua';
if ($mode === 'semua') {
    exit;
}

// Query dipilih dari daftar tetap agar nama tabel/kolom tidak berasal dari input.
$options = [
    'user_pengguna' => ['Nama User', 'SELECT DISTINCT nama_akses AS nilai FROM akses ORDER BY nama_akses ASC'],
    'kategori_aktivitas' => ['Kategori Aktivitas', 'SELECT DISTINCT kategori_log AS nilai FROM log ORDER BY kategori_log ASC'],
    'deskripsi_aktivitas' => ['Deskripsi Aktivitas', 'SELECT DISTINCT deskripsi_log AS nilai FROM log ORDER BY deskripsi_log ASC'],
];
if (!is_string($mode) || !isset($options[$mode])) {
    http_response_code(400);
    exit('Mode data tidak valid.');
}

try {
    $result = $Conn->query($options[$mode][1]);
    $html = '<div class="row mb-3"><div class="col-md-12">'
        . '<label for="nilai_mode_data_grafik">' . $options[$mode][0] . '</label>'
        . '<select name="nilai_mode_data" id="nilai_mode_data_grafik" class="form-control" required>'
        . '<option value="">Pilih ' . $options[$mode][0] . '</option>';
    while ($data = $result->fetch_assoc()) {
        $value = htmlspecialchars($data['nilai'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html .= '<option value="' . $value . '">' . $value . '</option>';
    }
    $result->free();
    echo $html . '</select></div></div>';
} catch (Throwable $exception) {
    error_log('Form mode grafik: ' . $exception->getMessage());
    http_response_code(500);
    echo 'Gagal memuat pilihan data grafik.';
}
