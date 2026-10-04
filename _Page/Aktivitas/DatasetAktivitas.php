<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <b class="card-title"># Dataset Log</b>
            </div>
            <div class="col-6 text-end">
                <button class="btn btn-secondary btn-md btn-floating" type="button" data-bs-toggle="modal" data-bs-target="#ModalFilterAktivitas">
                    <i class="bi bi-search"></i>
                </button>
                <button class="btn btn-secondary btn-md btn-floating" type="button" data-bs-toggle="modal" data-bs-target="#ModalDownloadAktivitas">
                    <i class="bi bi-download"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table table-responsive">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th><b>No</b></th>
                        <th><b>Nama User</b></th>
                        <th><b>Kategori</b></th>
                        <th><b>Deskripsi</b></th>
                        <th><b>Tgl/Jam</b></th>
                    </tr>
                </thead>
                <tbody id="tabel_log">
                    <tr>
                        <td colspan="5" class="text-center">
                            <small>No Data</small>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer border-0">
        <div class="row">
            <div class="col-6">
                <small id="page_info">
                    Page 0 Of 0
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