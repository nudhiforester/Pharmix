<?php
// Siapkan respons JSON serta dependensi koneksi dan sesi.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';
require_once __DIR__ . '/KodeAkunBerikutnya.php';

// Kirim respons yang digunakan oleh handler pemuatan form.
function Response($status, $message, $html = '')
{
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'html' => $html
    ]);
    exit;
}

// Validasi sesi dan ID akun induk sebelum menjalankan query.
if (empty($SessionIdAkses)) {
    Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}
if (empty($_POST['id_perkiraan']) || !is_scalar($_POST['id_perkiraan'])) {
    Response('error', 'ID Akun Perkiraan Tidak Dapat Didefinisikan.');
}
$idPerkiraan = trim((string) $_POST['id_perkiraan']);
if ($idPerkiraan === '') {
    Response('error', 'ID Akun Perkiraan Tidak Dapat Didefinisikan.');
}

// Ambil seluruh data yang diperlukan dalam satu prepared statement.
try {
    $stmt = mysqli_prepare($Conn, 'SELECT id_perkiraan, kode, level, saldo_normal FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query akun.');
    }
    mysqli_stmt_bind_param($stmt, 's', $idPerkiraan);
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Gagal membaca akun.');
    }
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    // Nomor anak berikutnya dihitung hanya dari anak langsung pada induk ini.
    if ($akun) {
        $kodeAnak = GetKodeAkunBerikutnya($Conn, (int) $akun['level'] + 1, $akun['kode']);
    }
} catch (Throwable $e) {
    Response('error', 'Terjadi kesalahan pada saat membaca data akun perkiraan.');
}

// Hentikan pemuatan jika akun induk tidak ditemukan.
if (!$akun) {
    Response('error', 'ID Akun Perkiraan Tidak Ditemukan Pada Database!');
}

// Escape nilai database sebelum menyisipkannya ke atribut HTML.
$idPerkiraanHtml = htmlspecialchars((string) $akun['id_perkiraan'], ENT_QUOTES, 'UTF-8');
$kodeIndukHtml = htmlspecialchars($akun['kode'] . '.', ENT_QUOTES, 'UTF-8');
$saldoNormalHtml = htmlspecialchars((string) $akun['saldo_normal'], ENT_QUOTES, 'UTF-8');

// Tampung markup form agar respons hanya berisi JSON.
ob_start();
?>
<input type="hidden" name="id_perkiraan" value="<?= $idPerkiraanHtml ?>">
<div class="row mb-3">
    <div class="col-md-4">
        <label for="kode_anak">Kode Akun</label>
    </div>
    <div class="col-md-8">
        <div class="input-group">
            <input type="text" readonly name="kode_induk" id="kode_induk" class="form-control" value="<?= $kodeIndukHtml ?>" required>
            <input type="text" id="kode_anak" class="form-control" value="<?= htmlspecialchars($kodeAnak, ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" pattern="[0-9]+" disabled>
            <input type="hidden" name="kode" value="<?= htmlspecialchars($kodeAnak, ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <small class="text-muted">Kode otomatis mengikuti nomor anak terbesar + 1 pada akun induk ini.</small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-4">
        <label for="nama_anak">Nama Akun</label>
    </div>
    <div class="col-md-8">
        <input type="text" name="nama" id="nama_anak" class="form-control" required>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-4">
        <label for="saldo_normal_anak">Saldo Normal</label>
    </div>
    <div class="col-md-8">
        <input type="text" readonly name="saldo_normal" id="saldo_normal_anak" class="form-control" value="<?= $saldoNormalHtml ?>">
    </div>
</div>
<?php
// Sertakan form pada respons sukses untuk ditampilkan oleh JavaScript.
$html = ob_get_clean();
Response('success', 'Form Tambah Akun Perkiraan Berhasil Dimuat.', $html);
