<div class="modal fade" id="ModalFilterAktivitas" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="ProsesFilter">
                <input type="hidden" name="page" id="page" value="1">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-filter"></i> Filter Log</h5>
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
                                <option value="nama_akses">Nama User</option>
                                <option value="kategori_log">Kategori</option>
                                <option value="deskripsi_log">Deskripsi</option>
                                <option value="datetime_log">Tanggal/Jam</option>
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
                                <option value="nama_akses">Nama User</option>
                                <option value="kategori_log">Kategori</option>
                                <option value="deskripsi_log">Deskripsi</option>
                                <option value="datetime_log">Tanggal</option>
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

<div class="modal fade" id="ModalDownloadAktivitas" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Aktivitas/ProsesExportAktivitas.php" method="POST" target="_blank" id="ProsesDownload">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode_data">Periode Data</label>
                            <select name="periode_data" id="periode_data" class="form-control">
                                <option value="Semua">Semua</option>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Harian">Harian</option>
                            </select>
                        </div>
                    </div>
                    <div id="FormFilterPeriode">
                        <!-- Akan Menampilkan Form Lanjutan Sesuai periode_data -->
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                           <div class="alert alert-warning text-center" role="alert">
                                <h1 class="h3"><i class="bi bi-exclamation-triangle"></i></h1>
                                <b>Perhatian!</b>
                                <br>
                                <small> 
                                    Periode data yang dipilih akan menentukan jumlah data yang diexport. 
                                    Pastikan periode data yang dipilih sesuai dengan kebutuhan.
                                </small>
                           </div>
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

<div class="modal fade" id="ModalPeriodeRekapAktivitas" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0);" id="FilterPeriodeRekapAktivitas">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-calendar"></i> Periode Rekap Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode_data_rekap_aktivitas">Periode Data</label>
                            <select name="periode_data" id="periode_data_rekap_aktivitas" class="form-control">
                                <option value="Semua">Semua</option>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Harian">Harian</option>
                            </select>
                        </div>
                    </div>
                    <div id="FormFilterPeriodeRekapAktivitas">
                        <!-- Akan Menampilkan Form Lanjutan Sesuai periode_data -->
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

<div class="modal fade" id="ModalRincianRekapAktivitas" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="_Page/Aktivitas/ProsesExportRincianRekapAktivitas.php" method="POST" target="_blank">
                <div id="ParameterExportRincianRekapAktivitas"></div>
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-list-columns"></i> Rincian Rekap Aktivitas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="table table-responsive">
                                <table class="table table-striped table-hover" id="TableRincianRekapAktivitas">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama User</th>
                                            <th>Kategori</th>
                                            <th>Deskripsi</th>
                                            <th>Tanggal/Jam</th>
                                        </tr>
                                    </thead>
                                    <tbody id="BodyTableRincianRekapAktivitas">
                                        <!-- Akan Menampilkan Data Rincian Rekap Aktivitas -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded" disabled id="ButtonExportRincianRekapAktivitas">
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


<div class="modal fade" id="ModalDownloadAktivitas" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="_Page/Aktivitas/ProsesExportAktivitas.php" method="POST" target="_blank" id="ProsesDownload">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Export Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode_data">Periode Data</label>
                            <select name="periode_data" id="periode_data" class="form-control">
                                <option value="Semua">Semua</option>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Bulanan">Bulanan</option>
                                <option value="Harian">Harian</option>
                            </select>
                        </div>
                    </div>
                    <div id="FormFilterPeriode">
                        <!-- Akan Menampilkan Form Lanjutan Sesuai periode_data -->
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                           <div class="alert alert-warning text-center" role="alert">
                                <h1 class="h3"><i class="bi bi-exclamation-triangle"></i></h1>
                                <b>Perhatian!</b>
                                <br>
                                <small> 
                                    Periode data yang dipilih akan menentukan jumlah data yang diexport. 
                                    Pastikan periode data yang dipilih sesuai dengan kebutuhan.
                                </small>
                           </div>
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

<!-- Modal Untuk Filter Grafik Aktivitas, Periode Rekap Aktivitas, Rincian Rekap Aktivitas, dan Download Aktivitas. -->
<div class="modal fade" id="ModalFilterGrafikAktivitas" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <form action="javascript:void(0)" id="FilterGrafikAktivitas">
                <div class="modal-header">
                    <h5 class="modal-title text-dark"><i class="bi bi-download"></i> Filter Grafik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="mode_data_grafik">Mode Data</label>
                            <select name="mode_data" id="mode_data_grafik" class="form-control">
                                <option value="semua" selected>Semua</option>
                                <option value="user_pengguna">User/Pengguna</option>
                                <option value="kategori_aktivitas">Kategori Aktivitas</option>
                                <option value="deskripsi_aktivitas">Deskripsi Aktivitas</option>
                            </select>
                        </div>
                    </div>
                    <div id="FormFilterModeData">
                        <!-- Akan Menampilkan Form Lanjutan Sesuai mode_data_grafik -->
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="periode_data_grafik">Periode Data</label>
                            <select name="periode_data" id="periode_data_grafik" class="form-control">
                                <option value="Tahunan" selected>Tahunan</option>
                                <option value="Bulanan">Bulanan</option>
                            </select>
                        </div>
                    </div>
                    <div id="FormFilterPeriodeGrafik">
                        <!-- Akan Menampilkan Form Lanjutan Sesuai periode_data -->
                         <div class="row">
                            <div class="col-12">
                                <label for="tahun_grafik">Tahun</label>
                                <input type="text" name="tahun" id="tahun_grafik" class="form-control" value="<?= date('Y') ?>"
                                    inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                         </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-responsive">
                    <button type="submit" class="btn btn-primary btn-rounded">
                        <i class="bi bi-check"></i> Tampilkan
                    </button>
                    <button type="button" class="btn btn-secondary btn-rounded" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="ModalDeleteAkses" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-light"><i class="bi bi-trash"></i> Hapus Akses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="FormDeleteAkses">
                
            </div>
            <div class="modal-footer bg-danger">
                <button type="button" class="btn btn-success btn-rounded" id="KonfirmasiHapusAkses">
                    <i class="bi bi-check"></i> Ya
                </button>
                <button type="button" class="btn btn-dark btn-rounded" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Tidak
                </button>
            </div>
        </div>
    </div>
</div>
