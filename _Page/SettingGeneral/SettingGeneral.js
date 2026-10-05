// Pemilihan file melalui klik maupun drag and drop menggunakan input form yang sama.
document.querySelectorAll('.setting-upload-box').forEach(function(box) {
    const input = box.querySelector('input[type="file"]');
    const filename = box.querySelector('.setting-upload-filename');
    const extensions = box.dataset.extensions.split(',');
    let dragDepth = 0;

    input.addEventListener('change', function() {
        const file = input.files[0];
        if (!file) {
            filename.textContent = 'Belum ada file dipilih';
            return;
        }
        const extension = file.name.split('.').pop().toLowerCase();
        if (!extensions.includes(extension) || file.size > 2097152) {
            input.value = '';
            filename.textContent = 'Pilih format yang sesuai dengan ukuran maksimal 2 MB.';
            return;
        }
        filename.textContent = file.name;
    });
    box.addEventListener('dragenter', function(event) {
        event.preventDefault();
        dragDepth++;
        box.classList.add('is-dragging');
    });
    box.addEventListener('dragover', function(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';
    });
    box.addEventListener('dragleave', function(event) {
        event.preventDefault();
        dragDepth = Math.max(0, dragDepth - 1);
        if (dragDepth === 0) box.classList.remove('is-dragging');
    });
    box.addEventListener('drop', function(event) {
        event.preventDefault();
        dragDepth = 0;
        box.classList.remove('is-dragging');
        if (event.dataTransfer.files.length !== 1) {
            filename.textContent = 'Tarik satu file untuk setiap upload.';
            return;
        }
        input.files = event.dataTransfer.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
    });
});

// Informasi Umum
$('#ProsesSettingGeneral').submit(function(e){
    e.preventDefault();

    $('#NotifikasiSimpanSettingGeneral').html('');

    let tombol = $('#ButtonSimpanSettingGeneral');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Menyimpan...');

    $.ajax({
        url: '_Page/SettingGeneral/ProsesSettingGeneral.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

            }else{

                $('#NotifikasiSimpanSettingGeneral').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiSimpanSettingGeneral').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});

// Favicon
$('#ProsesUpdateFavicon').submit(function(e){
    e.preventDefault();

    $('#NotifikasiUpdateFavicon').html('');

    let tombol      = $('#ButtonUpdateFavicon');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Uploading...');

    let formData = new FormData(this);

    $.ajax({
        url        : '_Page/SettingGeneral/ProsesUpdateFavicon.php',
        type       : 'POST',
        data       : formData,
        processData: false,
        contentType: false,
        dataType   : 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

                $('#FaviconPreview').load(
                    location.href + ' #FaviconPreview>*'
                );

            }else{

                $('#NotifikasiUpdateFavicon').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiUpdateFavicon').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});

// Logo
$('#ProsesUpdateLogo').submit(function(e){
    e.preventDefault();

    $('#NotifikasiUpdateLogo').html('');

    let tombol      = $('#ButtonUpdateLogo');
    let tombol_asli = tombol.html();

    tombol.prop('disabled', true);
    tombol.html('<i class="bi bi-hourglass-split"></i> Uploading...');

    let formData = new FormData(this);

    $.ajax({
        url        : '_Page/SettingGeneral/ProsesUpdateLogo.php',
        type       : 'POST',
        data       : formData,
        processData: false,
        contentType: false,
        dataType   : 'json',

        success: function(response){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            if(response.success){

                Swal.fire({
                    icon             : 'success',
                    title            : 'Berhasil',
                    text             : response.message,
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });

                $('#LogoPreview').load(
                    location.href + ' #LogoPreview>*'
                );

            }else{

                $('#NotifikasiUpdateLogo').html(
                    '<div class="alert alert-danger">'+
                        response.message+
                    '</div>'
                );

            }
        },

        error: function(){

            tombol.prop('disabled', false);
            tombol.html(tombol_asli);

            $('#NotifikasiUpdateLogo').html(
                '<div class="alert alert-danger">'+
                    'Terjadi kesalahan pada server.'+
                '</div>'
            );

        }
    });
});
