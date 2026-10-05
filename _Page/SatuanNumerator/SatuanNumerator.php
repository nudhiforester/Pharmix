<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'kzdf7lZVWpSIHvvz3lm');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
    <div class="pagetitle">
        <h1>
            <a href="">
                <i class="bi bi-box"></i> Satuan - Numerator</a>
            </a>
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Satuan - Numerator</li>
            </ol>
        </nav>
    </div>
    <section class="section dashboard">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <small>
                        Halaman ini digunakan untuk mengelola referensi Satuan - Numerator. Informasi tersebut berguna pada saat mengisi kelengkapan informasi <i>Medication</i>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </small>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-data card-table">
                    <div class="card-header">
                       <div class="row">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                        <h6>Option</h6>
                                    </li>
                                    <li>
                                        <a class="dropdown-item modal_export" href="javascript:void(0)">
                                            <i class="bi bi-download"></i> Export
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item modal_import" href="javascript:void(0)">
                                            <i class="bi bi-upload"></i> Import
                                        </a>
                                    </li>
                                </ul>
                                <button type="button" class="btn btn-md btn-secondary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalFilter">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-primary btn-floating" data-bs-toggle="modal" data-bs-target="#ModalTambah">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="table-load-container mt-3">
                            <table class="table table-hover table-responsive-card" id="TableSatuanNumerator">
                                <thead>
                                    <tr>
                                        <th><b>No</b></th>
                                        <th><b><i>Unit</i></b></th>
                                        <th><b><i>Code</i></b></th>
                                        <th><b><i>System</i></b></th>
                                        <th><b>Opsi</b></th>
                                    </tr>
                                </thead>
                                <tbody id="TabelSatuanNumerator">
                                    <tr class="table-empty">
                                        <td class="text-center" colspan="5">
                                            <small>Loading...</small>
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
                                <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button">
                                    <i class="bi bi-chevron-left"></i>
                                </button>
                                <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button">
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