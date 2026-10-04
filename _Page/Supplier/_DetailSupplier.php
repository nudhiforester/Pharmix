<?php
    // Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";

    // Time Zone
    date_default_timezone_set('Asia/Jakarta');

    // Time Now Tmp
    $now = date('Y-m-d H:i:s');

    // Validasi sesi login
    if (empty($SessionIdAkses)) {
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    Sesi Akses Sudah Berakhir! Silahkan Login Ulang!
                </small>
            </div>
        ';
        exit;
    }

    // Buat Variabel
    $id_supplier = validateAndSanitizeInput($_POST['id_supplier']);

    // Membuka 
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
        $error=$Conn->error;
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    Terjadi Kesalahan Pada Saat Membuka Data Supplier.<br>
                    Keterangan : '.$error.'
                </small>
            </div>
        ';
        exit;
    }
    $stmt->bind_param("i", $id_supplier);
    $stmt->execute();
    $result = $stmt->get_result();
    $DataSupplier = $result->fetch_assoc();
    $stmt->close();

    if(empty($DataSupplier)){
        echo '
            <div class="alert alert-danger text-center">
                <small>
                    <b>Opss!</b><br>
                    Data Supplier Tidak Ditemukan!
                </small>
            </div>
        ';
        exit;
    }

    // Ringkasan memakai nilai asli; escape dilakukan saat ditampilkan ke HTML.
    $display = static function ($value) {
        $value = trim((string) $value);
        return htmlspecialchars($value === '' ? '-' : $value, ENT_QUOTES, 'UTF-8');
    };
    $volumeTransaksi = 'Rp '.number_format((float) ($DataSupplier['total_transaksi'] ?? 0), 0, ',', '.');
    $fields = [
        'Nama Supplier'          => $DataSupplier['nama_supplier'],
        'Alamat Email'           => $DataSupplier['email_supplier'],
        'No. Kontak'             => $DataSupplier['kontak_supplier'],
        'Alamat Operasional'     => $DataSupplier['alamat_supplier'],
        'PIC (Person In Charge)' => $DataSupplier['pic'],
        'NPWP'                   => $DataSupplier['npwp'],
        'Volume Transaksi'       => $volumeTransaksi
    ];
?>
<div class="row g-3 MobileCard-grid">
    <!-- Card Aksi -->
    <div class="col-md-12">
        <div class="card card-tambah h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                <div class="aksi-resep mb-3">
                    <button type="button" class="icon-tambah tombol_kembali" title="Kembali Ke Data Supllier">
                        <i class="bi bi-chevron-left"></i>
                        <span class="visually-hidden">Kembali</span>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalEditSupplier" data-id="<?php echo $id_supplier; ?>" title="Edit Supplier">
                        <i class="bi bi-pencil"></i>
                        <span class="visually-hidden">Edit Supplier</span>
                    </button>
                    <button type="button" class="icon-tambah" id="tombol_cari" data-bs-toggle="modal" data-bs-target="#ModalHapusSupplier" data-id="<?php echo $id_supplier; ?>" title="Edit Supplier">
                        <i class="bi bi-trash"></i>
                        <span class="visually-hidden">Hapus Supplier</span>
                    </button>
                </div>
                <h6 class="mb-1">Detail Supplier</h6>
                <small class="text-muted">
                    Lihat Detail Informasi Supplier Atau Lihat Riwayat Transaksi
                </small>
            </div>
        </div>
    </div>

</div>
<div class="row mt-4">
    <!-- Mulai layar xl, kolom flex membuat kedua card mengikuti tinggi card tertinggi. -->
    <div class="col-xl-4 d-xl-flex">
        <div class="card w-100">
            <div class="card-header">
                <b class="card-title">
                    <i class="bi bi-info-circle"></i> Detail Supplier
                </b>
            </div>
            <div class="card-body">
                <div class="detail-info" role="group" aria-label="Informasi supplier">
                    <?php foreach ($fields as $label => $value): ?>
                    <div class="detail-info-row">
                        <small class="d-block text-truncate" title="<?= $display($label) ?>"><?= $display($label) ?></small>
                        <small aria-hidden="true">:</small>
                        <small class="d-block text-truncate text-grayish" title="<?= $display($value) ?>"><?= $display($value) ?></small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8 d-xl-flex">
        <div class="card card-data card-table w-100">
            <div class="card-header">
                <div class="row">
                    <div class="col-lg-6 col-md-12 mb-2">
                        <b class="card-title">
                            <i class="bi bi-clock-history"></i> Riwayat Transaksi
                        </b>
                    </div>
                    <!-- Tombol di tengah saat kolom bertumpuk, rata kanan mulai layar lg. -->
                    <div class="col-lg-6 col-md-12 text-center text-lg-end mb-2">
                        <a href="javascript:void(0);" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalExportTransaksi" title="Download/Export Data Riwayat Transaksi">
                            <i class="bi bi-download"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilterTransaksi" title="Filter Data / Cari Data">
                            <i class="bi bi-search"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="table-load-container mt-3">
                <table class="table table-hover table-responsive-card" id="TableRiwayatTransaksi">
                    <thead>
                        <tr>
                            <th><b>Referensi</b></th>
                            <th><b>Tgl</b></th>
                            <th><b>Uraian</b></th>
                            <th class="text-end"><b>Harga</b></th>
                            <th class="text-end"><b>QTY</b></th>
                            <th class="text-end"><b>PPN</b></th>
                            <th class="text-end"><b>Diskon</b></th>
                            <th class="text-end"><b>Jumlah</b></th>
                            <th><b>Status</b></th>
                        </tr>
                    </thead>
                    <tbody id="tabel_transaksi_supplier">
                        <tr class="table-empty">
                            <td colspan="9" class="text-center">
                                <h1 class="bi bi-inbox"></h1>
                                <small class="text-danger">Tidak Ada Data Transaksi Yang Ditemukan</small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                        <small id="page_info_transaksi">
                            Page 0 Of 0
                        </small>
                    </div>
                    <!-- Pagination selebar baris dan rata tengah pada tampilan mobile. -->
                    <div class="col-12 col-md-6 text-center text-md-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_transaksi">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_transaksi">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
