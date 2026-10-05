<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'8KbdfArJ7UmoX916kO7');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-layers"></i> Entitas Akses</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Entitas Akses</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <?php
                    echo '<div class="alert alert-warning alert-dismissible fade show" role="alert">';
                    echo '  <small>';
                    echo '      Fitur ini memungkinkan anda untuk membagi pengguna menjadi beberapa bagian entitas yang memiliki hak akses berbeda-beda satu dengan yang lainnya.';
                    echo '      Dengan menggunakan AksesEntitas ini, dapat mempermudah anda dalam memonitoring pengguna apikasi yang dibagi dalam beberapa kelompok.';
                    echo '      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '  </small>';
                    echo '</div>';
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-data card-table">
                    <div class="card-header">
                        <form action="javascript:void(0);" id="ProsesBatas">
                            <div class="row">
                                <div class="col-md-12 mb-3 text-end">
                                    <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter" title="Filter/Cari">
                                        <i class="bi bi-search"></i>
                                    </button>
                                    <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambahAksesEntitas" title="Tambah Level/Entitas Akses">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-load-container mt-3">
                            <table class="table table-hover table-responsive-card" id="TableAksesEntitas">
                                <thead><tr>
                                    <th><b>No</b></th>
                                    <th><b>Entitias</b></th>
                                    <th><b>Keterangan</b></th>
                                    <th><b>Pengguna</b></th>
                                    <th><b>Role</b></th>
                                    <th><b>Opsi</b></th>
                                </tr></thead>
                                <tbody id="TabelAksesEntitas">
                                    <tr class="table-empty">
                                        <td class="text-center" colspan="6">
                                            <small>No Data</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    <div class="card-footer border-0">
                        <div class="row">
                            <div class="col-6">
                                <small id="page_info">
                                    Page 1 Of 1
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