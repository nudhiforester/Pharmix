<?php
date_default_timezone_set('Asia/Jakarta');
include "../../_Config/Connection.php";
include "../../_Config/GlobalFunction.php";
include "../../_Config/Session.php";
header('Content-Type: application/json; charset=utf-8');

function responseJson($status, $message, $data = []) {
    echo json_encode(array_merge(['status' => $status, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($SessionIdAkses)) {
    responseJson('error', 'Sesi akses sudah berakhir. Silakan login ulang.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responseJson('error', 'Metode request tidak valid.');
}

$id_kunjungan = isset($_POST['id_kunjungan']) ? (int) $_POST['id_kunjungan'] : 0;
if ($id_kunjungan <= 0) {
    responseJson('error', 'ID Kunjungan tidak valid.');
}

// Ambil data kunjungan beserta nama pasien
$query = "SELECT kunjungan.*, anggota.id_pasien as rm_pasien, anggota.nama as nama_pasien 
          FROM kunjungan 
          LEFT JOIN anggota ON kunjungan.id_anggota = anggota.id_anggota 
          WHERE kunjungan.id_kunjungan = ?";
          
$stmt = $Conn->prepare($query);
if (!$stmt) {
    responseJson('error', 'Gagal mempersiapkan query database.');
}

$stmt->bind_param('i', $id_kunjungan);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$data) {
    responseJson('error', 'Data kunjungan tidak ditemukan.');
}

$escape = static function ($value) {
    return htmlspecialchars(trim((string) $value) !== '' ? (string) $value : '-', ENT_QUOTES, 'UTF-8');
};
$timestamp = empty($data['tanggal_kunjungan']) ? false : strtotime($data['tanggal_kunjungan']);
$tanggal = $timestamp === false ? '-' : date('d/m/Y H:i', $timestamp).' WIB';
$fields = [
    'No. RM' => $data['rm_pasien'] ?? '-',
    'Nama Pasien' => $data['nama_pasien'] ?? 'Tanpa Nama',
    'Tgl. Kunjungan' => $tanggal,
    'Poliklinik' => $data['nama_poli'] ?? '-',
    'Status' => $data['status'] ?? '-'
];
ob_start();
?>
<input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan ?>">
<div class="card border mb-3">
    <div class="card-header">
        <b class="card-title"># Informasi Kunjungan</b>
    </div>
    <div class="card-body pt-3 pb-2">
        <?php foreach ($fields as $label => $value): ?>
        <div class="row mb-2">
            <div class="col-5"><small><?= $escape($label) ?></small></div>
            <div class="col-1"><small>:</small></div>
            <div class="col-6 text-end text-break"><small style="overflow-wrap: anywhere;"><?= $escape($value) ?></small></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="alert alert-warning mb-0" role="alert">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle flex-shrink-0" aria-hidden="true"></i>
        <div>
            <p class="mb-1"><b>Hapus kunjungan ini?</b></p>
            <small>Pastikan data pasien dan kunjungan sudah sesuai. Tindakan ini tidak dapat dibatalkan.</small>
        </div>
    </div>
</div>
<?php
$html = ob_get_clean();

responseJson('success', 'Berhasil memuat data.', ['html' => $html]);
?>
