<?php
// Hitung nomor berikutnya; anak hanya dibandingkan dengan saudara pada induk yang sama.
function GetKodeAkunBerikutnya($conn, $level = 1, $kodeInduk = null)
{
    $level = filter_var($level, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($level === false) { throw new Exception('Level akun tidak valid.'); }
    $sql = "SELECT CAST(COALESCE(MAX(CAST(SUBSTRING_INDEX(kode, '.', -1) AS DECIMAL(20,0))), 0) + 1 AS CHAR) AS kode FROM akun_perkiraan WHERE level = ? AND SUBSTRING_INDEX(kode, '.', -1) REGEXP '^[0-9]+$'";
    if ($level > 1) { $sql .= ' AND `kd' . ($level - 1) . '` = ?'; }
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { throw new Exception('Gagal menyiapkan kode akun.'); }
    try {
        if ($level === 1) {
            mysqli_stmt_bind_param($stmt, 'i', $level);
        } else {
            mysqli_stmt_bind_param($stmt, 'is', $level, $kodeInduk);
        }
        if (!mysqli_stmt_execute($stmt)) { throw new Exception('Gagal membaca kode akun.'); }
        $data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        return $data['kode'];
    } finally {
        mysqli_stmt_close($stmt);
    }
}
