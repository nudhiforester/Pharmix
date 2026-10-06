<?php
    header('Content-Type: application/json; charset=utf-8');

    function Response($status, $message, $html = ''){
        echo json_encode([
            'status' => $status,
            'message' => $message,
            'html' => $html
        ]);
        exit;
    }

    //Koneksi
    include "../../_Config/Connection.php";
    include "../../_Config/GlobalFunction.php";
    include "../../_Config/Session.php";
    date_default_timezone_set('Asia/Jakarta');
    if(empty($SessionIdAkses)){
        Response('error', 'Sesi Akses Sudah Berakhir. Silahkan Login Ulang!');
    }else{
        //Tangkap id_perkiraan
        if(empty($_POST['id_perkiraan'])){
            Response('error', 'ID Akun Perkiraan Tidak Dapat Didefinisikan. Hubungi admin aplikasi untuk permasalahan ini.');
        }else{
            $id_perkiraan=$_POST['id_perkiraan'];
            $id_perkiraan=validateAndSanitizeInput($id_perkiraan);
            $id_perkiraan=GetDetailData($Conn,'akun_perkiraan','id_perkiraan',$id_perkiraan,'id_perkiraan');
            if(empty($id_perkiraan)){
                Response('error', 'ID Akun Perkiraan Tidak Ditemukan Pada Database!');
            }else{
                $kode=GetDetailData($Conn,'akun_perkiraan','id_perkiraan',$id_perkiraan,'kode');
                $nama=GetDetailData($Conn,'akun_perkiraan','id_perkiraan',$id_perkiraan,'nama');
                $level=GetDetailData($Conn,'akun_perkiraan','id_perkiraan',$id_perkiraan,'level');
                $saldo_normal=GetDetailData($Conn,'akun_perkiraan','id_perkiraan',$id_perkiraan,'saldo_normal');
                ob_start();
?>
                <input type="hidden" name="id_perkiraan" value="<?php echo "$id_perkiraan"; ?>">
                <div class="row mb-3">
                    <div class="col-4">Kode Akun</div>
                    <div class="col-8 text-end">
                        <small class="credit text-grayish"><?php echo $kode; ?></small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">Nama Akun</div>
                    <div class="col-8 text-end">
                        <small class="credit text-grayish"><?php echo $nama; ?></small>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-4">Level</div>
                    <div class="col-8 text-end">
                        <small class="credit text-grayish"><?php echo $level; ?></small>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-4">Saldo Normal</div>
                    <div class="col-8 text-end">
                        <small class="credit text-grayish"><?php echo $saldo_normal; ?></small>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <small>
                                <b>Penting!</b><br> Data yang sudah dihapus tidak bisa dikembalikan lagi. <br>
                                Apakah anda yakin akan menghapus akun perkiraan ini?
                            </small>
                        </div>
                    </div>
                </div>
<?php 
                $html = ob_get_clean();
                Response('success', 'Data Akun Perkiraan Berhasil Dimuat.', $html);
            } 
        } 
    } 
?>
