<div class="modal fade" id="ModalFilter" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-filter"></i> Filter Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="batas">Limit/Batas</label>
                            <select name="batas" id="batas" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="OrderBy">Order By</label>
                            <select name="OrderBy" id="OrderBy" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_supplier">Nama Supplier</option>
                                <option value="email_supplier">Email Perusahaan</option>
                                <option value="kontak_supplier">Kontak Perusahaan</option>
                                <option value="pic">PIC</option>
                                <option value="npwp">NPWP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="ShortBy">Short By</label>
                            <select name="ShortBy" id="ShortBy" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="keyword_by">Keyword By</label>
                            <select name="keyword_by" id="keyword_by" class="form-control">
                                <option value="">Pilih</option>
                                <option value="nama_supplier">Nama Supplier</option>
                                <option value="email_supplier">Email Perusahaan</option>
                                <option value="kontak_supplier">Kontak Perusahaan</option>
                                <option value="pic">PIC</option>
                                <option value="npwp">NPWP</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12" id="FormFilterKeyword">
                            <label for="keyword">Keyword</label>
                            <input type="text" name="keyword" id="keyword" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Tambah Supplier -->
<div class="modal fade" id="ModalFilterTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilterTransaksi">
                <input type="hidden" name="page" id="page_transaksi" value="1">
                <input type="hidden" name="id_supplier" id="id_supplier_transaksi" value="">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-funnel"></i> Filter Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="batas_riwayat_transaksi">Limit/Batas</label>
                            <select name="batas" id="batas_riwayat_transaksi" class="form-control">
                                <option value="5">5</option>
                                <option selected value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="OrderByRiwayatTransaksi"><i>Order By</i></label>
                            <select name="OrderBy" id="OrderByRiwayatTransaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="id_transaksi_jual_beli">ID Transaksi</option>
                                <option value="kategori">Kategori Transaksi</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="nama_barang">Uraian</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="ShortByRiwayatTransaksi"><i>Short By</i></label>
                            <select name="ShortBy" id="ShortByRiwayatTransaksi" class="form-control">
                                <option value="DESC">Z To A</option>
                                <option value="ASC">A To Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="keyword_by_riwayat_transaksi"><i>Keyword By</i></label>
                            <select name="keyword_by" id="keyword_by_riwayat_transaksi" class="form-control">
                                <option value="">Pilih</option>
                                <option value="id_transaksi_jual_beli">ID Transaksi</option>
                                <option value="kategori">Kategori Transaksi</option>
                                <option value="tanggal">Tanggal</option>
                                <option value="nama_barang">Uraian</option>
                                <option value="status">Status</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12" id="FormFilterKeywordRiwayatTransaksi">
                            <label for="keyword_riwayat_transaksi"><i>Keyword</i></label>
                            <input type="text" name="keyword" id="keyword_riwayat_transaksi" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Supplier -->
<div class="modal fade" id="ModalTambahSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesTambahSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-plus"></i> Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nama_supplier"><span class="text-danger">*</span> Nama Supplier</label>
                            <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="email_supplier">Email Perusahaan</label>
                            <input type="email" name="email_supplier" id="email_supplier" class="form-control" placeholder="email@domain.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="kontak_supplier">Kontak Perusahaan</label>
                            <input type="text" name="kontak_supplier" id="kontak_supplier" class="form-control" placeholder="62">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="alamat_supplier">Alamat Kantor</label>
                            <input type="text" name="alamat_supplier" id="alamat_supplier" class="form-control" placeholder="Contoh : Jalan Anggrek 4 Nomor 5 Kabupaten Kuningan-Jawa Barat">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="pic">PIC <i>(Person In Charge)</i></label>
                            <input type="text" name="pic" id="pic" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="npwp">NPWP (Nomor Pokok Wajib Pajak)</label>
                            <input type="text" name="npwp" id="npwp" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiTambahSupplier">
                            <!-- Notifikasi Tambah Supplier -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolTambahSupplier">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Supplier -->
