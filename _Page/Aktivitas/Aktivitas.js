// ==============================================================
// FUNCTION
// ==============================================================

//Dataset Log
function DatasetAktivitas() {
    // Target And Filter
    let target = $('#tabel_log');
    let data   = $('#ProsesFilter').serialize();

    target.addClass('blur-loading');

    $.ajax({
        type    : 'POST',
        url     : '_Page/Aktivitas/TabelAktivitas.php',
        data    : data,
        dataType: 'json',
        success : function(res) {

            if(res.status === "success"){

                target.fadeOut(150, function () {
                    target.html(res.html).fadeIn(150);
                });

                // Update info page
                $('#page').val(res.page);
                $('#page_info').html('Page ' + res.page + ' Of ' + res.total_page);

                // Handle tombol
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop('disabled', res.page >= res.total_page);

            }else{
                target.html(res.html);
            }

        },
        error: function() {
            target.html('<tr><td colspan="5" class="text-center text-danger"><small>Gagal memuat data log. Silakan coba kembali.</small></td></tr>');
        },
        complete: function() {
            target.removeClass('blur-loading');
        }
    });
}

const rekapForms = {user: '#FormSearchRekapUser', kategori: '#FormSearchRekapKategori', deskripsi: '#FormSearchRekapDeskripsi'};
const rekapRequests = {};
let periodeRekapAktif = null;

function LoadDataRekapAktivitas(tipe) {
    let target = $('#data_list_rekap_' + tipe);
    if (!target.length) return;
    if (rekapRequests[tipe]) rekapRequests[tipe].abort();
    target.addClass('blur-loading');
    $('#prev_button_rekap_' + tipe + ', #next_button_rekap_' + tipe).prop('disabled', true);
    let periode = periodeRekapAktif || $('#FilterPeriodeRekapAktivitas').serialize();
    rekapRequests[tipe] = $.ajax({
        type: 'POST',
        url: '_Page/Aktivitas/DataRekapAktivitas.php',
        data: periode + '&' + $(rekapForms[tipe]).serialize() + '&tipe=' + tipe,
        dataType: 'json',
        success: function(res) {
            if (res.status !== 'success') {
                target.text(res.message || 'Gagal memuat rekap.');
                return;
            }
            target.html(res.html);
            $('#page_rekap_' + tipe).val(res.page);
            $('#page_info_rekap_' + tipe).text('Page ' + res.page + ' Of ' + res.total_page);
            $('#prev_button_rekap_' + tipe).prop('disabled', res.page <= 1);
            $('#next_button_rekap_' + tipe).prop('disabled', res.page >= res.total_page);
        },
        error: function(xhr, status) {
            if (status !== 'abort') target.text(xhr.responseJSON?.message || 'Gagal memuat rekap. Silakan coba kembali.');
        },
        complete: function() { target.removeClass('blur-loading'); }
    });
    return rekapRequests[tipe];
}

function RekapUserAktivitas() { return LoadDataRekapAktivitas('user'); }
function RekapKategoriAktivitas() { return LoadDataRekapAktivitas('kategori'); }
function RekapDeskripsiAktivitas() { return LoadDataRekapAktivitas('deskripsi'); }

function RefreshRekapAktivitas() {
    Object.keys(rekapForms).forEach(function(tipe) { $('#page_rekap_' + tipe).val(1); });
    RekapUserAktivitas();
    RekapKategoriAktivitas();
    RekapDeskripsiAktivitas();
}

let chartGrafikAktivitas = null;
let requestGrafikAktivitas = null;

function HapusGrafikAktivitas() {
    if (requestGrafikAktivitas) requestGrafikAktivitas.abort();
    if (chartGrafikAktivitas) chartGrafikAktivitas.destroy();
    chartGrafikAktivitas = null;
}

