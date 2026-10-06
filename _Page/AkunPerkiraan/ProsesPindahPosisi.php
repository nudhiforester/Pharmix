<?php
// Siapkan sesi dan respons JSON untuk perpindahan posisi.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';
date_default_timezone_set('Asia/Jakarta');
function Response($status, $message) {
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}
function UpdatePosisi($conn, $sql, array $values) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { throw new Exception('Gagal menyiapkan query.'); }
    try {
        if (!mysqli_stmt_bind_param($stmt, str_repeat('s', count($values)), ...$values) || !mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan posisi.');
        }
    } finally { mysqli_stmt_close($stmt); }
}

// Hitung kode baru kedua cabang dengan batas awalan titik yang tepat.
function KodePindah($kode, $asal, $tujuan) {
    if ($kode === $asal) { return $tujuan; }
    if (strpos($kode, $asal . '.') === 0) { return $tujuan . substr($kode, strlen($asal)); }
    if ($kode === $tujuan) { return $asal; }
    if (strpos($kode, $tujuan . '.') === 0) { return $asal . substr($kode, strlen($tujuan)); }
    return $kode;
}
if (empty($SessionIdAkses)) { Response('error', 'Sesi akses sudah berakhir. Silahkan login ulang.'); }
if (empty($_POST['id_perkiraan']) || !is_scalar($_POST['id_perkiraan']) || !isset($_POST['arah']) || !is_string($_POST['arah']) || !in_array($_POST['arah'], ['atas', 'bawah'], true)) {
    Response('error', 'ID akun atau arah perpindahan tidak valid.');
}
$id = validateAndSanitizeInput((string) $_POST['id_perkiraan']);
$arah = $_POST['arah'];
$aktif = false;
$message = 'Terjadi kesalahan saat memindahkan posisi akun.';
try {
    // Kunci akun dalam urutan ID yang sama dengan proses penomoran ulang.
    if (!mysqli_begin_transaction($Conn)) { throw new Exception('Gagal memulai transaksi.'); }
    $aktif = true;
    $result = mysqli_query($Conn, 'SELECT * FROM akun_perkiraan ORDER BY id_perkiraan FOR UPDATE');
    if (!$result) { throw new Exception('Gagal membaca akun.'); }
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    $akun = null;
    foreach ($rows as $row) { if ((string) $row['id_perkiraan'] === $id) { $akun = $row; break; } }
    if (!$akun) { $message = 'Akun tidak ditemukan.'; throw new Exception($message); }
    $level = filter_var($akun['level'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($level === false) { throw new Exception('Level tidak valid.'); }
    $kolomInduk = 'kd' . ($level - 1);
    $induk = $level === 1 ? '' : ($akun[$kolomInduk] ?? null);
    if ($level > 1 && !$induk) { throw new Exception('Induk tidak valid.'); }

    // Cari tetangga terdekat hanya pada level dan induk yang sama.
    $saudara = array_values(array_filter($rows, function ($row) use ($level, $kolomInduk, $induk) {
        return (int) $row['level'] === $level && ($level === 1 || ($row[$kolomInduk] ?? null) === $induk);
    }));
    usort($saudara, function ($a, $b) { return strnatcmp($a['kode'], $b['kode']); });
    $index = array_search($id, array_map('strval', array_column($saudara, 'id_perkiraan')), true);
    $indexTujuan = $index + ($arah === 'atas' ? -1 : 1);
    if (!isset($saudara[$indexTujuan])) {
        $message = 'Akun sudah berada pada posisi paling ' . ($arah === 'atas' ? 'atas.' : 'bawah.');
        throw new Exception($message);
    }
    $asal = $akun['kode'];
    $tujuan = $saudara[$indexTujuan]['kode'];
    $perubahan = [];
    foreach ($rows as $row) {
        // Kode numerik memastikan kode sementara tidak berbenturan dengan akun lain.
        if (!preg_match('/^[0-9]+(?:\.[0-9]+)*$/D', $row['kode'])) { throw new Exception('Kode akun tidak valid.'); }
        $baru = KodePindah($row['kode'], $asal, $tujuan);
        if ($baru !== $row['kode']) {
            if (count(explode('.', $baru)) !== (int) $row['level']) { throw new Exception('Hierarki akun tidak valid.'); }
            $row['baru'] = $baru;
            $perubahan[] = $row;
        }
    }

    // Dua tahap menghindari benturan saat menukar kode; ID dan data akun tetap.
    foreach ($perubahan as $row) {
        $sementara = '~' . $row['id_perkiraan'];
        UpdatePosisi($Conn, 'UPDATE akun_perkiraan SET kode = ? WHERE id_perkiraan = ?', [$sementara, $row['id_perkiraan']]);
        UpdatePosisi($Conn, 'UPDATE jurnal SET kode_perkiraan = ? WHERE kode_perkiraan = ?', [$sementara, $row['kode']]);
    }
    foreach ($perubahan as $row) {
        // Bangun ulang setiap awalan kd agar seluruh turunan mengikuti posisi baru.
        $set = ['kode = ?'];
        $values = [$row['baru']];
        $awalan = [];
        foreach (explode('.', $row['baru']) as $i => $bagian) {
            $awalan[] = $bagian;
            $set[] = '`kd' . ($i + 1) . '` = ?';
            $values[] = implode('.', $awalan);
        }
        $values[] = $row['id_perkiraan'];
        UpdatePosisi($Conn, 'UPDATE akun_perkiraan SET ' . implode(', ', $set) . ' WHERE id_perkiraan = ?', $values);
        UpdatePosisi($Conn, 'UPDATE jurnal SET kode_perkiraan = ? WHERE kode_perkiraan = ?', [$row['baru'], '~' . $row['id_perkiraan']]);
    }
    // Catat perpindahan dan konfirmasi semua perubahan bersama-sama.
    UpdatePosisi($Conn, 'INSERT INTO log (id_akses, datetime_log, kategori_log, deskripsi_log) VALUES (?, ?, ?, ?)', [$SessionIdAkses, date('Y-m-d H:i:s'), 'Akun Perkiraan', 'Pindah Posisi Akun Perkiraan']);
    if (!mysqli_commit($Conn)) { throw new Exception('Gagal menyimpan transaksi.'); }
    $aktif = false;
    Response('success', 'Posisi akun perkiraan berhasil dipindahkan.');
} catch (Throwable $e) {
    if ($aktif) { mysqli_rollback($Conn); }
    Response('error', $message);
}
