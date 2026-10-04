<?php
    // Koneksi dan session
    include __DIR__ . '/../../_Config/Connection.php';
    include __DIR__ . '/../../_Config/GlobalFunction.php';
    include __DIR__ . '/../../_Config/Session.php';

    // Validasi akses sebelum menghitung data transaksi
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>Sesi Akses Sudah Berakhir, Silahkan Login Ulang!</small>
            </div>
        ';
        exit;
    }

    // Tangkap ID supplier yang dikirim saat modal export dibuka
    $id_supplier = filter_var($_POST['id_supplier'] ?? '', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);
    if ($id_supplier === false) {
        echo '
            <div class="alert alert-danger text-center">
                <small>ID Supplier Tidak Boleh Kosong Atau Tidak Valid!</small>
            </div>
        ';
        exit;
    }

    // Hitung baris rincian transaksi; supplier ditentukan dari transaksi induknya
    try {
        $sql = "
            SELECT COUNT(*) AS jml
            FROM transaksi_jual_beli_rincian r
            INNER JOIN transaksi_jual_beli t
                ON t.id_transaksi_jual_beli = r.id_transaksi_jual_beli
            WHERE t.id_supplier = ?
        ";
        $stmt = mysqli_prepare($Conn, $sql);
        if (!$stmt) {
            throw new RuntimeException('Gagal menyiapkan query jumlah transaksi.');
        }
        mysqli_stmt_bind_param($stmt, 'i', $id_supplier);
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('Gagal menghitung jumlah transaksi.');
        }
        $result   = mysqli_stmt_get_result($stmt);
        $data     = mysqli_fetch_assoc($result);
        $jml_data = (int)$data['jml'];
        mysqli_stmt_close($stmt);
    } catch (Exception $e) {
        error_log('Form export transaksi supplier: ' . $e->getMessage());
        echo '
            <div class="alert alert-danger text-center">
                <small>Gagal Menghitung Data Transaksi Yang Akan Di Export.</small>
            </div>
        ';
        exit;
    }

    // Apabila tidak ada data yang bisa diexport
    if (empty($jml_data)) {
        echo '
            <div class="alert alert-danger">
                <small>Tidak Ada Data Transaksi Supplier Yang Bisa Di Export. Silahkan Tambahkan Data Transaksi Terlebih Dulu!</small>
            </div>
        ';
        exit;
    }

    // Sertakan ID supplier untuk proses export ketika form modal disubmit
    // Tampilan ringkasan mengikuti FormExportSupplier.php
    echo '
        <input type="hidden" name="id_supplier" value="'.$id_supplier.'">
        <div class="row mb-2">
            <div class="col-12 text-center">
                <small>Jumlah Data</small><br>
                <h2>'.number_format($jml_data, 0, ',', '.').' Baris</h2>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12 text-center">
                <small>Format Data : Excel</small>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <small>Semakin banyak data transaksi supplier yang ada maka proses export akan membutuhkan waktu lebih lama.</small>
                </div>
            </div>
        </div>
    ';
?>
