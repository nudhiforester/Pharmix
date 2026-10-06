<?php
// Muat dependensi aplikasi dan tetapkan format respons AJAX.
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../_Config/Connection.php';
require_once __DIR__ . '/../../_Config/GlobalFunction.php';
require_once __DIR__ . '/../../_Config/Session.php';
require_once __DIR__ . '/KodeAkunBerikutnya.php';
date_default_timezone_set('Asia/Jakarta');

// Kirim hasil proses sesuai kontrak status dan message di JavaScript.
function Response($status, $message)
{
    echo json_encode(['status' => $status, 'message' => $message]);
    exit;
}

// Jalankan prepared statement dengan nilai parameter terpisah dari SQL.
function ExecuteAkunStatement($conn, $query, $types, array $values)
{
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query.');
    }
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

// Validasi sesi, lalu field wajib sesuai urutan validasi sebelumnya.
if (empty($SessionIdAkses)) {
    Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
}
$mandatory = [
    'kode' => 'Kode Perkiraan Tidak Boleh Kosong.',
    'nama' => 'Nama Perkiraan Tidak Boleh Kosong.',
    'id_perkiraan' => 'ID Akun Perkiraan Diatasnya Tidak Boleh Kosong.'
];
$data = [];
foreach ($mandatory as $field => $message) {
    if (empty($_POST[$field]) || !is_scalar($_POST[$field])) {
        Response('error', $message);
    }
    // Pertahankan sanitasi aplikasi pada seluruh input yang digunakan.
    $data[$field] = validateAndSanitizeInput((string) $_POST[$field]);
    if ($data[$field] === '') {
        Response('error', $message);
    }
}

// Inisialisasi data masukan dan status transaksi untuk penanganan kegagalan.
$kode = $data['kode'];
// Tolak segmen kode nonangka meskipun request dikirim di luar form.
if (!preg_match('/^[0-9]+$/D', $kode)) {
    Response('error', 'Kode akun anak hanya boleh berisi angka.');
}
$nama = $data['nama'];
$idPerkiraan = $data['id_perkiraan'];
$transaksiAktif = false;

