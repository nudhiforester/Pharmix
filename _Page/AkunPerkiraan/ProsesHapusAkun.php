<?php
// Tetapkan format respons AJAX dan zona waktu aplikasi.
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set('Asia/Jakarta');

// Muat koneksi database, fungsi bersama, dan validasi sesi pengguna.
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';

/**
 * Kirim status dan pesan dalam format JSON, lalu hentikan proses.
 */
function Response($status, $message)
{
    echo json_encode([
        'status' => $status,
        'message' => $message
    ]);
    exit;
}

/** Susun kode berurutan per induk, dengan urutan angka lama tetap dipertahankan. */
function SusunKodeAkun(array $daftarAkun)
{
    usort($daftarAkun, function ($a, $b) {
        $bedaLevel = (int) $a['level'] <=> (int) $b['level'];
        return $bedaLevel ?: strnatcmp($a['kode'], $b['kode']);
    });
    $petaKode = [];
    $nomorInduk = [];
    $perubahan = [];
    foreach ($daftarAkun as $akun) {
        $level = filter_var($akun['level'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($level === false || !preg_match('/^[0-9]+(?:\.[0-9]+)*$/D', $akun['kode']) || isset($petaKode[$akun['kode']])) {
            throw new Exception('Struktur kode akun tidak valid.');
        }
        $indukLama = $level === 1 ? '' : ($akun['kd' . ($level - 1)] ?? '');
        if ($level > 1 && !isset($petaKode[$indukLama])) {
            throw new Exception('Akun induk tidak ditemukan.');
        }
        $indukBaru = $level === 1 ? '' : $petaKode[$indukLama];
        $nomor = ($nomorInduk[$indukBaru] ?? 0) + 1;
        $nomorInduk[$indukBaru] = $nomor;
        $kodeBaru = $indukBaru === '' ? (string) $nomor : $indukBaru . '.' . $nomor;
        if (count(explode('.', $akun['kode'])) !== $level) {
            throw new Exception('Level dan kode akun tidak sesuai.');
        }
        $petaKode[$akun['kode']] = $kodeBaru;
        $akun['kode_baru'] = $kodeBaru;
        $perubahan[] = $akun;
    }
    return $perubahan;
}

/** Jalankan query perubahan berparameter dan bebaskan statement setelah digunakan. */
function JalankanQuerySusunAkun($conn, $sql, array $nilai)
{
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) { throw new Exception('Gagal menyiapkan query penomoran ulang.'); }
    try {
        if (!mysqli_stmt_bind_param($stmt, str_repeat('s', count($nilai)), ...$nilai) || !mysqli_stmt_execute($stmt)) {
            throw new Exception('Gagal menyimpan penomoran ulang.');
        }
    } finally {
        mysqli_stmt_close($stmt);
    }
}

// Pastikan pengguna masih memiliki sesi akses yang valid.
if (empty($SessionIdAkses)) {
    Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}

// Validasi ID akun yang dikirim dari form sebelum mengakses database.
if (empty($_POST['id_perkiraan']) || !is_scalar($_POST['id_perkiraan'])) {
    Response('error', 'ID Perkiraan Tidak Boleh Kosong!');
}

// Inisialisasi ID akun dan penanda transaksi untuk penanganan rollback.
$idPerkiraan = validateAndSanitizeInput($_POST['id_perkiraan']);
$transaksiAktif = false;

try {
    // Satukan penghapusan akun dan pencatatan log dalam satu transaksi.
    if (!mysqli_begin_transaction($Conn)) {
        throw new Exception('Gagal memulai transaksi.');
    }
    $transaksiAktif = true;

    // Kunci seluruh akun karena penomoran ulang dapat memengaruhi setiap cabang.
    $resultKunci = mysqli_query($Conn, 'SELECT id_perkiraan FROM akun_perkiraan ORDER BY id_perkiraan FOR UPDATE');
    if (!$resultKunci) { throw new Exception('Gagal mengunci akun.'); }
    mysqli_free_result($resultKunci);

    // Ambil kode dan level akun terpilih, serta kunci datanya selama transaksi.
    $queryAkun = 'SELECT kode, level FROM akun_perkiraan WHERE id_perkiraan = ? FOR UPDATE';
    $stmtAkun = mysqli_prepare($Conn, $queryAkun);
    if (!$stmtAkun) {
        throw new Exception('Gagal menyiapkan query akun.');
    }

    mysqli_stmt_bind_param($stmtAkun, 's', $idPerkiraan);
    if (!mysqli_stmt_execute($stmtAkun)) {
        throw new Exception('Gagal membaca akun.');
    }
    $akun = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtAkun));
    mysqli_stmt_close($stmtAkun);

    // Hentikan proses jika akun sudah tidak tersedia di database.
    if (!$akun) {
        mysqli_rollback($Conn);
        $transaksiAktif = false;
        Response('error', 'ID Akun Perkiraan Tidak Ditemukan Pada Database!');
    }

    // Inisialisasi kode dan validasi level sebelum menyusun nama kolom dinamis.
    $kodeAkun = $akun['kode'];
    $levelAkun = filter_var($akun['level'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($levelAkun === false || $kodeAkun === null || $kodeAkun === '') {
        throw new Exception('Kode atau level akun tidak valid.');
    }

    // Level 1 menggunakan kd1, level 2 menggunakan kd2, dan seterusnya.
    // Nama kolom berasal dari level bilangan bulat positif, bukan input mentah.
    $kolomKode = 'kd' . $levelAkun;
    $queryHapus = "DELETE FROM akun_perkiraan WHERE `$kolomKode` = ?";
    $stmtHapus = mysqli_prepare($Conn, $queryHapus);
    if (!$stmtHapus) {
        throw new Exception('Gagal menyiapkan query hapus akun.');
    }

    // Hapus akun terpilih beserta seluruh akun turunannya yang memiliki kd sama.
    // Data jurnal terkait ditangani oleh relasi database.
    mysqli_stmt_bind_param($stmtHapus, 's', $kodeAkun);
    if (!mysqli_stmt_execute($stmtHapus)) {
        throw new Exception('Gagal menghapus akun dan akun turunannya.');
    }
    $jumlahTerhapus = mysqli_stmt_affected_rows($stmtHapus);
    mysqli_stmt_close($stmtHapus);
    if ($jumlahTerhapus < 1) {
        throw new Exception('Tidak ada akun yang terhapus.');
    }

    // Baca akun yang tersisa dan susun nomor 1..n pada setiap kelompok saudara.
    $resultAkun = mysqli_query($Conn, 'SELECT * FROM akun_perkiraan ORDER BY id_perkiraan FOR UPDATE');
    if (!$resultAkun) { throw new Exception('Gagal membaca akun tersisa.'); }
    $daftarAkun = mysqli_fetch_all($resultAkun, MYSQLI_ASSOC);
    mysqli_free_result($resultAkun);
    $susunanAkun = SusunKodeAkun($daftarAkun);

    // Gunakan kode sementara unik untuk menghindari benturan kode lama dan baru.
    // ID akun tetap sama, sehingga relasi berdasarkan ID tidak berubah.
    foreach ($susunanAkun as $item) {
        if ($item['kode'] === $item['kode_baru']) { continue; }
        $kodeSementara = '~' . $item['id_perkiraan'];
        JalankanQuerySusunAkun($Conn, 'UPDATE akun_perkiraan SET kode = ? WHERE id_perkiraan = ?', [$kodeSementara, $item['id_perkiraan']]);
        // Sinkronkan jurnal juga pada database yang belum memiliki ON UPDATE CASCADE.
        JalankanQuerySusunAkun($Conn, 'UPDATE jurnal SET kode_perkiraan = ? WHERE kode_perkiraan = ?', [$kodeSementara, $item['kode']]);
    }

    // Simpan kode akhir dan semua awalan kd sesuai posisi akun dalam hierarki baru.
    foreach ($susunanAkun as $item) {
        $setKolom = ['kode = ?'];
        $nilaiKolom = [$item['kode_baru']];
        $bagianKode = explode('.', $item['kode_baru']);
        $awalanKode = [];
        foreach ($bagianKode as $index => $bagian) {
            $awalanKode[] = $bagian;
            $setKolom[] = '`kd' . ($index + 1) . '` = ?';
            $nilaiKolom[] = implode('.', $awalanKode);
        }
        $nilaiKolom[] = $item['id_perkiraan'];
        JalankanQuerySusunAkun($Conn, 'UPDATE akun_perkiraan SET ' . implode(', ', $setKolom) . ' WHERE id_perkiraan = ?', $nilaiKolom);
        if ($item['kode'] !== $item['kode_baru']) {
            JalankanQuerySusunAkun($Conn, 'UPDATE jurnal SET kode_perkiraan = ? WHERE kode_perkiraan = ?', [$item['kode_baru'], '~' . $item['id_perkiraan']]);
        }
    }

    // Inisialisasi informasi log untuk mencatat tindakan penghapusan akun.
    $waktuLog = date('Y-m-d H:i:s');
    $kategoriLog = 'Akun Perkiraan';
    $deskripsiLog = 'Hapus Akun Perkiraan';

    // Simpan log; kegagalan log juga membatalkan penghapusan akun.
    $hasilLog = addLog($Conn, $SessionIdAkses, $waktuLog, $kategoriLog, $deskripsiLog);
    if ($hasilLog !== 'Success') {
        throw new Exception('Gagal menyimpan log.');
    }

    // Konfirmasi seluruh perubahan sebelum mengirim respons sukses.
    if (!mysqli_commit($Conn)) {
        throw new Exception('Gagal menyimpan transaksi.');
    }
    $transaksiAktif = false;
    Response('success', 'Akun berhasil dihapus dan kode akun perkiraan berhasil disusun ulang.');
} catch (Throwable $e) {
    // Batalkan perubahan jika transaksi belum selesai dan proses mengalami error.
    if ($transaksiAktif) {
        mysqli_rollback($Conn);
    }

    // Kirim pesan gagal yang dapat ditampilkan oleh handler AJAX.
    Response('error', 'Terjadi kesalahan pada saat menghapus akun perkiraan.');
}
