<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'rA8MRFArw1qPeVySjAC');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-app"></i> Fitur Aplikasi</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Fitur Aplikasi</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo '
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <small>
                                Berikut ini adalah halaman pengelolaan data fitur aplikasi yang digunakan oleh pengembang untuk memetakan ijin akses setiap pengguna pada halaman dan modul aplikasi. 
                                Penting untuk diketahui bahwa mengubah data pada halaman ini, akan merubah aturan khusus pada fitur yang digunakan. 
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    ';
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-data card-table">
                    <div class="card-header">
                        <form action="javascript:void(0);" id="ProsesBatas">
                            <div class="row">
                                <div class="col-12 text-end mb-3">
                                    <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Filter">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahFitur" title="Tambah Fitur">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-load-container mt-3">
                            <table class="table table-hover table-responsive-card" id="TableAksesFitur">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b>Fitur</b></th>
                                        <th><b>Kategori</b></th>
                                        <th><b>Kode</b></th>
                                        <th><b>Akses/User</b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="MenampilkanTabelFitur">
                                    <!-- Menampilkan Tabel Fitur -->
                                    <tr class="table-empty">
                                        <td colspan="6" class="text-center">
                                            <small class="text-danger">Tidak Ada Data Fitur Yang Ditampilkan!</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <div class="card-footer border-0">
                        <div class="row">
                            <div class="col-6">
                                <small id="page_info">
                                    Page 1 Of 100
                                </small>
                            </div>
                            <div class="col-6 text-end">
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="prev_button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info btn-floating" id="next_button">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php } ?>