<?php
    //Cek Aksesibilitas ke halaman ini
    $IjinAksesSaya=IjinAksesSaya($Conn,$SessionIdAkses,'w4BCAy6VPuZLhkgUQEU');
    if($IjinAksesSaya!=="Ada"){
        include "_Page/Error/NoAccess.php";
    }else{
?>
<div class="pagetitle">
    <h1>
        <a href="">
            <i class="bi bi-circle"></i> Log</a>
        </a>
    </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Log Aktivitas</li>
        </ol>
    </nav>
</div>
<style>
    .activity-mode-row {
        --bs-gutter-x: 1rem;
        --bs-gutter-y: 1rem;
        margin-bottom: 1rem;
    }
    .activity-mode-row > div > a {
        display: flex;
        border-radius: 16px;
    }
    .activity-mode-row .activity-mode-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        color: #16324f;
        width: 100%;
        margin-bottom: 0;
        padding-bottom: 0;
        border: 1px solid rgba(255, 255, 255, .65);
        border-radius: 16px;
        background: linear-gradient(125deg, var(--mode-start), var(--mode-end));
        box-shadow: 0 6px 18px rgba(22, 50, 79, .08);
    }
    .card-inactive {
        --mode-start: #e0f2ff;
        --mode-end: #91d5ff;
        --mode-hover-start: #b9e4ff;
        --mode-hover-end: #69bfff;
        --mode-accent: #135b91;
    }
    .card-active {
        --mode-start: #dcfaf0;
        --mode-end: #8ee0cd;
        --mode-hover-start: #b4f0df;
        --mode-hover-end: #60cdb5;
        --mode-accent: #126451;
    }
    #GrafikAktivitas {
        --mode-start: #f0e8ff;
        --mode-end: #c8b2f7;
        --mode-hover-start: #e2d3ff;
        --mode-hover-end: #b49aee;
        --mode-accent: #624299;
    }
    .activity-mode-card::before,
    .activity-mode-card::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: -1;
    }
    .activity-mode-card::before {
        background: linear-gradient(125deg, var(--mode-hover-start), var(--mode-hover-end));
        opacity: 0;
        transition: opacity .25s ease;
    }
    .activity-mode-card::after {
        background:
            radial-gradient(circle at 87% 18%, transparent 0 27px, rgba(255, 255, 255, .32) 28px 29px, transparent 30px),
            radial-gradient(circle at 73% 86%, rgba(255, 255, 255, .22) 0 38px, transparent 39px),
            radial-gradient(circle at 96% 70%, rgba(255, 255, 255, .5) 0 3px, transparent 4px),
            radial-gradient(circle at 60% 12%, rgba(255, 255, 255, .5) 0 2px, transparent 3px),
            radial-gradient(circle at 43% 92%, rgba(255, 255, 255, .4) 0 4px, transparent 5px);
    }
    #RekapAktivitas .activity-mode-card::after {
        transform: scaleX(-1);
    }
    #GrafikAktivitas .activity-mode-card::after {
        transform: scaleY(-1);
    }
    .activity-mode-row a:hover .activity-mode-card::before,
    .activity-mode-row a:focus-visible .activity-mode-card::before {
        opacity: 1;
    }
    .activity-mode-row a:focus-visible {
        outline: 3px solid #012970;
        outline-offset: 4px;
    }
    .dashboard .activity-mode-card .card-body {
        min-height: 140px;
        padding: 24px;
        gap: 16px;
    }
    .dashboard .activity-mode-card .card-icon {
        flex: 0 0 56px;
        width: 56px;
        height: 56px;
        font-size: 26px;
        color: var(--mode-accent);
        background: rgba(255, 255, 255, .65);
        border: 1px solid rgba(255, 255, 255, .75);
    }
    .activity-mode-card .activity-mode-copy {
        min-width: 0;
    }
    .activity-mode-card small {
        display: block;
        font-size: .82rem;
        line-height: 1.5;
        color: #30465e;
    }
    @media (max-width: 991.98px) {
        .dashboard .activity-mode-card .card-body {
            flex-direction: column;
            justify-content: center;
            padding: 20px 12px;
            gap: 12px;
            text-align: center;
        }
    }
    @media (max-width: 575.98px) {
        .activity-mode-row { --bs-gutter-x: .5rem; }
        .dashboard .activity-mode-card .card-body {
            min-height: 132px;
            padding: 16px 6px;
            gap: 10px;
        }
        .dashboard .activity-mode-card .card-icon {
            flex-basis: 44px;
            width: 44px;
            height: 44px;
            font-size: 22px;
        }
        .activity-mode-card h2 { font-size: .75rem; }
        .activity-mode-card small { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .activity-mode-card::before { transition: none; }
    }
</style>
<section class="section dashboard">

    <!-- Menampilkan Card untuk menampilkan pilihan mode tampilan data log aktivitas user. Pilihan mode tampilan data log aktivitas user terdiri dari 3 mode, yaitu Dataset, Rekapitulasi, dan Grafik. Setiap mode memiliki fungsi yang berbeda-beda. Dataset digunakan untuk menampilkan data log aktivitas user dalam bentuk tabel. Rekapitulasi digunakan untuk menampilkan data log aktivitas user dalam bentuk rekapitulasi. Grafik digunakan untuk menampilkan data log aktivitas user dalam bentuk grafik. -->
    <div class="row align-items-stretch activity-mode-row">
        <div class="col-4 d-flex">
            <a class="w-100 text-decoration-none" href="javascript:void(0);" aria-current="page" data-id="DatasetAktivitas">
                <div class="card info-card sales-card activity-mode-card h-100 card-active">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-table" aria-hidden="true"></i>
                        </div>
                        <div class="activity-mode-copy">
                            <h2 class="h6 mb-1 fw-semibold">
                                Dataset
                            </h2>
                            <small>
                                Tampilkan data log aktivitas user dalam bentuk tabel.
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-4 d-flex">
            <a class="w-100 text-decoration-none" href="javascript:void(0);" data-id="RekapAktivitas">
                <div class="card info-card sales-card activity-mode-card h-100 card-inactive">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i>
                        </div>
                        <div class="activity-mode-copy">
                            <h2 class="h6 mb-1 fw-semibold">
                                Rekapitulasi
                            </h2>
                            <small>
                                Tampilkan data log aktivitas user dengan rekapitulasi.
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-4 d-flex">
            <a class="w-100 text-decoration-none" href="javascript:void(0);" data-id="GrafikAktivitas">
                <div class="card info-card sales-card activity-mode-card h-100 card-inactive">
                    <div class="card-body d-flex align-items-center">
                        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-graph-up" aria-hidden="true"></i>
                        </div>
                        <div class="activity-mode-copy">
                            <h2 class="h6 mb-1 fw-semibold">
                                Grafik
                            </h2>
                            <small>
                                Tampilkan data log aktivitas user dalam bentuk grafik.
                            </small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Menampilkan data log aktivitas user dalam bentuk tabel, rekapitulasi, atau grafik.  -->
    <div class="row mt-3">
        <div class="col-lg-12" id="DataViewAktivitas">
            <!-- Mode Data Akan ditampilkan disini -->
            
        </div>
    </div>
</section>
<?php } ?>
