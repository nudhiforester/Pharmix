<?php
// Siapkan dependensi dan kontrak respons untuk pemuatan form edit.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';

function Response($status, $message, $html = '')
{
    echo json_encode(['status' => $status, 'message' => $message, 'html' => $html]);
    exit;
}

// Validasi sesi dan sanitasi ID akun sebelum membaca database.
if (empty($SessionIdAkses)) {
    Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}
if (empty($_POST['id_perkiraan']) || !is_scalar($_POST['id_perkiraan'])) {
    Response('error', 'ID Akun Perkiraan Tidak Boleh Kosong.');
}
$idPerkiraan = validateAndSanitizeInput((string) $_POST['id_perkiraan']);

try {
    // Ambil detail dan kode hierarki akun dalam satu query berparameter.
    $stmt = mysqli_prepare($Conn, 'SELECT * FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1');
    if (!$stmt) { throw new Exception('Gagal menyiapkan query.'); }
    mysqli_stmt_bind_param($stmt, 's', $idPerkiraan);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca akun.'); }
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$akun) {
        Response('error', 'ID Akun Perkiraan Tidak Ditemukan Pada Database!');
    }

    // Normalisasi level agar pemeriksaan level 1 konsisten.
    $level = filter_var($akun['level'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($level === false) { throw new Exception('Level akun tidak valid.'); }
    $kolomKd = 'kd' . $level;
    $kodeInduk = $level === 1 ? '' : ($akun['kd' . ($level - 1)] ?? '') . '.';
    if ($level > 1 && $kodeInduk === '.') { throw new Exception('Kode induk tidak valid.'); }
    $kodeInput = $level === 1 ? $akun['kode'] : substr($akun['kode'], strlen($kodeInduk));

    // Periksa keberadaan turunan tanpa membaca seluruh barisnya.
    $stmt = mysqli_prepare($Conn, "SELECT id_perkiraan FROM akun_perkiraan WHERE `$kolomKd` = ? AND level > ? LIMIT 1");
    if (!$stmt) { throw new Exception('Gagal menyiapkan query turunan.'); }
    mysqli_stmt_bind_param($stmt, 'si', $akun['kode'], $level);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca turunan.'); }
    $punyaAnak = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
} catch (Throwable $e) {
    Response('error', 'Terjadi kesalahan pada saat membaca data akun perkiraan.');
}

// Escape data database pada setiap atribut HTML.
$escape = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
ob_start();
?>
<input type="hidden" name="id_perkiraan" value="<?= $escape($akun['id_perkiraan']) ?>">
<div class="row mb-3">
    <div class="col-md-4"><label for="level_edit">Level</label></div>
    <div class="col-md-8"><input type="text" id="level_edit" class="form-control" value="<?= $level ?>" disabled></div>
</div>
<div class="row mb-3">
    <div class="col-md-4"><label for="kode_edit">Kode Akun</label></div>
    <div class="col-md-8">
        <div class="input-group">
            <?php if ($level > 1): ?>
                <input type="text" name="kode_induk" id="kode_induk_edit" class="form-control" value="<?= $escape($kodeInduk) ?>" readonly>
            <?php endif; ?>
            <!-- Kolom kode dikunci; hidden input tetap mengirim kode saat submit. -->
            <input type="text" id="kode_edit" class="form-control" value="<?= $escape($kodeInput) ?>" disabled>
            <input type="hidden" name="kode" value="<?= $escape($kodeInput) ?>">
        </div>
        <small class="text-muted">Kode akun ditetapkan otomatis dan tidak dapat diubah.</small>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-4"><label for="nama_edit">Nama Akun</label></div>
    <div class="col-md-8"><input type="text" name="nama" id="nama_edit" class="form-control" value="<?= $escape($akun['nama']) ?>" required></div>
</div>
<div class="row mb-3">
    <div class="col-md-4"><label for="saldo_normal_edit">Saldo Normal</label></div>
    <div class="col-md-8">
        <?php if ($level === 1): ?>
            <select name="saldo_normal" id="saldo_normal_edit" class="form-control" required>
                <option value="">Pilih</option>
                <option value="Debet" <?= $akun['saldo_normal'] === 'Debet' ? 'selected' : '' ?>>Debet</option>
                <option value="Kredit" <?= $akun['saldo_normal'] === 'Kredit' ? 'selected' : '' ?>>Kredit</option>
            </select>
            <small class="text-muted">Perubahan saldo normal berlaku untuk seluruh akun turunannya.</small>
        <?php else: ?>
            <input type="text" name="saldo_normal" id="saldo_normal_edit" class="form-control" value="<?= $escape($akun['saldo_normal']) ?>" readonly>
            <small class="text-muted">Saldo normal mengikuti akun level 1.</small>
        <?php endif; ?>
    </div>
</div>
<?php
// Sertakan markup form hanya di dalam respons JSON.
$html = ob_get_clean();
Response('success', 'Form Edit Akun Berhasil Dimuat.', $html);