<div class="modal fade" id="ModalDetailSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesDetail">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-info-circle"></i> Detail Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col-12" id="FormDetailSupplier">
                            <!-- Form Detail -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" id="NotifikasiDetailSupplier">
                            <!-- Notifikasi Detail -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        Selengkapnya <i class="bi bi-chevron-right"></i>
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Supplier -->
<div class="modal fade" id="ModalEditSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesEditSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-pencil"></i> Edit Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormEditSupplier">
                            <!-- Form Edit Supplier -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiEditSupplier">
                            <!-- Notifikasi Edit Supplier -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolEditSupplier">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Supplier -->
<div class="modal fade" id="ModalHapusSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesHapusSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-trash"></i> Hapus Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormHapusSupplier">
                            <!-- Form Hapus Disini -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12" id="NotifikasiHapusSupplier">
                            <!-- Notifikasi Hapus Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" id="TombolHapusSupplier">
                        <i class="bi bi-check"></i> Ya, Hapus
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tidak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Supplier -->
<div class="modal fade" id="ModalExportSupplier" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Supplier/ProsesExportSupplier.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export/Download Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExportSupplier">
                            <!-- Form Export Supplier Akan Tampil Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="ModalImportSupplier" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesImportSupplier">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-upload"></i> Import Data Supplier
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <small class="credit">
                                Sebelum melakukan import data, perhatikan hal berikut ini.
                                <ol>
                                    <li>
                                        Pastikan anda menggunakan template file untuk melakukan import 
                                        pada link <a href="_Page/Supplier/Template-Supplier.xlsx">berikut ini</a>.
                                    </li>
                                    <li>
                                        Isi kolom <b>No, Nama Supplier, Alamat, Email, Kontak, PIC dan NPWP</b> sesuai data yang anda miliki.
                                    </li>
                                    <li>
                                        Kolom <b>No dan Nama Supplier</b> wajib diisi sebagai data utama identifikasi supplier.
                                    </li>
                                    <li>
                                        Sistem akan menolak data jika Nama Supplier sudah terdaftar atau NPWP yang diisi sudah digunakan.
                                    </li>
                                </ol>
                            </small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="file_supplier">Upload File (Excel)</label>
                            <div class="input-group">
                                <input type="file" name="file_supplier" id="file_supplier" class="form-control">
                                <button type="submit" class="btn btn-primary" id="TombolImport">
                                    <i class="bi bi-upload"></i> Import
                                </button>
                            </div>
                            <small class="text text-muted">Maksimal 10 mb</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="table-responsive border rounded" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th><b>No</b></th>
                                            <th><b>Supplier</b></th>
                                            <th><b>Alamat</b></th>
                                            <th><b>Email</b></th>
                                            <th><b>Kontak</b></th>
                                            <th><b>PIC</b></th>
                                            <th><b>NPWP</b></th>
                                        </tr>
                                    </thead>
                                    <tbody id="NotifikasiImportSupplier">
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <!-- Notifikasi Import Akan Muncul Disini -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-md w-100" id="TombolSelesai" disabled>
                                <i class="bi bi-check"></i> Selesai
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Transaksi -->
<div class="modal fade" id="ModalExportTransaksi" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Supplier/ProsesExportTransaksi.php" method="POST" target="_blank">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-download"></i> Export/Download Transaksi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormExportTransaksi">
                            <!-- Form Export Supplier Akan Tampil Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-download"></i> Export
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detail Transaksi -->
<div class="modal fade" id="ModalDetailTransaksi" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesCetakTransaksi">
                <div class="modal-header">
                    <h5 class="modal-title text-dark">
                        <i class="bi bi-info-circle"></i> Detail Transaksi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12" id="FormDetailTransaksi">
                            <!-- Form Export Supplier Akan Tampil Disini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-printer"></i> Cetak
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

