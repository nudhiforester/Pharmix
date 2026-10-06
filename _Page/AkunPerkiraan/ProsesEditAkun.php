<?php
// Muat dependensi dan siapkan respons JSON untuk submit edit.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';
date_default_timezone_set('Asia/Jakarta');

function Response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Jalankan query berparameter dan tutup statement ketika terjadi kegagalan.
function ExecuteEditStatement($conn, $sql, $types, array $values)
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { throw new Exception('Gagal menyiapkan query.'); }
    try {
        if (!mysqli_stmt_bind_param($stmt, $types, ...$values) || !mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menjalankan query.');
        }
        return $stmt;
    } catch (Throwable $e) {
        mysqli_stmt_close($stmt);
        throw $e;
    }
}

// Validasi sesi dan sanitasi input wajib dengan fungsi aplikasi.
if (empty($SessionIdAkses)) {
    Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}
$fields = [
    'id_perkiraan' => 'ID Akun Perkiraan Tidak Boleh Kosong.',
    'kode' => 'Kode Perkiraan Tidak Boleh Kosong.',
    'nama' => 'Nama Perkiraan Tidak Boleh Kosong.',
    'saldo_normal' => 'Saldo Normal Tidak Boleh Kosong.'
];
$data = [];
foreach ($fields as $field => $message) {
    if (empty($_POST[$field]) || !is_scalar($_POST[$field])) { Response('error', $message); }
    $data[$field] = validateAndSanitizeInput((string) $_POST[$field]);
    if ($data[$field] === '') { Response('error', $message); }
}
$idPerkiraan = $data['id_perkiraan'];
$nama = $data['nama'];
$transaksiAktif = false;
$message = 'Terjadi kesalahan pada saat memperbarui akun perkiraan.';

try {
    // Kunci akun agar validasi dan pembaruan menggunakan data yang konsisten.
    if (!mysqli_begin_transaction($Conn)) { throw new Exception('Gagal memulai transaksi.'); }
    $transaksiAktif = true;
    $stmt = ExecuteEditStatement($Conn, 'SELECT * FROM akun_perkiraan WHERE id_perkiraan = ? FOR UPDATE', 's', [$idPerkiraan]);
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$akun) {
        $message = 'ID Akun Perkiraan Tidak Ditemukan Pada Database!';
        throw new Exception($message);
    }
    $level = filter_var($akun['level'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($level === false) { throw new Exception('Level akun tidak valid.'); }
    $kodeLama = $akun['kode'];
    $kolomKd = 'kd' . $level;

    // Bangun kode lengkap memakai kode induk dari database, bukan hidden input.
    $kodeInduk = $level === 1 ? '' : ($akun['kd' . ($level - 1)] ?? '') . '.';
    if ($level > 1 && $kodeInduk === '.') { throw new Exception('Kode induk tidak valid.'); }
    $kodeBaru = $kodeInduk . $data['kode'];

    // Cegah perubahan kode akun yang memiliki turunan, termasuk request manual.
    $stmt = ExecuteEditStatement($Conn, "SELECT id_perkiraan FROM akun_perkiraan WHERE `$kolomKd` = ? AND level > ? LIMIT 1 FOR UPDATE", 'si', [$kodeLama, $level]);
    $punyaAnak = (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($punyaAnak && $kodeBaru !== $kodeLama) {
        $message = 'Kode akun tidak bisa diubah karena memiliki akun turunan.';
        throw new Exception($message);
    }

    // Pastikan kode baru tidak digunakan akun lain.
    $stmt = ExecuteEditStatement($Conn, 'SELECT id_perkiraan FROM akun_perkiraan WHERE kode = ? AND id_perkiraan <> ? LIMIT 1', 'ss', [$kodeBaru, $idPerkiraan]);
    $duplikat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($duplikat) {
        $message = 'Kode tersebut sudah ada, gunakan kode lain!';
        throw new Exception($message);
    }

    // Hanya level 1 dapat mengubah saldo; akun lain mengikuti akun utama.
    $saldoNormal = $data['saldo_normal'];
    if ($level > 1) {
        $stmt = ExecuteEditStatement($Conn, 'SELECT saldo_normal FROM akun_perkiraan WHERE kode = ? AND level = 1 LIMIT 1 FOR UPDATE', 's', [$akun['kd1']]);
        $utama = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        if (!$utama) { throw new Exception('Akun utama tidak ditemukan.'); }
        $saldoNormal = $utama['saldo_normal'];
    }
    if (!in_array($saldoNormal, ['Debet', 'Kredit'], true)) {
        $message = 'Saldo normal harus Debet atau Kredit.';
        throw new Exception($message);
    }

    // Terapkan saldo baru ke seluruh turunan berdasarkan kode utama lama.
    if ($level === 1 && $saldoNormal !== $akun['saldo_normal']) {
        $stmt = ExecuteEditStatement($Conn, 'UPDATE akun_perkiraan SET saldo_normal = ? WHERE kd1 = ? AND level > 1', 'ss', [$saldoNormal, $kodeLama]);
        mysqli_stmt_close($stmt);
    }

    // Perbarui detail dan kd pada level sendiri agar hierarki mengikuti kode baru.
    $stmt = ExecuteEditStatement($Conn, "UPDATE akun_perkiraan SET kode = ?, nama = ?, saldo_normal = ?, `$kolomKd` = ? WHERE id_perkiraan = ?", 'sssss', [$kodeBaru, $nama, $saldoNormal, $kodeBaru, $idPerkiraan]);
    mysqli_stmt_close($stmt);

    // Pertahankan sinkronisasi kode dan nama akun pada jurnal yang terkait.
    $stmt = ExecuteEditStatement($Conn, 'UPDATE jurnal SET kode_perkiraan = ?, nama_perkiraan = ? WHERE kode_perkiraan = ?', 'sss', [$kodeBaru, $nama, $kodeLama]);
    mysqli_stmt_close($stmt);

    // Catat perubahan tanpa bergantung pada file InputLog lama.
    $stmt = ExecuteEditStatement($Conn, 'INSERT INTO log (id_akses, datetime_log, kategori_log, deskripsi_log) VALUES (?, ?, ?, ?)', 'ssss', [$SessionIdAkses, date('Y-m-d H:i:s'), 'Akun Perkiraan', 'Edit Akun Perkiraan']);
    mysqli_stmt_close($stmt);
    if (!mysqli_commit($Conn)) { throw new Exception('Gagal menyimpan transaksi.'); }
    $transaksiAktif = false;
    Response('success', 'Akun Perkiraan Berhasil Diperbaharui.');
} catch (Throwable $e) {
    // Batalkan seluruh perubahan bila satu proses gagal, lalu kirim pesan JSON.
    if ($transaksiAktif) { mysqli_rollback($Conn); }
    Response('error', $message);
}
