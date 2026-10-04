// ============================================================
// FUNCTION
// Keterangan : Semua function di letakan pada block ini
// ============================================================

//Fungsi Menampilkan Data
function ShowData() {
    const target = $('#tabel_supplier');
    const data   = $('#ProsesFilter').serialize();

    $.ajax({
        type    : 'POST',
        url     : '_Page/Supplier/TabelSupplier.php',
        data    : data,
        dataType: 'json',

        beforeSend: function() {
            tableLoading('#TableSupplier', true);
        },

        success: function(res) {
            if (res.status === 'success') {
                target.html(res.html);

                // Gunakan ID table, bukan ID tbody
                initResponsiveTable('#TableSupplier');

                // Update informasi halaman
                $('#page_info').text(
                    'Page ' + res.page + ' Of ' + res.total_page
                );

                // Pengaturan tombol pagination
                $('#prev_button').prop('disabled', res.page <= 1);
                $('#next_button').prop(
                    'disabled',
                    res.total_page <= 0 || res.page >= res.total_page
                );

                return;
            }

            target.html(res.html);

            $('#prev_button, #next_button').prop('disabled', true);
        },

        error: function(xhr) {
            target.html(`
                <tr class="table-empty">
                    <td colspan="8" class="text-center text-danger">
                        <small>Gagal memuat data supplier.</small>
                    </td>
                </tr>
            `);

            $('#prev_button, #next_button').prop('disabled', true);

            console.error(xhr.responseText);
        },

        complete: function() {
            tableLoading('#TableSupplier', false);
        }
    });
}

//Fungsi Menampilkan Detail Supplier
function ShowDetail(id_supplier) {
    //Loading element
    $('#detail_view').html('<div class="row"><div class="col-md-12 text-center">Loading...</div></div>');
    $.ajax({
        type        : 'POST',
        url         : '_Page/Supplier/_DetailSupplier.php',
        data        : {id_supplier: id_supplier},
        success: function(response) {
            $('#detail_view').html(response);

            // Muat riwayat setelah tabel pada detail supplier tersedia
            $('#id_supplier_transaksi').val(id_supplier);
            $('#page_transaksi').val(1);
            ShowRiwayatTransaksi();
        }
    });
}

//Fungsi Menampilkan Riwayat Transaksi
function ShowRiwayatTransaksi() {

    const target = $('#tabel_transaksi_supplier');
    const data   = $('#ProsesFilterTransaksi').serialize();

    // Detail supplier harus tersedia sebelum riwayat dimuat
    if (!target.length || !$('#id_supplier_transaksi').val()) {
        return;
    }

    $.ajax({
        type    : 'POST',
        url     : '_Page/Supplier/TabelRiwayatTransaksi.php',
        data    : data,
        dataType: 'json',

        beforeSend: function() {
            tableLoading('#TableRiwayatTransaksi', true);
            $('#prev_button_transaksi, #next_button_transaksi').prop('disabled', true);
        },

        success: function(res) {
            if (res.status === 'success') {
                target.html(res.html);

                // Gunakan ID table, bukan ID tbody
                initResponsiveTable('#TableRiwayatTransaksi');

                // Update informasi halaman
                $('#page_transaksi').val(res.page);
                $('#page_info_transaksi').text(
                    'Page ' + res.page + ' Of ' + res.total_page
                );

                // Pengaturan tombol pagination
                $('#prev_button_transaksi').prop('disabled', res.page <= 1);
                $('#next_button_transaksi').prop(
                    'disabled',
                    res.total_page <= 0 || res.page >= res.total_page
                );

                return;
            }

            target.html(res.html);

            $('#page_info_transaksi').text('Page 0 Of 0');
            $('#prev_button_transaksi, #next_button_transaksi').prop('disabled', true);
        },

        error: function(xhr) {
            target.html(`
                <tr class="table-empty">
                    <td colspan="9" class="text-center text-danger">
                        <small>Gagal memuat riwayat transaksi supplier.</small>
                    </td>
                </tr>
            `);

            $('#page_info_transaksi').text('Page 0 Of 0');
            $('#prev_button_transaksi, #next_button_transaksi').prop('disabled', true);

            console.error(xhr.responseText);
        },

        complete: function() {
            tableLoading('#TableRiwayatTransaksi', false);
        }
    });
}