function TampilkanGrafikAktivitas() {
    const target = $('#ChartGrafikAktivitas');
    if (!target.length) return;
    HapusGrafikAktivitas();
    target.text('Loading...');
    if (typeof ApexCharts === 'undefined') {
        target.text('Pustaka ApexCharts belum tersedia.');
        return;
    }
    return requestGrafikAktivitas = $.ajax({
        type: 'POST',
        url: '_Page/Aktivitas/DataGrafikAktivitas.php',
        data: $('#FilterGrafikAktivitas').serialize(),
        dataType: 'json',
        success: function(res) {
            if (res.status !== 'success') {
                target.text(res.message || 'Gagal memuat grafik.');
                return;
            }
            target.empty();
            chartGrafikAktivitas = new ApexCharts(target[0], {
                chart: {
                    type: 'bar', height: 400,
                    zoom: {enabled: false},
                    toolbar: {
                        show: true,
                        tools: {download: true, selection: false, zoom: false, zoomin: false, zoomout: false, pan: false, reset: false},
                        export: {png: {filename: 'Grafik_Aktivitas'}, svg: {filename: 'Grafik_Aktivitas'}}
                    }
                },
                title: {text: res.title, align: 'center'},
                series: res.series,
                xaxis: {categories: res.categories, title: {text: $('#periode_data_grafik').val() === 'Tahunan' ? 'Bulan' : 'Tanggal'}},
                yaxis: {min: 0, forceNiceScale: true, decimalsInFloat: 0, title: {text: 'Jumlah Log'}},
                dataLabels: {enabled: false},
                plotOptions: {bar: {borderRadius: 3, columnWidth: '60%'}},
                colors: ['#4154f1'],
                noData: {text: 'Tidak ada data log.'}
            });
            chartGrafikAktivitas.render().catch(function() {
                target.text('Gagal menampilkan grafik. Silakan coba kembali.');
            });
            $('#ModalFilterGrafikAktivitas').modal('hide');
        },
        error: function(xhr, status) {
            if (status !== 'abort') target.text(xhr.responseJSON?.message || 'Gagal memuat grafik. Silakan coba kembali.');
        }
    });
}

// Perbarui tombol setelah tampilan rekap tersedia di DOM.
function UpdateButtonPeriodeRekapAktivitas() {
    let target = $('#ButtonFilterRekapAktivitas');
    if (!target.length) return;

    target.text('Loading...');
    return $.ajax({
        type: 'POST',
        url: '_Page/Aktivitas/RoutingButtonPeriodeRekapAktivitas.php',
        data: periodeRekapAktif || $('#FilterPeriodeRekapAktivitas').serialize(),
        dataType: 'text',
        success: function(label) {
            target.empty().append($('<i>').addClass('bi bi-calendar'));
            target.append(document.createTextNode(' Periode : ' + label));
        },
        error: function(xhr) {
            target.text(xhr.responseText || 'Gagal memuat periode. Silakan coba kembali.');
        }
    });
}

