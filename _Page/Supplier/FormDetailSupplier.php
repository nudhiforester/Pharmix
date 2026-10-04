<?php
    //Koneksi
    date_default_timezone_set('Asia/Jakarta');
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    //Tangkap id_supplier
    if(empty($_POST['id_supplier'])){
        echo '  <div class="row">';
        echo '      <div class="col-md-6 mb-3">';
        echo '          ID Supplier Tidak Boleh Kosong!.';
        echo '      </div>';
        echo '  </div>';
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
        echo '  <div class="row">';
        echo '      <div class="col-md-6 mb-3 text-danger">';
        echo '          Gagal menyiapkan data supplier.';
        echo '      </div>';
        echo '  </div>';
        exit;
    }
    $stmt->bind_param("i", $id_supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $DataSupplier = $result->fetch_assoc();
    $stmt->close();

    if(empty($DataSupplier)){
        echo '  <div class="row">';
        echo '      <div class="col-md-6 mb-3 text-danger">';
        echo '          Data supplier tidak ditemukan.';
        echo '      </div>';
        echo '  </div>';
        exit;
    }

    // Ringkasan memakai nilai asli; escape dilakukan saat ditampilkan ke HTML.
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
?>
<input type="hidden" name="id_supplier" value="<?= (int) $DataSupplier['id_supplier'] ?>">
<div class="detail-info" role="group" aria-label="Informasi supplier">
    <?php foreach ($fields as $label => $value): ?>
    <div class="detail-info-row">
        <small class="d-block text-truncate" title="<?= $display($label) ?>"><?= $display($label) ?></small>
        <small aria-hidden="true">:</small>
        <small class="d-block text-truncate text-grayish text-end" title="<?= $display($value) ?>"><?= $display($value) ?></small>
    </div>
    <?php endforeach; ?>
</div>
