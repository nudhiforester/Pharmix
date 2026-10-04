<div class="row mb-3">
    <div class="col-md-12 text-xxl-end text-xl-end text-md-end text-sm-center text-center">

        <!-- Tombol ini berfungsi untuk menampilkan modal yang akan mengubah periode data rekapitulasi log. -->
        <button type="button"  class="btn btn-md btn-outline-primary btn-rounded" data-bs-toggle="modal" data-bs-target="#ModalPeriodeRekapAktivitas" id="ButtonFilterRekapAktivitas">
            <i class="bi bi-calendar"></i> Periode : 2026
        </button>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-header border-0">
                <b class="card-title"># User/Pengguna</b>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <form action="javascript:void(0);" method="POST" id="FormSearchRekapUser">
                            <div class="input-group">
                                <input type="hidden" name="page" id="page_rekap_user" value="1">
                                <input type="hidden" name="limit" id="limit_rekap_user" value="10">
                                <input type="text" class="form-control" name="keyword" id="search_rekap_user" placeholder="Cari User/Pengguna">
                                <button class="input-group-text" type="submit" id="button_search_rekap_user" aria-label="Cari">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12" id="data_list_rekap_user">
                        <!-- Isi card body untuk menampilkan jumlah user/pengguna -->
                         <div class="alert alert-warning text-center" role="alert">
                            <small> No Data </small>
                         </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_rekap_user">
                            Page 0 Of 0
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_rekap_user">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_rekap_user">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-header border-0">
                <b class="card-title"># Kategori Aktivitas</b>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <form action="javascript:void(0);" method="POST" id="FormSearchRekapKategori">
                            <div class="input-group">
                                <input type="hidden" name="page" id="page_rekap_kategori" value="1">
                                <input type="hidden" name="limit" id="limit_rekap_kategori" value="10">
                                <input type="text" class="form-control" name="keyword" id="search_rekap_kategori" placeholder="Cari Kategori Aktivitas">
                                <button class="input-group-text" type="submit" id="button_search_rekap_kategori" aria-label="Cari">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12" id="data_list_rekap_kategori">
                        <!-- Isi card body untuk menampilkan jumlah kategori -->
                         <div class="alert alert-warning text-center" role="alert">
                            <small> No Data </small>
                         </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_rekap_kategori">
                            Page 0 Of 0
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_rekap_kategori">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_rekap_kategori">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-header border-0">
                <b class="card-title"># Deskripsi Aktivitas</b>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-12">
                        <form action="javascript:void(0);" method="POST" id="FormSearchRekapDeskripsi">
                            <div class="input-group">
                                <input type="hidden" name="page" id="page_rekap_deskripsi" value="1">
                                <input type="hidden" name="limit" id="limit_rekap_deskripsi" value="10">
                                <input type="text" class="form-control" name="keyword" id="search_rekap_deskripsi" placeholder="Cari Deskripsi Aktivitas">
                                <button class="input-group-text" type="submit" id="button_search_rekap_deskripsi" aria-label="Cari">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12" id="data_list_rekap_deskripsi">
                        <!-- Isi card body untuk menampilkan jumlah deskripsi -->
                         <div class="alert alert-warning text-center" role="alert">
                            <small> No Data </small>
                         </div>
                    </div>
                </div>
            </div>
            <div class="card-footer border-0">
                <div class="row">
                    <div class="col-6">
                        <small id="page_info_rekap_deskripsi">
                            Page 0 Of 0
                        </small>
                    </div>
                    <div class="col-6 text-end">
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="prev_button_rekap_deskripsi">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-md btn-outline-info btn-floating" id="next_button_rekap_deskripsi">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>