// ==============================================================
// EVENT HANDDLER
// ==============================================================
$(document).ready(function() {

    // -------------------------------------------------------------
    // ROUTING PAGE AKTIVITAS
    // -------------------------------------------------------------
    // Pada saat pertama kali, DataViewAktivitas akan menampilkan data aktivitas umum
    $('#DataViewAktivitas').html("Loading...");
    $('#DataViewAktivitas').load("_Page/Aktivitas/DatasetAktivitas.php", function(response, status) {
        if (status === 'success') DatasetAktivitas();
    });

    $(document).on('click', '#prev_button, #next_button', function() {
        if ($(this).prop('disabled')) return;
        let page = parseInt($('#page').val(), 10) || 1;
        $('#page').val(Math.max(1, page + (this.id === 'next_button' ? 1 : -1)));
        DatasetAktivitas();
    });

    // Handle Routing halaman Berdasarkan card yang di click
    $('.activity-mode-row a[data-id]').click(function(){
        var modeLinks = $(this).closest('.activity-mode-row').find('a[data-id]');
        modeLinks.removeAttr('aria-current');
        modeLinks.find('.activity-mode-card').removeClass('card-active').addClass('card-inactive');
        $(this).attr('aria-current', 'page');
        $(this).find('.activity-mode-card').removeClass('card-inactive').addClass('card-active');

        var viewType = $(this).data('id');
        HapusGrafikAktivitas();
        $('#DataViewAktivitas').html("Loading...");
        $('#DataViewAktivitas').load("_Page/Aktivitas/" + viewType + ".php", function(response, status) {
            if (status === 'success' && viewType === 'DatasetAktivitas') DatasetAktivitas();
            if (status === 'success' && viewType === 'RekapAktivitas') {
                UpdateButtonPeriodeRekapAktivitas();
                RefreshRekapAktivitas();
            }
            if (status === 'success' && viewType === 'GrafikAktivitas') TampilkanGrafikAktivitas();
        });
    });

    // -------------------------------------------------------------
    // DATASET
    // -------------------------------------------------------------
    // Auto Focus ModalFilterAktivitas
    $('#ModalFilterAktivitas').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });
    // Ketika Keyword By Diubah
    $('#keyword_by').change(function(){
        var keyword_by =$('#keyword_by').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Aktivitas/FormFilter.php',
            data        : {keyword_by: keyword_by},
            success     : function(data){
                $('#FormFilterKeyword').html(data);
            }
        });
    });

    $('#ProsesFilter').on('submit', function(event) {
        event.preventDefault();

        // Default Page Ke 1
        $('#page').val(1);

        // Panggil Function DatasetAktivitas
        DatasetAktivitas();

        // Tutup Modal Filter
        $('#ModalFilterAktivitas').modal('hide');
    });

    // Routing Periode Data
    $('#periode_data').change(function(){
        var periode_data =$('#periode_data').val();
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Aktivitas/FormFilterPeriode.php',
            data        : {periode_data: periode_data},
            success     : function(data){
                $('#FormFilterPeriode').html(data);
            }
        });
    });

    // -------------------------------------------------------------
    // REKAP AKTIVITAS
    // -------------------------------------------------------------

    let requestRincianRekap = null;
    $('#ModalRincianRekapAktivitas').on('show.bs.modal', function(event) {
        if (requestRincianRekap) requestRincianRekap.abort();
        let item = $(event.relatedTarget);
        let target = $('#BodyTableRincianRekapAktivitas');
        let exportButton = $('#ButtonExportRincianRekapAktivitas');
        exportButton.prop('disabled', true);
        target.html('<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>');
        let periode = periodeRekapAktif || $('#FilterPeriodeRekapAktivitas').serialize();
        // Simpan snapshot filter modal agar export menggunakan item dan periode yang sama.
        let parameters = new URLSearchParams(periode);
        parameters.set('tipe', item.attr('data-tipe') || '');
        parameters.set('item_value', item.attr('data-value') || '');
        let exportParameters = $('#ParameterExportRincianRekapAktivitas').empty();
        parameters.forEach(function(value, name) {
            exportParameters.append($('<input>').attr({type: 'hidden', name: name}).val(value));
        });
        requestRincianRekap = $.ajax({
            type: 'POST',
            url: '_Page/Aktivitas/DataRekapAktivitas.php',
            dataType: 'json',
            data: periode + '&' + $.param({mode: 'rincian', tipe: item.attr('data-tipe'), item_value: item.attr('data-value')}),
            success: function(res) {
                if (res.status === 'success') {
                    target.html(res.html);
                    exportButton.prop('disabled', !(Number(res.total_data) > 0));
                }
                else showError(res.message || 'Gagal memuat rincian.');
            },
            error: function(xhr, status) {
                if (status !== 'abort') showError(xhr.responseJSON?.message || 'Gagal memuat rincian. Silakan coba kembali.');
            }
        });
        function showError(message) {
            exportButton.prop('disabled', true);
            target.empty().append($('<tr>').append($('<td>').attr('colspan', 5).addClass('text-center text-danger').text(message)));
        }
    }).on('hidden.bs.modal', function() {
        if (requestRincianRekap) requestRincianRekap.abort();
        $('#ButtonExportRincianRekapAktivitas').prop('disabled', true);
    });

    Object.keys(rekapForms).forEach(function(tipe) {
        $(document).on('submit', rekapForms[tipe], function(event) {
            event.preventDefault();
            $('#page_rekap_' + tipe).val(1);
            LoadDataRekapAktivitas(tipe);
        });
        $(document).on('change', '#limit_rekap_' + tipe, function() {
            $('#page_rekap_' + tipe).val(1);
            LoadDataRekapAktivitas(tipe);
        });
        $(document).on('click', '#prev_button_rekap_' + tipe + ', #next_button_rekap_' + tipe, function() {
            if ($(this).prop('disabled')) return;
            let page = parseInt($('#page_rekap_' + tipe).val(), 10) || 1;
            $('#page_rekap_' + tipe).val(Math.max(1, page + (this.id.startsWith('next_') ? 1 : -1)));
            LoadDataRekapAktivitas(tipe);
        });
    });

    $('#periode_data_rekap_aktivitas').on('change', function() {
        let target = $('#FormFilterPeriodeRekapAktivitas');
        let submit = $('#FilterPeriodeRekapAktivitas button[type="submit"]');
        target.text('Loading...');
        submit.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '_Page/Aktivitas/FormFilterPeriode.php',
            data: {periode_data: $(this).val()},
            dataType: 'html',
            success: function(html) {
                target.html(html);
                // Bedakan ID dari input periode pada modal export.
                target.find('[id]').each(function() {
                    let originalId = this.id;
                    this.id = originalId + '_rekap_aktivitas';
                    target.find('label').filter(function() {
                        return $(this).attr('for') === originalId;
                    }).attr('for', this.id);
                });
                submit.prop('disabled', false);
            },
            error: function() {
                target.text('Gagal memuat form periode. Silakan pilih periode kembali.');
            }
        });
    });

    $('#FilterPeriodeRekapAktivitas').on('submit', function(event) {
        event.preventDefault();
        let previousPeriode = periodeRekapAktif;
        periodeRekapAktif = $(this).serialize();
        let request = UpdateButtonPeriodeRekapAktivitas();
        if (request) {
            request.done(function() {
                $('#ModalPeriodeRekapAktivitas').modal('hide');
                RefreshRekapAktivitas();
            });
            request.fail(function() { periodeRekapAktif = previousPeriode; });
        }
    });

    // -------------------------------------------------------------
    // GRAFIK AKTIVITAS
    // -------------------------------------------------------------

    const grafikFormRequests = {};
    const grafikFormReady = {mode: true, periode: true};

    $('#FilterGrafikAktivitas').on('submit', function(event) {
        event.preventDefault();
        if (grafikFormReady.mode && grafikFormReady.periode) TampilkanGrafikAktivitas();
    });

    if ($('#ChartGrafikAktivitas').length) TampilkanGrafikAktivitas();

    function RoutingFormGrafik(kind, value) {
        if (grafikFormRequests[kind]) grafikFormRequests[kind].abort();
        const isMode = kind === 'mode';
        const target = $(isMode ? '#FormFilterModeData' : '#FormFilterPeriodeGrafik');
        const submit = $('#FilterGrafikAktivitas button[type="submit"]');
        grafikFormReady[kind] = false;
        submit.prop('disabled', true);
        target.text('Loading...');
        grafikFormRequests[kind] = $.ajax({
            type: 'POST',
            url: isMode ? '_Page/Aktivitas/FormFilterModeDataGrafik.php' : '_Page/Aktivitas/FormFilterPeriode.php',
            data: isMode ? {mode_data: value} : {periode_data: value},
            dataType: 'html',
            success: function(html) {
                target.html(html);
                if (!isMode) {
                    // Hindari ID yang sama dengan form export dan rekap.
                    target.find('[id]').each(function() {
                        const originalId = this.id;
                        this.id = originalId + '_grafik';
                        target.find('label').filter(function() {
                            return $(this).attr('for') === originalId;
                        }).attr('for', this.id);
                    });
                }
                grafikFormReady[kind] = true;
                submit.prop('disabled', !grafikFormReady.mode || !grafikFormReady.periode);
            },
            error: function(xhr, status) {
                if (status !== 'abort') target.text(xhr.responseText || 'Gagal memuat form. Silakan pilih kembali.');
            }
        });
    }

    $('#mode_data_grafik').on('change', function() {
        RoutingFormGrafik('mode', $(this).val());
    });
    $('#periode_data_grafik').on('change', function() {
        RoutingFormGrafik('periode', $(this).val());
    });

    
});
