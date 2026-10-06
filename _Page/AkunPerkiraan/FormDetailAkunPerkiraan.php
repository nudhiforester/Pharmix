<?php
// Muat dependensi untuk respons HTML modal detail.
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';

// Tampilkan pesan singkat jika detail tidak dapat dimuat.
function DetailError($message)
{
    echo '<div class="alert alert-danger text-center"><small>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</small></div>';
    exit;
}
if (empty($SessionIdAkses)) {
    DetailError('Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}
if (empty($_POST['id_perkiraan']) || !is_scalar($_POST['id_perkiraan'])) {
    DetailError('ID Akun Perkiraan Tidak Boleh Kosong.');
}
$idPerkiraan = validateAndSanitizeInput((string) $_POST['id_perkiraan']);

try {
    // Baca detail akun dan kode hierarki dalam satu query berparameter.
    $stmt = mysqli_prepare($Conn, 'SELECT * FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1');
    if (!$stmt) { throw new Exception('Gagal menyiapkan query.'); }
    mysqli_stmt_bind_param($stmt, 's', $idPerkiraan);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca akun.'); }
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$akun) { DetailError('ID Akun Perkiraan Tidak Ditemukan Pada Database!'); }

    // Nama kolom hierarki hanya dibentuk dari level bilangan bulat positif.
    $level = filter_var($akun['level'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($level === false) { throw new Exception('Level akun tidak valid.'); }
    $kolomKd = 'kd' . $level;
    $atasan = [];
    $turunan = [];

    // Ambil seluruh akun di atasnya sekaligus berdasarkan kode kd yang tersimpan.
    $kodeAtasan = [];
    for ($i = 1; $i < $level; $i++) {
        if (!empty($akun['kd' . $i])) { $kodeAtasan[] = $akun['kd' . $i]; }
    }
    if ($kodeAtasan) {
        $placeholder = implode(', ', array_fill(0, count($kodeAtasan), '?'));
        $stmt = mysqli_prepare($Conn, "SELECT kode, nama, level FROM akun_perkiraan WHERE kode IN ($placeholder) ORDER BY level ASC");
        if (!$stmt) { throw new Exception('Gagal menyiapkan query atasan.'); }
        mysqli_stmt_bind_param($stmt, str_repeat('s', count($kodeAtasan)), ...$kodeAtasan);
        if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca atasan.'); }
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) { $atasan[] = $row; }
        mysqli_stmt_close($stmt);
    }

    // Baca seluruh turunan pada cabang akun ini, tanpa memasukkan akun itu sendiri.
    $stmt = mysqli_prepare($Conn, "SELECT kode, nama, level, saldo_normal FROM akun_perkiraan WHERE `$kolomKd` = ? AND level > ?");
    if (!$stmt) { throw new Exception('Gagal menyiapkan query turunan.'); }
    mysqli_stmt_bind_param($stmt, 'si', $akun['kode'], $level);
    if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca turunan.'); }
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) { $turunan[] = $row; }
    mysqli_stmt_close($stmt);

    // Urutkan kode secara alami agar 1.2 tampil sebelum 1.10.
    usort($turunan, function ($a, $b) { return strnatcmp($a['kode'], $b['kode']); });
} catch (Throwable $e) {
    DetailError('Terjadi kesalahan pada saat membaca detail akun perkiraan.');
}

// Escape seluruh nilai database sebelum ditampilkan di modal.
$escape = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
$parameter = [
    'Kode Akun' => $akun['kode'],
    'Nama Akun' => $akun['nama'],
    'Level' => $level,
    'Saldo Normal' => $akun['saldo_normal']
];
?>
<!-- Kolom label, titik dua, dan nilai tetap sejajar, termasuk saat nilai membungkus. -->
<div class="mb-3" style="display: grid; grid-template-columns: minmax(0, 2fr) auto minmax(0, 3fr); gap: .75rem; align-items: start;">
    <?php foreach ($parameter as $label => $value): ?>
        <small><?= $escape($label) ?></small>
        <small>:</small>
        <small class="text-break"><?= $escape($value) ?></small>
    <?php endforeach; ?>
    <small>Akun Di Atasnya</small>
    <small>:</small>
    <div>
        <?php if (!$atasan): ?>
            <small>-</small>
        <?php else: ?>
            <?php foreach ($atasan as $item): ?>
                <small class="d-block text-break"><?= $escape($item['kode']) ?>. <?= $escape($item['nama']) ?></small>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php if ($turunan): ?>
    <!-- Tampilkan anggota di seluruh level bawah dalam list group. -->
    <p class="mb-2"><small class="fw-bold">Akun Di Bawahnya (<?= count($turunan) ?>)</small></p>
    <ul class="list-group">
        <?php foreach ($turunan as $item): ?>
            <li class="list-group-item">
                <small class="d-block text-break fw-semibold"><?= $escape($item['kode']) ?>. <?= $escape($item['nama']) ?></small>
                <small class="text-muted">Level <?= $escape($item['level']) ?> &middot; Saldo Normal: <?= $escape($item['saldo_normal']) ?></small>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
