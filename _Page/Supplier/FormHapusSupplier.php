<?php
    // Json Format
    header('Content-Type: application/json; charset=utf-8');

    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Sesi Akses
    if (empty($SessionIdAkses)) {
        echo json_encode([
            "status" => "error",
            "message" => "Sesi Akses Sudah Berakhir. Silahkan Login Ulang!",
            "html"   => ''
        ]);
        exit;
    }

    //Tangkap 'id_supplier'
    if(empty($_POST['id_supplier'])){
        echo json_encode([
            "status" => "error",
            "message" => "ID Supplier Tidak Boleh Kosong!",
            "html"   => ''
        ]);
        exit;
    }

    $id_supplier = (int) $_POST['id_supplier'];

    //Ambil detail supplier dan total volume transaksi dalam 1 query
    $sql = "
        SELECT
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp,
            COALESCE(SUM(t.total), 0) AS total_transaksi
        FROM supplier s
        LEFT JOIN transaksi_jual_beli t ON t.id_supplier = s.id_supplier
        WHERE s.id_supplier = ?
        GROUP BY
            s.id_supplier,
            s.nama_supplier,
            s.alamat_supplier,
            s.email_supplier,
            s.kontak_supplier,
            s.pic,
            s.npwp
        LIMIT 1
    ";
    $stmt = $Conn->prepare($sql);
    if(!$stmt){
        echo json_encode([
            "status" => "error",
            "message" => "Gagal menyiapkan data supplier!",
            "html"   => ''
        ]);
        exit;
    }
    $stmt->bind_param("i", $id_supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $DataSupplier = $result->fetch_assoc();
    $stmt->close();

    if(empty($DataSupplier)){
        echo json_encode([
            "status" => "error",
            "message" => "Data supplier tidak ditemukan!",
            "html"   => ''
        ]);
        exit;
    }

    // Gunakan layout detail yang sama dengan modal detail supplier.
    $display = static function ($value) {
        $value = trim((string) $value);
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES, 'UTF-8');
    };
    $volumeTransaksi = 'Rp '.number_format((float) ($DataSupplier['total_transaksi'] ?? 0), 0, ',', '.');
    $fields = [
        'Nama Supplier' => $DataSupplier['nama_supplier'],
        'Alamat Email' => $DataSupplier['email_supplier'],
        'No. Kontak' => $DataSupplier['kontak_supplier'],
        'Alamat Operasional' => $DataSupplier['alamat_supplier'],
        'PIC (Person In Charge)' => $DataSupplier['pic'],
        'NPWP' => $DataSupplier['npwp'],
        'Volume Transaksi' => $volumeTransaksi
    ];
    ob_start();
?>
<input type="hidden" name="id_supplier" value="<?= (int) $DataSupplier['id_supplier'] ?>">
<div class="detail-info" role="group" aria-label="Supplier yang akan dihapus">
    <?php foreach ($fields as $label => $value): ?>
    <div class="detail-info-row">
        <small class="d-block text-truncate" title="<?= $display($label) ?>"><?= $display($label) ?></small>
        <small aria-hidden="true">:</small>
        <small class="d-block text-truncate text-grayish text-end" title="<?= $display($value) ?>"><?= $display($value) ?></small>
    </div>
    <?php endforeach; ?>
</div>
<div class="alert alert-danger mt-3 mb-0" role="alert">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle flex-shrink-0" aria-hidden="true"></i>
        <div>
            <p class="mb-1"><b>Hapus supplier ini?</b></p>
            <small>Menghapus supplier dapat menyebabkan transaksi terkait kehilangan informasi supplier. Pastikan data di atas sudah sesuai sebelum melanjutkan.</small>
        </div>
    </div>
</div>
<?php
    echo json_encode([
        'status' => 'success',
        'message' => 'Form Berhasil Ditampilkan',
        'html' => ob_get_clean()
    ], JSON_UNESCAPED_UNICODE);
    exit;