try {
    // Baca induk sekali, termasuk seluruh kolom kd yang akan diwariskan.
    $stmt = ExecuteAkunStatement($Conn, 'SELECT * FROM akun_perkiraan WHERE id_perkiraan = ? LIMIT 1', 's', [$idPerkiraan]);
    $induk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$induk) {
        Response('error', 'ID Akun Perkiraan Induk Tidak Ditemukan Pada Database!');
    }

    // Validasi level agar nama kolom dinamis hanya berasal dari bilangan positif.
    $levelInduk = filter_var($induk['level'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => PHP_INT_MAX - 1]
    ]);
    if ($levelInduk === false || $induk['kode'] === null || $induk['kode'] === '') {
        Response('error', 'Kode atau level akun induk tidak valid.');
    }
    $kodeBaru = $induk['kode'] . '.' . $kode;
    $levelAnak = $levelInduk + 1;
    if ($kode !== GetKodeAkunBerikutnya($Conn, $levelAnak, $induk['kode'])) {
        Response('error', 'Urutan kode sudah berubah. Tutup dan buka kembali form tambah akun.');
    }
    $saldoNormal = $induk['saldo_normal'];

    // Tolak kode yang sudah terdaftar sebelum mengubah struktur atau data.
    $stmt = ExecuteAkunStatement($Conn, 'SELECT id_perkiraan FROM akun_perkiraan WHERE kode = ? LIMIT 1', 's', [$kodeBaru]);
    $duplikat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($duplikat) {
        Response('error', 'Kode yang anda gunakan sudah ada, silahkan gunakan kode lain.');
    }

    // Susun nilai kd dari induk dan gunakan kode baru untuk level anak sendiri.
    $kolomKd = [];
    $nilaiKd = [];
    $kolomBaru = [];
    for ($level = 1; $level <= $levelAnak; $level++) {
        $kolom = 'kd' . $level;
        $kolomKd[] = '`' . $kolom . '`';
        if ($level < $levelAnak) {
            if (!array_key_exists($kolom, $induk) || $induk[$kolom] === null || $induk[$kolom] === '') {
                Response('error', 'Kode hierarki akun induk tidak lengkap.');
            }
            $nilaiKd[] = $induk[$kolom];
        } else {
            $nilaiKd[] = $kodeBaru;
        }
        if (!array_key_exists($kolom, $induk)) {
            $kolomBaru[] = "ADD COLUMN `$kolom` VARCHAR(50) NULL";
        }
    }

    // Pertahankan pembuatan kolom kd; DDL dijalankan sebelum transaksi data.
    // Identifier berasal dari level tervalidasi; nilai pengguna tidak masuk SQL.
    if ($kolomBaru && !mysqli_query($Conn, 'ALTER TABLE akun_perkiraan ' . implode(', ', $kolomBaru))) {
        throw new Exception('Gagal menambah kolom hierarki akun.');
    }

    // Simpan akun dan log sebagai satu transaksi agar tidak ada data setengah jadi.
    if (!mysqli_begin_transaction($Conn)) {
        throw new Exception('Gagal memulai transaksi.');
    }
    $transaksiAktif = true;

    // Kunci dan baca ulang induk untuk memastikan hierarki belum berubah.
    $stmt = ExecuteAkunStatement($Conn, 'SELECT * FROM akun_perkiraan WHERE id_perkiraan = ? FOR UPDATE', 's', [$idPerkiraan]);
    $indukTerkini = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$indukTerkini || $indukTerkini['kode'] !== $induk['kode'] || $indukTerkini['level'] !== $induk['level']) {
        throw new Exception('Data induk berubah saat proses berlangsung.');
    }
    $saldoNormal = $indukTerkini['saldo_normal'];
    for ($level = 1; $level <= $levelInduk; $level++) {
        $nilaiInduk = $indukTerkini['kd' . $level];
        if ($nilaiInduk === null || $nilaiInduk === '') {
            throw new Exception('Kode hierarki induk tidak lengkap.');
        }
        $nilaiKd[$level - 1] = $nilaiInduk;
    }

    // Ulangi pemeriksaan duplikat setelah induk dikunci.
    $stmt = ExecuteAkunStatement($Conn, 'SELECT id_perkiraan FROM akun_perkiraan WHERE kode = ? LIMIT 1', 's', [$kodeBaru]);
    $duplikat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($duplikat) {
        mysqli_rollback($Conn);
        $transaksiAktif = false;
        Response('error', 'Kode yang anda gunakan sudah ada, silahkan gunakan kode lain.');
    }

    // Satu INSERT menyimpan akun sekaligus seluruh kode hierarkinya.
    $kolomInsert = array_merge(['kode', 'nama', 'level', 'saldo_normal'], $kolomKd);
    $nilaiInsert = array_merge([$kodeBaru, $nama, $levelAnak, $saldoNormal], $nilaiKd);
    $placeholder = implode(', ', array_fill(0, count($nilaiInsert), '?'));
    $queryInsert = 'INSERT INTO akun_perkiraan (' . implode(', ', $kolomInsert) . ') VALUES (' . $placeholder . ')';
    $stmt = ExecuteAkunStatement($Conn, $queryInsert, 'ssis' . str_repeat('s', count($nilaiKd)), $nilaiInsert);
    mysqli_stmt_close($stmt);

    // Catat tindakan dengan prepared statement, menggantikan file InputLog lama.
    $stmt = ExecuteAkunStatement(
        $Conn,
        'INSERT INTO log (id_akses, datetime_log, kategori_log, deskripsi_log) VALUES (?, ?, ?, ?)',
        'ssss',
        [$SessionIdAkses, date('Y-m-d H:i:s'), 'Akun Perkiraan', 'Tambah Akun Perkiraan']
    );
    mysqli_stmt_close($stmt);

    // Konfirmasi perubahan dan kirim respons sukses.
    if (!mysqli_commit($Conn)) {
        throw new Exception('Gagal menyimpan transaksi.');
    }
    $transaksiAktif = false;
    Response('success', 'Data Akun Perkiraan Berhasil Ditambahkan.');
} catch (Throwable $e) {
    // Batalkan penyimpanan akun dan log jika proses gagal.
    if ($transaksiAktif) {
        mysqli_rollback($Conn);
    }
    Response('error', 'Terjadi kesalahan pada saat menyimpan data akun perkiraan.');
}