// ============================================================
// EVENT HANDLE
// Keterangan : Semua Event Handle di tulis pada block ini
// ============================================================
$(document).ready(function() {

    // -------------------------------------------------------
    // DATA TABEL SUPPLIER
    // Keterangan : Adalah handle tampilan data table
    // -------------------------------------------------------
    
    // Switch data & detail View
    $('#data_view').show();
    $('#detail_view').hide();

    // Responsive table to  card
    initResponsiveTable('#TableSupplier');

    // Call Data Function
    ShowData();

    // Auto Focus ModalFilter
    $('#ModalFilter').on('shown.bs.modal', function () {
        $('#keyword').trigger('focus');
    });

    //Ketika Submit Filter
    $('#ProsesFilter').submit(function(){
        
        //Kembalikan ke halaman 1
        $('#page').val(1);
        
        // Reload Data
        ShowData();

        //Tutup Modal
        $('#ModalFilter').modal('hide');
    });
    
    //Pagging
    $(document).on('click', '#next_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now + 1;
        $('#page').val(next_page);
        ShowData(0);
        scrollToTop();
    });
    $(document).on('click', '#prev_button', function() {
        var page_now = parseInt($('#page').val(), 10); // Pastikan nilai diambil sebagai angka
        var next_page = page_now - 1;
        $('#page').val(next_page);
        ShowData(0);
        scrollToTop();
    });
    
    // -------------------------------------------------------
    // TAMBAH SUPPLIER
    // Keterangan : Adalah handle tambah data supplier
    // -------------------------------------------------------
    
    // Auto Focus pada saat 'ModalTambahSupplier' muncul
    $('#ModalTambahSupplier').on('shown.bs.modal', function () {
        $('#nama_supplier').trigger('focus');
    });

    //Proses submit Tambah Supplier
    $('#ProsesTambahSupplier').submit(function(){
        
        // Tangkap Data
        var ProsesTambahSupplier = $('#ProsesTambahSupplier').serialize();

        // Tombol
        var TombolTambahSupplier = $('#TombolTambahSupplier').html();

        // Loading Tombol
        $('#TombolSimpanSesi').html('...');

        // Clear Notifikasi Text
        $('#NotifikasiTambahSupplier').html("");

        // Disable tombol
        $('#TombolTambahSupplier').prop('disabled', true);

        // Insert Data Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/ProsesTambahSupplier.php',
            dataType    : 'JSON',
            data 	    :  ProsesTambahSupplier,
            success     : function(response){

                // Status & message
                let status = response.status;
                let message = response.message;

                // Jika Berhasil
                if(status=='success'){
                    //tutup modal
                    $('#ModalTambahSupplier').modal('hide');

                    //Reset halaman
                    $('#page').val(1);

                    //Reset Form
                    $('#ProsesTambahSupplier')[0].reset();

                    //Tampilkan Data
                    ShowData();

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );
                }else{
                    
                    // Jika gagal tampilkan notifikasi text
                    $('#NotifikasiTambahSupplier').html('<div class="alert alert-danger"><small>'+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiTambahSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },

            complete: function(){
                // Kembalikan Tombol
                $('#TombolTambahSupplier').prop('disabled', false);
                $('#TombolTambahSupplier').html(TombolSimpanSesi);
            }
        });
    });

    // -------------------------------------------------------
    // EXPORT SUPPLIER
    // Keterangan : Block handle export data supplier ke file excel
    // -------------------------------------------------------

    //Modal Export Supplier
    $('#ModalExportSupplier').on('show.bs.modal', function (e) {
        $('#FormExportSupplier').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormExportSupplier.php',
            success     : function(data){
                $('#FormExportSupplier').html(data);
            }
        });
    });

    // -------------------------------------------------------
    // IMPORT SUPPLIER
    // Keterangan : Block Untuk Handle Import Data
    // -------------------------------------------------------
    
    // Ketika 'ModalImportSupplier' Muncul
    $('#ModalImportSupplier').on('show.bs.modal', function (e) {
        //Kosongkan Notifikasi
        $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>No Data</small></td></tr>');

        //Disabled Button
        $('#TombolImport').prop('disabled', true);

        // Reset Form
        $('#ProsesImportSupplier')[0].reset();
    });

    //Validasi File Import
    $('#file_supplier').on('change', function () {
        var file = this.files[0];
        var validTypes = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'];
        var maxSize = 10 * 1024 * 1024; // 10 MB

        // Reset notifikasi
        $('#NotifikasiImportSupplier').html('');

        if (file) {
            if (!validTypes.includes(file.type)) {
                $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Tipe File Tidak Valid</small></td></tr>');
                $(this).val(''); // Reset input file
                return;
            }

            if (file.size > maxSize) {
                $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Ukuran file terlalu besar. Maksimal 10 MB.</small></td></tr>');
                $(this).val(''); // Reset input file
                return;
            }
            $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-success">Siap Import</small></td></tr>');
            $('#TombolImport').prop('disabled', false);
        }
    });

    //Proses Import
    $('#ProsesImportSupplier').on('submit', function (e) {
        e.preventDefault();

        // Tangkap Data
        var formData = new FormData(this);

        // Loading Notifikasi 'NotifikasiImportSupplier'
        $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>Loading...</small></td></tr>');

        // Disabled 'TombolImport' dan 'TombolSelesai'
        $('#TombolImport').prop('disabled', true);
        $('#TombolSelesai').prop('disabled', true);

        // Proses Data Dengan 'AJAX'
        $.ajax({
            url        : '_Page/Supplier/ProsesImportSupplier.php',
            type       : 'POST',
            data       : formData,
            dataType   : 'JSON',
            contentType: false,
            processData: false,
            beforeSend : function () {
                $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small>Sedang Memproses Data</small></td></tr>');
            },

            success: function (response) {
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Apabila Berhasil
                if(status=="success"){
                    // Tampilkan Data
                    $('#NotifikasiImportSupplier').html(html);

                    // Enable Tombol Selesai
                    $('#TombolSelesai').prop('disabled', false);
                }else{
                    $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">'+message+'</small></td></tr>');

                    // Enamble Tombol
                    $('#TombolImport').prop('disabled', false);
                }
            },

            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiImportSupplier').html('<tr><td colspan="7" class="text-center"><small class="text-danger">Terjadi kesalahan saat mengimpor data.</small></td></tr>');
                
                // Enamble Tombol
                $('#TombolImport').prop('disabled', false);
            }
        });
    });

    // Tombol Selesai
    $('#TombolSelesai').on('click', function () {
        //Reset Filter
        $('#ProsesFilter')[0].reset();
        $('#ProsesImportSupplier')[0].reset();

        //Tampilkan Data
        ShowData();

        // Tutup Modal
        $('#ModalImportSupplier').modal('hide');

        // Enable Tombol TombolImport dan TombolSelesai
        $('#TombolImport').prop('disabled', true);
        $('#TombolSelesai').prop('disabled', true);
    });
    
    // -------------------------------------------------------
    // DETAIL SUPPLIER
    // Keterangan : Menampilkan informasi supplier dalam bentuk modal
    // -------------------------------------------------------
    
    //Menampilkan 'FormDetailSupplier' pada 'ModalDetailSupplier'
    $('#ModalDetailSupplier').on('show.bs.modal', function (e) {
        var id_supplier= $(e.relatedTarget).data('id');
        $('#FormDetailSupplier').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormDetailSupplier.php',
            data        : {id_supplier: id_supplier},
            success     : function(data){
                $('#FormDetailSupplier').html(data);
            }
        });
    });

    // Ketika Submit Detail Supplier
    $('#ProsesDetail').submit(function(e){
        e.preventDefault();

        // Menangkap 'id_supplier' dari 'FormDetailSupplier'
        const id_supplier = $('#FormDetailSupplier').find('input[name="id_supplier"]').val();

        // Jika id_supplier tidak ditemukan
        if (!id_supplier) {
            $('#NotifikasiDetailSupplier').html(`
                <div class="alert alert-danger">
                    <small>
                        <b>Opss!</b><br>
                        ID Supplier Tidak Ditemukan!
                    </small>
                </div>
            `);
            return;
        }

        // Switch Data & Detail View
        $('#data_view').hide();
        $('#detail_view').show();

        // Tutup Modal
        $('#ModalDetailSupplier').modal('hide');

        // Tampilkan Detail Dengan Function
        ShowDetail(id_supplier);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        
    });

    $(document).on('click', '.tombol_kembali', function () {
        // Menyembunyikan detail_view dan Menampilkan data_view
        $('#data_view').show();
        $('#detail_view').hide();

        // Reload Data
        ShowData(0);

        // Scroll ke atas
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // -------------------------------------------------------
    // EDIT SUPPLIER
    // Keterangan : Handle Form Edit dan Submit Edit Supplier
    // -------------------------------------------------------

    //Modal Edit Supplier
    $('#ModalEditSupplier').on('show.bs.modal', function (e) {

        // Tangkap 'id_supplier'
        var id_supplier = $(e.relatedTarget).data('id');

        // Loading Form 'FormEditSupplier'
        $('#FormEditSupplier').html("Loading...");

        // Kosongkan Notifikasi 'NotifikasiEditSupplier'
        $('#NotifikasiEditSupplier').html("");

        // Disable Button
        $('#TombolEditSupplier').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormEditSupplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    // Tampilkan Form
                    $('#FormEditSupplier').html(html);

                    // Enable Tombol
                    $('#TombolEditSupplier').prop('disabled', false);
                }else{
                    $('#NotifikasiEditSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiEditSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    //Proses Edit Supplier
    $('#ProsesEditSupplier').submit(function(e){
        e.preventDefault();

        // Menangkap Data
        var form = $('#ProsesEditSupplier')[0];
        var data = new FormData(form);

        // Tangkap Elemnt Tombol
        var TombolEditSupplier = $('#TombolEditSupplier').html();

        // Disable Tombol 'TombolEditSupplier'
        $('#TombolEditSupplier').prop('disabled', true);

        // Loading Tombol 'TombolEditSupplier'
        $('#TombolEditSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');

        // Kosongkan Notifikasi  'NotifikasiEditSupplier'
        $('#NotifikasiEditSupplier').html('');
        
        $.ajax({
            type       : 'POST',
            url        : '_Page/Supplier/ProsesEditSupplier.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            enctype    : 'multipart/form-data',
            success    : function(response){

                // Message & Status
                var status  = response.status;
                var message = response.message;

                // Apabila Berhasil
                if(status=='success'){

                    //Tutup Modal
                    $('#ModalEditSupplier').modal('hide');
                    
                    // Reload Data
                    ShowData(0);

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil disimpan.'
                    );

                    //Jika Posisi Sedang Dalam Detail Supplier
                    if ($("#put_id_supplier_on_detail").length) {
                        var id_supplier=$("#put_id_supplier_on_detail").val();
                        ShowDetailSupplier(id_supplier);
                    }

                }else{
                    $('#NotifikasiEditSupplier').html('<div class="alert alert-danger"><small>' + message + '</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiEditSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#TombolEditSupplier').prop('disabled', false);
                $('#TombolEditSupplier').html(TombolEditSupplier);
            }
        });
    });

    // -------------------------------------------------------
    // HAPUS SUPPLIER
    // Keterangan : Handle Form Hapus dan Submit Hapus Supplier
    // -------------------------------------------------------

    //Modal Hapus Supplier
    $('#ModalHapusSupplier').on('show.bs.modal', function (e) {

        // Tangkap 'id_supplier'
        var id_supplier = $(e.relatedTarget).data('id');

        // Loading Form 'FormHapusSupplier'
        $('#FormHapusSupplier').html("Loading...");

        // Kosongkan Notifikasi 'NotifikasiHapusSupplier'
        $('#NotifikasiHapusSupplier').html("");

        // Disable Button
        $('#TombolHapusSupplier').prop('disabled', true);

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/FormHapusSupplier.php',
            data        : {id_supplier: id_supplier},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    // Tampilkan Form
                    $('#FormHapusSupplier').html(html);

                    // Enable Tombol
                    $('#TombolHapusSupplier').prop('disabled', false);
                }else{
                    $('#NotifikasiHapusSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiHapusSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            }
        });
    });

    //Proses Hapus Supplier
    $('#ProsesHapusSupplier').submit(function(e){
        e.preventDefault();

        // Tangkap data
        var form = $('#ProsesHapusSupplier')[0];
        var data = new FormData(form);

        // Element Tombol
        var TombolHapusSupplier = $('#TombolHapusSupplier').html();

        // Disable Button 'TombolHapusSupplier'
        $('#TombolHapusSupplier').prop('disabled', true);

        // Loading Button 'TombolHapusSupplier'
        $('#TombolHapusSupplier').html('<div class="spinner-border text-secondary" role="status"><span class="sr-only"></span></div>');

        // Bersihkan Notifikasi
        $('#NotifikasiHapusSupplier').html('');
       
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Supplier/ProsesHapusSupplier.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            dataType    : 'JSON',
            success     : function(response){
                
                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Jika Berhasil
                if(status=='success'){

                    //Tutup Modal
                    $('#ModalHapusSupplier').modal('hide');
                    
                    // Reload Data
                    ShowData(0);

                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data berhasil dihapus.'
                    );

                }else{
                    $('#NotifikasiHapusSupplier').html('<div class="alert alert-danger text-center"><small><b>Opss!</b> '+message+'</small></div>');
                }
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Tampilkan Notifikasi
                $('#NotifikasiHapusSupplier').html(`<div class="alert alert-danger">Terjadi kesalahan server.</div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#TombolHapusSupplier').prop('disabled', false);
                $('#TombolHapusSupplier').html(TombolHapusSupplier);
            }
        });
    });


    // -------------------------------------------------------
    // RIWAYAT TRANSAKSI SUPPLIER
    // Keterangan : Filter dan pagination tabel pada detail supplier
    // -------------------------------------------------------

    // Kembalikan halaman pertama ketika filter diterapkan
    $('#ProsesFilterTransaksi').submit(function(e) {
        e.preventDefault();
        $('#page_transaksi').val(1);
        ShowRiwayatTransaksi();
        $('#ModalFilterTransaksi').modal('hide');
    });

    // Gunakan event delegasi karena tombol dimuat melalui ShowDetail
    $(document).on('click', '#next_button_transaksi', function() {
        var page_now = parseInt($('#page_transaksi').val(), 10) || 1;
        $('#page_transaksi').val(page_now + 1);
        ShowRiwayatTransaksi();
    });
    $(document).on('click', '#prev_button_transaksi', function() {
        var page_now = parseInt($('#page_transaksi').val(), 10) || 1;
        $('#page_transaksi').val(Math.max(1, page_now - 1));
        ShowRiwayatTransaksi();
    });

    // Sesuaikan input kata kunci dengan dasar pencarian yang dipilih
    $('#keyword_by_riwayat_transaksi').on('change', function() {
        var keyword_by = $(this).val();
        $.ajax({
            type    : 'POST',
            url     : '_Page/Supplier/FormFilterKeywordRiwayatTransaksi.php',
            data    : {keyword_by_riwayat_transaksi: keyword_by},
            success: function(response) {
                // Abaikan respons lama jika pilihan sudah berubah
                if ($('#keyword_by_riwayat_transaksi').val() !== keyword_by) {
                    return;
                }
                $('#FormFilterKeywordRiwayatTransaksi').html(
                    '<label for="keyword_riwayat_transaksi"><i>Keyword</i></label>' + response
                );
            }
        });
    });

    //Modal Detail Transaksi
    $('#ModalDetailTransaksi').on('show.bs.modal', function (e) {
        //Tangkap id_transaksi_jual_beli dari modal detail
        var id_transaksi_jual_beli = $(e.relatedTarget).data('id');
        
        //Buka Detail Barang
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/Pembelian/detail_pembelian.php',
            data        : {id_transaksi_jual_beli: id_transaksi_jual_beli},
            dataType    : "json",
            success     : function(response){
                if(response.status=="Success"){

                    var data = response.dataset;
                    var list_rincian = response.list_rincian;
                    
                    //Tempelkan Ke Element
                    $('#FormDetailTransaksi').html(`
                        <input type="hidden" name="id" value="${id_transaksi_jual_beli}">
                        <div class="row mb-2">
                            <div class="col-4"><small>Tanggal</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.tanggal}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Supplier</small></div>
                            <div class="col-8">
                                <a href="javascriipt:void(0);" data-bs-toggle="modal" data-bs-target="#ModalListSupplierEdit" data-id="${id_transaksi_jual_beli}" data-mode="List">
                                    <small class="text text-grayish">${data.nama_supplier}</small>
                                </a>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Kategori</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.kategori}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Subtotal</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.subtotal_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>PPN</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.ppn_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Diskon</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.diskon_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Total</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.total_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Cash</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.cash_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Kembalian</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.kembalian_rp}</small>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-4"><small>Status</small></div>
                            <div class="col-8">
                                <small class="text text-grayish">${data.status}</small>
                            </div>
                        </div>
                    `);
                    var rincianList = response.list_rincian;
                    var html = "";

                    // Inisialisasi total
                    var totalPpn = 0;
                    var totalDiskon = 0;
                    var totalSubtotal = 0;

                    if (rincianList.length > 0) {
                        $.each(rincianList, function(index, item) {
                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.nama_barang}</td>
                                    <td>${item.qty}</td>
                                    <td class="text-end">${item.harga_rp}</td>
                                    <td class="text-end">${item.ppn_rp}</td>
                                    <td class="text-end">${item.diskon_rp}</td>
                                    <td class="text-end">${item.subtotal_rp}</td>
                                </tr>
                            `;

                            // Hitung total
                            totalPpn += parseFloat(item.ppn);
                            totalDiskon += parseFloat(item.diskon);
                            totalSubtotal += parseFloat(item.subtotal);
                        });

                        // Tambahkan baris total di akhir tabel
                        html += `
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-center">Total</td>
                                <td class="text-end">Rp ${totalPpn.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalDiskon.toLocaleString("id-ID")}</td>
                                <td class="text-end">Rp ${totalSubtotal.toLocaleString("id-ID")}</td>
                            </tr>
                        `;
                    } else {
                        html = '<tr><td colspan="7" class="text-center">Tidak ada rincian transaksi</td></tr>';
                    }

                    // Masukkan ke dalam tabel
                    $("#ListRincianTransaksi").html(html);

                    //Enable tombol
                    $('#ButtonSelengkapnyaTransaksi').prop("disabled", false);
                }else{
                    //Tempelkan Notifikasi
                    $('#FormDetailTransaksi').html(
                        `<div class="alert alert-danger" role="alert">${response.message}</div>`
                    );
                    //Disable tombol
                    $('#ButtonSelengkapnyaTransaksi').prop("disabled", true);
                }
            },
            error: function () {
                //Tempelkan Notifikasi
                $('#FormDetailTransaksi').html(
                    '<div class="alert alert-danger" role="alert">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>'
                );
                //Disable tombol
                $('#ButtonSelengkapnyaTransaksi').prop("disabled", true);
            },
        });
    });

    //Modal Export Transaksi
    $('#ModalExportTransaksi').on('show.bs.modal', function (e) {
        // Tangkap ID dari tombol atau supplier yang sedang ditampilkan
        var id_supplier = $(e.relatedTarget).data('id') || $('#id_supplier_transaksi').val();
        var target = $('#FormExportTransaksi');
        var tombol = $(this).find('button[type="submit"]');

        // Kosongkan form sebelumnya dan nonaktifkan export selama pemuatan
        target.html('<div class="text-center"><small>Loading...</small></div>');
        tombol.prop('disabled', true);

        // Pastikan supplier sudah dipilih sebelum meminta form export
        if (!id_supplier) {
            target.html('<div class="text-center text-danger"><small>ID Supplier Tidak Boleh Kosong</small></div>');
            return;
        }

        // Muat HTML form export sesuai supplier yang sedang ditampilkan
        $.ajax({
            type        : 'POST',
            url         : '_Page/Supplier/FormExportTransaksi.php',
            data        : {id_supplier: id_supplier},
            dataType    : 'html',
            success: function(response) {
                target.html(response);
                tombol.prop('disabled', !$.trim(response));
            },
            error: function(xhr) {
                target.html('<div class="text-center text-danger"><small>Gagal memuat form export transaksi.</small></div>');
                console.error(xhr.responseText);
            }
        });
    });

    
});





