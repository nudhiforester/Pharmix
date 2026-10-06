// ============================================================
// FUNCTION
// ============================================================

// Show Data
function filterAndLoadTable() {
    $.ajax({
        type 	    : 'POST',
        url 	    : '_Page/AkunPerkiraan/TabelAkunPerkiraan.php',
        success     : function(data){
            $('#MenampilkanTabelAkunPerkiraan').html(data);
        }
    });
}

// EVENT HANDLE
$(document).ready(function() {

    // Menampilkan Data Pertama Kali
    filterAndLoadTable();

    // ----------------------------------------------------
    // TAMBAH AKUN PERKIRAAN UTAMA
    // ----------------------------------------------------
    
    // Segarkan nomor akun induk setiap modal dibuka, termasuk setelah penambahan.
    $('#ModalTambahAkunPerkiraan').on('show.bs.modal', function () {
        $('#kode, #kode_utama_value').val('');
        $('#NotifikasiTambahAkunPerkiraanUtama').empty();
        $('#ButtonTambahAkunPerkiraanUtama').prop('disabled', true);
        $.ajax({
            type: 'POST',
            url: '_Page/AkunPerkiraan/FormKodeAkunUtama.php',
            dataType: 'JSON',
            success: function (response) {
                if (response.status === 'success') {
                    $('#kode, #kode_utama_value').val(response.kode);
                    $('#ButtonTambahAkunPerkiraanUtama').prop('disabled', false);
                } else {
                    $('#NotifikasiTambahAkunPerkiraanUtama').html('<div class="alert alert-danger"><small></small></div>');
                    $('#NotifikasiTambahAkunPerkiraanUtama small').text(response.message);
                }
            },
            error: function () {
                $('#NotifikasiTambahAkunPerkiraanUtama').html('<div class="alert alert-danger"><small>Gagal menyiapkan kode akun.</small></div>');
            }
        });
    });

    // Ketika Modal 'ModalTambahAkunPerkiraan' Muncul
    $('#ModalTambahAkunPerkiraan').on('shown.bs.modal', function () {
        // Auto Focus ke Form Kode setelah modal selesai terbuka
        setTimeout(function () {
            $('#ModalTambahAkunPerkiraan #nama').trigger('focus');
        }, 150);
    });

    //Tambah Akun Perkiraan Utama
    $('#ProsesTambahAkunPerkiraanUtama').submit(function(){
        // Get Data Form
        var form = $('#ProsesTambahAkunPerkiraanUtama')[0];
        var data = new FormData(form);

        // Button Element
        var ButtonTambahAkunPerkiraanUtama = $('#ButtonTambahAkunPerkiraanUtama').html();

        // Disable Button
        $(ButtonTambahAkunPerkiraanUtama).prop('disabled', true);

        // Loading Button
        $(ButtonTambahAkunPerkiraanUtama).html("Loading...");

        // Send to AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AkunPerkiraan/ProsesTambahAkunPerkiraanUtama.php',
            data 	    :  data,
            cache       : false,
            processData : false,
            contentType : false,
            enctype     : 'multipart/form-data',
            dataType    : 'JSON',
            success     : function(response){

                // Get Variabel From Response
                var status = response.status;
                var message = response.message;

                // Success
                if(status=='success'){
                    $('#ModalTambahAkunPerkiraan').modal('toggle');
                    $("#ProsesTambahAkunPerkiraanUtama")[0].reset();
                    
                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data Akun Perkiraan Berhasil Ditambahkan.'
                    );
                    filterAndLoadTable();
                }else{
                    $('#NotifikasiTambahAkunPerkiraanUtama').html('<div class="alert alert-danger text-center><small>'+message+'</small></div>');
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
                $('#NotifikasiTambahAkunPerkiraanUtama').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#ButtonTambahAkunPerkiraanUtama').prop('enable', true);
                $('#ButtonTambahAkunPerkiraanUtama').html(ButtonTambahAkunPerkiraanUtama);
            }
        });
    });

    // ----------------------------------------------------
    // TAMBAH AKUN PERKIRAAN ANAK
    // ----------------------------------------------------
    
    //Tambah Akun Perkiraan Untuk Anak
    // Fokus setelah animasi modal selesai jika form sudah tersedia.
    $('#ModalTambahAkunPerkiraanAnak').on('shown.bs.modal', function () {
        $(this).data('modalSiap', true);
        $(this).find('#nama_anak').trigger('focus');
    }).on('hide.bs.modal', function () {
        $(this).data('modalSiap', false);
    });

    $('#ModalTambahAkunPerkiraanAnak').on('show.bs.modal', function (e) {

        // Tandai modal sedang membuka untuk menghindari fokus saat transisi.
        var modal = $(this);
        modal.data('modalSiap', false);

        // Tangkap 'id_perkiraan'
        var id_perkiraan = $(e.relatedTarget).data('id');

        // Kosongkan Notifikasi Kesalahan
        $('#NotifikasiTambahAkunPerkiraanAnak').html('');

        // Disable Button
        $('#ButtonTambahAkunPerkiraanAnak').prop('disabled', true);

        // Loading Form
        $('#FormTambahAkunPerkiraanAnak').html("Loading...");

        // Tampilkan Form Dengan AJAX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AkunPerkiraan/FormTambahAkunPerkiraanAnak.php',
            data        : {id_perkiraan: id_perkiraan},
            dataType    : 'JSON',
            success     : function(response){

                // Status & message
                var status = response.status;
                var message = response.message;
                var html = response.html;

                // Success
                if(status=='success'){

                    // Tampilkan Form
                    $('#FormTambahAkunPerkiraanAnak').html(html);

                    // Enable Button
                    $('#ButtonTambahAkunPerkiraanAnak').prop('disabled', false);

                    // Jika modal sudah terbuka, fokus segera setelah form dimuat.
                    if (modal.data('modalSiap')) {
                        modal.find('#nama_anak').trigger('focus');
                    }
                }else{
                    // Kosongkan Form
                    $('#FormTambahAkunPerkiraanAnak').html('');

                    // Tampilkan Notifikasi Kesalahan
                    $('#NotifikasiTambahAkunPerkiraanAnak').html('<div class="alert alert-danger text-center"><small></small></div>');
                    $('#NotifikasiTambahAkunPerkiraanAnak small').text(message);
                }
                
            },

            // Jika Response Bukan JSON Valid
            error: function(xhr, status, error){
                // Consol
                console.log("XHR:", xhr);
                console.log("STATUS:", status);
                console.log("ERROR:", error);
                console.log("RESPONSE:", xhr.responseText);

                // Kosongkan Form
                $('#FormTambahAkunPerkiraanAnak').html('');

                // Tampilkan Notifikasi
                $('#NotifikasiTambahAkunPerkiraanAnak').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            }
        });
    });

    //Tambah Akun Perkiraan Anak
    $('#ProsesTambahAkunPerkiraanAnak').submit(function(){

        // Get Data Form
        var form = $('#ProsesTambahAkunPerkiraanAnak')[0];
        var data = new FormData(form);

        // Disable And Loading Button
        var ButtonTambahAkunPerkiraanAnak = $('#ButtonTambahAkunPerkiraanAnak').html();
        $(ButtonTambahAkunPerkiraanAnak).prop('disabled', true);
        $(ButtonTambahAkunPerkiraanAnak).html("Loading...");

        // Kirim Data Melalui AjaX
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AkunPerkiraan/ProsesTambahAkunPerkiraanAnak.php',
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

                // Success
                if(status=='success'){
                    $('#ModalTambahAkunPerkiraanAnak').modal('toggle');
                    $("#ProsesTambahAkunPerkiraanAnak")[0].reset();
                    $('#NotifikasiTambahAkunPerkiraanAnak').html("");
                    
                    //Tampilkan Toast
                    showToast(
                        'success',
                        'Berhasil',
                        'Data Akun Perkiraan Berhasil Ditambahkan.'
                    );
                    filterAndLoadTable();
                }else{
                    $('#NotifikasiTambahAkunPerkiraanAnak').html('<div class="alert alert-danger text-center"><small>'+message+'</small></div>');
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
                $('#NotifikasiTambahAkunPerkiraanAnak').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#ButtonTambahAkunPerkiraanAnak').prop('enable', true);
                $('#ButtonTambahAkunPerkiraanAnak').html(ButtonTambahAkunPerkiraanAnak);
            }
        });
    });
    
    // ------------------------------------------------------------
    // DETAIL AKUN
    // ------------------------------------------------------------
    $('#ModalDetailAkunPerkiraan').on('show.bs.modal', function (e) {
        var id_perkiraan = $(e.relatedTarget).data('id');
        $('#FormDetailAkunPerkiraan').html("Loading...");
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AkunPerkiraan/FormDetailAkunPerkiraan.php',
            data        : {id_perkiraan: id_perkiraan},
            success     : function(data){
                $('#FormDetailAkunPerkiraan').html(data);
            }
        });
    });

    // ------------------------------------------------------------
    // EDIT AKUN: muat form dan submit melalui respons JSON.
    // ------------------------------------------------------------
    function tampilkanErrorEdit(message) {
        $('#NotifikasiEditAkun').html('<div class="alert alert-danger text-center"><small></small></div>');
        $('#NotifikasiEditAkun small').text(message);
    }

    $('#ModalEditAkun').on('show.bs.modal', function (e) {
        // Bersihkan form lama dan cegah submit selama form belum tersedia.
        var idPerkiraan = $(e.relatedTarget).data('id');
        $('#NotifikasiEditAkun').empty();
        $('#ButtonEditAkun').prop('disabled', true);
        $('#FormEditAkun').html('<small>Loading...</small>');
        $.ajax({
            type: 'POST',
            url: '_Page/AkunPerkiraan/FormEditAkun.php',
            data: {id_perkiraan: idPerkiraan},
            dataType: 'JSON',
            success: function (response) {
                // Aktifkan tombol hanya jika form berhasil dimuat.
                if (response.status === 'success') {
                    $('#FormEditAkun').html(response.html);
                    $('#ButtonEditAkun').prop('disabled', false);
                } else {
                    $('#FormEditAkun').empty();
                    tampilkanErrorEdit(response.message);
                }
            },
            error: function () {
                $('#FormEditAkun').empty();
                tampilkanErrorEdit('Terjadi kesalahan server.');
            }
        });
    });

    $('#ProsesEditAkun').on('submit', function (e) {
        e.preventDefault();
        // Cegah submit berulang dan simpan isi tombol untuk pemulihan.
        var button = $('#ButtonEditAkun');
        if (button.prop('disabled')) { return; }
        var buttonHtml = button.html();
        button.prop('disabled', true).html('Loading...');
        $('#NotifikasiEditAkun').empty();
        $.ajax({
            type: 'POST',
            url: '_Page/AkunPerkiraan/ProsesEditAkun.php',
            data: new FormData(this),
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: function (response) {
                // Tutup modal dan segarkan tabel setelah penyimpanan berhasil.
                if (response.status === 'success') {
                    $('#ModalEditAkun').modal('hide');
                    $('#ProsesEditAkun')[0].reset();
                    $('#NotifikasiEditAkun').empty();
                    showToast('success', 'Berhasil', response.message);
                    filterAndLoadTable();
                } else {
                    tampilkanErrorEdit(response.message);
                }
            },
            error: function () {
                tampilkanErrorEdit('Terjadi kesalahan server.');
            },
            complete: function () {
                // Pulihkan tombol setelah respons sukses maupun gagal.
                button.prop('disabled', false).html(buttonHtml);
            }
        });
    });

    // ------------------------------------------------------------
    // HAPUS AKUN
    // ------------------------------------------------------------
    
    //Hapus Akun Perkiraan
    $('#ModalHapusAkun').on('show.bs.modal', function (e) {

        // Get id_perkiraan form button
        var id_perkiraan = $(e.relatedTarget).data('id');

        // Get Button Element
        var ButtonHapusAkun = $('#ButtonHapusAkun').html();

        // Disable Button
        $(ButtonHapusAkun).prop('disabled', true);

        // Loading Form
        $('#FormHapusAkun').html("Loading...");

        // Get Detail Data
        $.ajax({
            type 	    : 'POST',
            url 	    : '_Page/AkunPerkiraan/FormHapusAkun.php',
            data        : {id_perkiraan: id_perkiraan},
            dataType    : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;
                var html    = response.html;

                // Success
                if(status=='success'){
                    $('#FormHapusAkun').html(html);
                    $('#NotifikasiHapusAkun').html("");
                    $('#ButtonHapusAkun').prop('disabled', false);
                }else{
                    $('#ButtonHapusAkun').prop('disabled', true);
                    $('#FormHapusAkun').html("");
                    $('#NotifikasiHapusAkun').html('<div class="alert alert-danger"><small>' + message + '</small></div>');
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
                $('#NotifikasiHapusAkun').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#ButtonHapusAkun').html(ButtonHapusAkun);
            }
        });

    });

    //Tambah Akun Perkiraan Anak
    $('#ProsesHapusAkun').submit(function(){

        // Get Data Form
        var form = $('#ProsesHapusAkun')[0];
        var data = new FormData(form);

        // Get Button Element
        var ButtonHapusAkun = $('#ButtonHapusAkun').html();

        // Disable Button
        $(ButtonHapusAkun).prop('disabled', true);

        // Loading Button
        $(ButtonHapusAkun).html("Loading...");

        // Send Data To AJAX
        $.ajax({
            type       : 'POST',
            url        : '_Page/AkunPerkiraan/ProsesHapusAkun.php',
            data       : data,
            cache      : false,
            processData: false,
            contentType: false,
            enctype    : 'multipart/form-data',
            dataType   : 'JSON',
            success     : function(response){

                // Status & Message
                var status  = response.status;
                var message = response.message;

                // Success
                if(status=='success'){
                    $('#ModalHapusAkun').modal('toggle');
                    $("#ProsesHapusAkun")[0].reset();
                    $('#NotifikasiHapusAkun').html("");
                    showToast(
                        'success',
                        'Berhasil',
                        'Data Akun Perkiraan Berhasil Dihapus.'
                    );
                    filterAndLoadTable();
                }else{
                    $('#NotifikasiHapusAkun').html('<div class="alert alert-danger"><small>' + message + '</small></div>');
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
                $('#NotifikasiTambahAkunPerkiraanUtama').html(`<div class="alert alert-danger"><small>Terjadi kesalahan server.</small></div>`);
            },
            complete: function(){
                // Kembalikan Tombol
                $('#ButtonHapusAkun').prop('enable', true);
                $('#ButtonHapusAkun').html(ButtonHapusAkun);
            }
        });
    });

    // ------------------------------------------------------------
    // PINDAH POSISI
    // ------------------------------------------------------------
    $('#ModalPindahPosisi').on('show.bs.modal', function (e) {
        // Isi konfirmasi dari tombol opsi tanpa menyisipkan nama akun sebagai HTML.
        var trigger = $(e.relatedTarget);
        $('#id_pindah_posisi').val(trigger.data('id'));
        $('#arah_pindah_posisi').val(trigger.data('arah'));
        $('#KonfirmasiPindahPosisi').text('Pindahkan akun ' + trigger.data('akun') + ' ke ' + trigger.data('arah') + '?');
        $('#NotifikasiPindahPosisi').empty();
        $('#ButtonPindahPosisi').prop('disabled', false);
    });

    $('#ProsesPindahPosisi').on('submit', function (e) {
        e.preventDefault();
        var button = $('#ButtonPindahPosisi');
        if (button.prop('disabled')) { return; }
        var label = button.html();
        button.prop('disabled', true).text('Loading...');
        $('#NotifikasiPindahPosisi').empty();
        function tampilkanError(message) {
            $('#NotifikasiPindahPosisi').html('<div class="alert alert-danger"><small></small></div>');
            $('#NotifikasiPindahPosisi small').text(message);
        }
        // Kirim arah dan ID; server menentukan tetangga berdasarkan posisi terkini.
        $.ajax({
            type       : 'POST',
            url        : '_Page/AkunPerkiraan/ProsesPindahPosisi.php',
            data       : new FormData(this),
            processData: false,
            contentType: false,
            dataType   : 'JSON',
            success    : function (response) {
                if (response.status === 'success') {
                    $('#ModalPindahPosisi').modal('hide');
                    showToast('success', 'Berhasil', response.message);
                    filterAndLoadTable();
                } else { tampilkanError(response.message); }
            },
            error: function () { tampilkanError('Terjadi kesalahan server.'); },
            complete: function () { button.prop('disabled', false).html(label); }
        });
    });

});





